<?php

namespace App\Http\Livewire\CellGroup;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\CellGroup;
use App\Models\Member;
use App\Models\Church;

class CreateCellGroup extends Component
{
    public $church;        // Church model
    public $church_id;     // Used internally
    public $name;
    public $description;

    public $members = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'members.*.member_id' => 'required|exists:members,id',
        'members.*.role' => 'required|string',
    ];

    public function mount()
    {
        // 🔐 Get logged-in user's member
        $member = Auth::user()->member ?? null;

        if (!$member) {
            abort(403, 'No member record linked to this user.');
        }

        $this->church = Church::find($member->church_id);

        if (!$this->church) {
            abort(403, 'Church not found for this member.');
        }

        $this->church_id = $this->church->id;

        // Start with one member row
        $this->members[] = [
            'member_id' => '',
            'role' => '',
        ];
    }

    public function addMember()
    {
        $this->members[] = [
            'member_id' => '',
            'role' => '',
        ];
    }

    public function removeMember($index)
    {
        unset($this->members[$index]);
        $this->members = array_values($this->members);
    }

    public function save()
    {
        $this->validate();

        // 🛑 Role constraints
        $leaderCount = collect($this->members)->where('role', 'CELL_LEADER')->count();
        $assistantCount = collect($this->members)->where('role', 'ASSISTANT_CELL_LEADER')->count();

        if ($leaderCount > 1) {
            $this->addError('members', 'Only one Cell Leader is allowed.');
            return;
        }

        if ($assistantCount > 1) {
            $this->addError('members', 'Only one Assistant Cell Leader is allowed.');
            return;
        }

        DB::transaction(function () {

            $cell = CellGroup::create([
                'church_id' => $this->church_id,
                'name' => strtoupper($this->name),
                'description' => $this->description,
            ]);

            foreach ($this->members as $row) {

                // 🔐 Extra safety: member must belong to same church
                $member = Member::where('id', $row['member_id'])
                    ->where('church_id', $this->church_id)
                    ->firstOrFail();

                $cell->members()->attach($member->id, [
                    'role' => $row['role'],
                ]);
            }
        });

        session()->flash('success', 'Cell group created successfully.');

        $this->reset(['name', 'description', 'members']);
        $this->mount();
    }

    // public function render()
    // {
    //     return view('livewire.cell-group.create-cell-group', [
    //         // ✅ Only members from same church
    //         'churchMembers' => Member::where('church_id', $this->church_id)
    //             ->orderBy('first_name')
    //             ->get(),
    //     ]);
    // }
    public function render()
{
    return view('livewire.cell-group.create-cell-group', [
        'churchMembers' => Member::where('church_id', $this->church_id)
            ->whereDoesntHave('cell_groups') // ✅ ONLY members with NO cell
            ->orderBy('first_name')
            ->get(),
    ]);
}

}
