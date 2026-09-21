<?php

namespace App\Http\Controllers\API\Programs;

use App\Http\Controllers\Concerns\AuthorizesPrograms;
use App\Http\Controllers\Controller;
use App\Models\Permission;

class ProgramSettingsController extends Controller
{
    use AuthorizesPrograms;

    /**
     * Module settings landing page - a reference for the permission codes
     * that gate this module. There is no module-wide adjustable lookup table
     * (unlike Pledges' Payment Methods) - everything else is per-program.
     */
    public function index()
    {
        $this->authorizeProgram('PROGRAMS_EDIT');

        $permissions = Permission::where('code', 'like', 'PROGRAMS_%')
            ->orWhere('code', 'like', 'ATTENDANCE_%')
            ->orWhere('code', 'like', 'NEW_SOULS_%')
            ->orWhere('code', 'like', 'PROGRAM_REPORTS_%')
            ->orderBy('code')
            ->get();

        return view('portal.programs.settings.index', compact('permissions'));
    }
}
