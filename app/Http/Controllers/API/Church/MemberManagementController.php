<?php

namespace App\Http\Controllers\API\Church;

use App\Exports\MemberImportTemplateExport;
use App\Exports\MembersExport;
use App\Http\Controllers\Controller;
use App\Imports\MembersImport;
use App\Services\MemberBulkImporter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\{
    Member,
    Church,
    MemberDesignation,
    CellGroup,
    Department
};

class MemberManagementController extends Controller
{
    /**
     * Build the base, hierarchy-scoped + filtered members query shared by
     * the index listing and the Excel export.
     */
    private function scopedMembersQuery(Request $request)
    {
        $member = Auth::user()->member;

        if (!$member) {
            abort(403, 'No member record found for this user.');
        }

        $userChurch = Church::find($member->church_id);

        if (!$userChurch) {
            abort(403, 'No church found for this member.');
        }

        $query = Member::with([
            'church',
            'member_roles.member_designation',
            'cell_groups',
            'departments',
        ]);

        // Default to the real congregation - new souls stay out of Member
        // Management unless explicitly requested (they have their own New
        // Souls screen), same as every other member list/count in the app.
        if ($request->get('type') !== 'all') {
            $query->where('member_type', $request->get('type') === 'new_soul' ? 'new_soul' : 'member');
        }

        if (!is_null($userChurch->parent_church_id)) {
            $churchIds = Church::where('id', $userChurch->id)
                ->orWhere('parent_church_id', $userChurch->id)
                ->pluck('id');

            $query->whereIn('church_id', $churchIds);
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('church_id')) {
            $query->where('church_id', $request->church_id);
        }

        if ($request->filled('designation_id')) {
            $query->whereHas('member_roles', function ($q) use ($request) {
                $q->where('designation_id', $request->designation_id);
            });
        }

        return $query->orderBy('first_name');
    }

    /**
     * LIST MEMBERS
     */
    public function index(Request $request)
    {
        $Members = $this->scopedMembersQuery($request)
            ->paginate(10)
            ->withQueryString();

        $churches = Church::with('current_head.member')->orderBy('name')->get();
        $designations = MemberDesignation::orderBy('name')->get();
        $cellGroups = CellGroup::all();
        $departments = Department::all();

        $stats = [
            'total'      => $this->scopedMembersQuery($request)->count(),
            'baptized'   => $this->scopedMembersQuery($request)->where('baptism_status', 'yes')->count(),
            'foundation' => $this->scopedMembersQuery($request)->where('foundation_clases', 'yes')->count(),
            'married'    => $this->scopedMembersQuery($request)->where('marriage_status', 'married')->count(),
        ];

        return view('portal.members.churchmebers', [
            'Members'      => $Members,
            'churches'     => $churches,
            'designations' => $designations,
            'cellGroups'   => $cellGroups,
            'departments'  => $departments,
            'stats'        => $stats,
            'uploadChurch' => $this->uploaderChurch(),
        ]);
    }

