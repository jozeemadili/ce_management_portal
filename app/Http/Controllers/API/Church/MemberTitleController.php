<?php

namespace App\Http\Controllers\API\Church;

use App\Http\Controllers\Concerns\AuthorizesPrograms;
use App\Http\Controllers\Controller;
use App\Models\MemberTitle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Church Setup > Member Titles: the list behind the Title dropdown on
 * member forms and the Title column of the Excel upload.
 */
class MemberTitleController extends Controller
{
    use AuthorizesPrograms;

    public function index()
    {
        $this->authorizeProgram('PROGRAMS_VIEW');

        $titles = MemberTitle::withCount('members')->orderBy('sort_order')->orderBy('name')->get();

        return view('portal.churches.titles.index', compact('titles'));
    }

    public function store(Request $request)
    {
        $this->authorizeProgram('PROGRAMS_EDIT');

        $data = $request->validate([
            'name' => 'required|string|max:50|unique:member_titles,name',
            'sort_order' => 'nullable|integer|min:0|max:999',
        ]);

        MemberTitle::create([
            'name' => trim($data['name']),
            'sort_order' => $data['sort_order'] ?? (MemberTitle::max('sort_order') + 1),
            'is_active' => true,
        ]);

        return back()->with('success', 'Title "' . trim($data['name']) . '" added.');
    }

    public function update(Request $request, MemberTitle $title)
    {
        $this->authorizeProgram('PROGRAMS_EDIT');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('member_titles', 'name')->ignore($title->id)],
            'sort_order' => 'nullable|integer|min:0|max:999',
            'is_active' => 'nullable|boolean',
        ]);

        $title->update([
            'name' => trim($data['name']),
            'sort_order' => $data['sort_order'] ?? $title->sort_order,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Title "' . $title->name . '" saved.');
    }

    public function destroy(MemberTitle $title)
    {
        $this->authorizeProgram('PROGRAMS_EDIT');

        if ($title->members()->exists()) {
            return back()->withErrors(['title' => '"' . $title->name . '" is used by ' . $title->members()->count() . ' member(s). Switch it off instead of deleting it.']);
        }

        $name = $title->name;
        $title->delete();

        return back()->with('success', 'Title "' . $name . '" deleted.');
    }
}