    /**
     * EXPORT MEMBERS TO EXCEL
     */
    public function export(Request $request)
    {
        $members = $this->scopedMembersQuery($request)->get();

        $filename = 'members-list-' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new MembersExport($members), $filename);
    }

    public function update(Request $request, Member $member)
    {
        $member->update($request->only([
            'first_name',
            'last_name',
            'email',
            'phone',
            'church_id',
            'foundation_clases',
            'foundation_clases_date',
            'baptism_status',
            'baptism_date',
            'marriage_status',
            'marriage_dates',
        ]));

        $member->member_roles()->delete();

        if ($request->designations) {
            foreach ($request->designations as $designation) {
                $member->member_roles()->create([
                    'designation_id' => $designation
                ]);
            }
        }

        $member->cell_groups()->sync($request->cell_groups ?? []);
        $member->departments()->sync($request->departments ?? []);

        return back()->with('success', 'Member updated successfully');
    }

    /* =================================================================
     | BULK UPLOAD
     | Every member in an uploaded sheet joins the uploader's own church.
     | Step 1 (preview) stores the file and reports what would happen;
     | step 2 (import) re-checks the stored file and saves it.
     |=================================================================*/

    /**
     * The logged-in user's own church - where bulk-uploaded members go.
     */
    private function uploaderChurch(): ?Church
    {
        $member = Auth::user()->member;

        return $member ? Church::find($member->church_id) : null;
    }

    private function importPath(string $token): string
    {
        return 'member-imports/' . Auth::id() . '/' . $token;
    }

    private function readImportRows(string $path): array
    {
        $sheets = Excel::toArray(new MembersImport, $path, 'local');

        return $sheets[0] ?? [];
    }

    public function importTemplate()
    {
        return Excel::download(new MemberImportTemplateExport, 'member-upload-template.xlsx');
    }

    public function importPreview(Request $request, MemberBulkImporter $importer)
    {
        $church = $this->uploaderChurch();
        abort_unless($church, 422, 'Your account is not linked to a church, so members cannot be uploaded from it.');

        $request->validate([
            'file' => 'required|file|max:5120|mimes:xlsx,xls,csv,txt',
        ], [
            'file.mimes' => 'Upload an Excel file (.xlsx or .xls) or a CSV file.',
        ]);

        $extension = strtolower($request->file('file')->getClientOriginalExtension() ?: 'xlsx');
        $token = Str::uuid() . '.' . (in_array($extension, ['xlsx', 'xls', 'csv']) ? $extension : 'xlsx');
        $path = $this->importPath($token);

        // One pending upload per user: a new check replaces any earlier file
        // that was checked but never imported.
        Storage::disk('local')->delete(Storage::disk('local')->files(dirname($path)));
        Storage::disk('local')->putFileAs(dirname($path), $request->file('file'), $token);

        try {
            $rows = $this->readImportRows($path);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            abort(422, 'This file could not be read. Download the template and fill it in.');
        }

        $firstRow = $rows[0] ?? [];
        if (!array_key_exists('first_name', $firstRow) || !array_key_exists('phone', $firstRow)) {
            Storage::disk('local')->delete($path);
            abort(422, 'The file is missing the template columns (First Name, Last Name, Phone, ...). Download the template and fill it in.');
        }

        $result = $importer->analyse($rows);

        if ($result['total'] > MemberBulkImporter::MAX_ROWS) {
            Storage::disk('local')->delete($path);
            abort(422, 'A file can hold at most ' . MemberBulkImporter::MAX_ROWS . ' members. Split it into smaller files.');
        }

        return response()->json([
            'token' => $token,
            'church' => $church->name,
            'total_rows' => $result['total'],
            'valid_count' => count($result['valid']),
            'skipped' => $result['skipped'],
        ]);
    }

    public function import(Request $request, MemberBulkImporter $importer)
    {
        $church = $this->uploaderChurch();
        if (!$church) {
            return back()->withErrors('Your account is not linked to a church, so members cannot be uploaded from it.');
        }

        $data = $request->validate([
            'token' => ['required', 'regex:/^[0-9a-f-]{36}\.(xlsx|xls|csv)$/'],
        ]);
        $path = $this->importPath($data['token']);

        if (!Storage::disk('local')->exists($path)) {
            return back()->withErrors('The uploaded file has expired. Please upload it again.');
        }

        // Re-checked here rather than trusting the preview: someone may have
        // registered one of these members in between.
        $result = $importer->analyse($this->readImportRows($path));
        $imported = $importer->import($result['valid'], $church, Auth::id());
        Storage::disk('local')->delete($path);

        $message = "{$imported} member" . ($imported === 1 ? '' : 's') . " added to {$church->name}.";
        if ($skipped = count($result['skipped'])) {
            $message .= " {$skipped} row" . ($skipped === 1 ? ' was' : 's were') . ' skipped.';
        }

        return redirect()->route('member.management')->with('success', $message);
    }
}
