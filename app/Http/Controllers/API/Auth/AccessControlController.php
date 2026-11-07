<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Models\ProgramAuditLog;
use App\Models\User;
use App\Models\UserMenuGrant;
use App\Services\MenuAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Security & Settings > Access Control: choose a user and tick the menus /
 * sub-menus they may use. ADMIN users always see everything.
 *
 * A non-admin with this page can only give or take away menus they have
 * themselves, and can't change their own access or an ADMIN's - nobody
 * can use it to give themselves more.
 */
class AccessControlController extends Controller
{
    public function __construct(private MenuAccess $access)
    {
    }

    public function index(Request $request)
    {
        $me = Auth::user();
        $selected = $request->filled('user') ? User::find($request->user) : null;
        $copyFrom = $selected && $request->filled('copy') ? User::find($request->copy) : null;

        $ticked = $selected ? $this->access->granted($copyFrom ?? $selected) : [];

        $withAccess = User::query()
            ->whereIn('id', UserMenuGrant::select('user_id'))
            ->withCount('menuGrants')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'mobile', 'email', 'role']);

        return view('admin.security.access-control', [
            'catalog' => $this->access->catalog(),
            'always' => config('menu_access.always', []),
            'selected' => $selected,
            'copyFrom' => $copyFrom,
            'ticked' => $ticked,
            'withAccess' => $withAccess,
            'admins' => User::where('role', 'ADMIN')->orderBy('first_name')->get(['id', 'first_name', 'last_name']),
            'myKeys' => $this->grantableBy($me),
            'iAmAdmin' => $this->access->isAdmin($me),
            'locked' => $selected ? $this->lockReason($selected) : null,
        ]);
    }

    /** Select2 look-up: users by name, phone or email. */
    public function users(Request $request)
    {
        $q = mb_strtolower(trim((string) $request->q));
        $digits = preg_replace('/\D/', '', $q);

        $users = User::query()
            ->when($q !== '', function ($query) use ($q, $digits) {
                $like = '%' . $q . '%';
                $query->where(function ($w) use ($like, $digits) {
                    $w->whereRaw('LOWER(first_name) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(last_name) LIKE ?', [$like])
                        ->orWhereRaw("LOWER(CONCAT(first_name, ' ', last_name)) LIKE ?", [$like])
                        ->orWhereRaw('LOWER(email) LIKE ?', [$like]);
                    if (strlen($digits) >= 3) {
                        $w->orWhereRaw("CONCAT('', mobile) LIKE ?", ['%' . ltrim($digits, '0') . '%']);
                    }
                });
            })
            ->orderBy('first_name')
            ->limit(20)
            ->get(['id', 'first_name', 'last_name', 'mobile', 'email', 'role']);

        return response()->json(['results' => $users->map(fn ($u) => [
            'id' => $u->id,
            'text' => $this->label($u),
        ])]);
    }

    public function save(Request $request, User $user)
    {
        if ($reason = $this->lockReason($user)) {
            return back()->withErrors(['menus' => $reason]);
        }

        $data = $request->validate([
            'menus' => 'nullable|array',
            'menus.*' => 'string|max:100',
        ]);

        $me = Auth::user();
        $wanted = $data['menus'] ?? [];

        // A non-admin can only change the menus they have themselves; any
        // other menus the user already had stay as they were.
        if (!$this->access->isAdmin($me)) {
            $mine = $this->grantableBy($me);
            $current = $this->access->granted($user);
            $wanted = array_merge(
                array_intersect($wanted, $mine),
                array_diff($current, $mine)
            );
        }

        [$before, $after] = DB::transaction(fn () => $this->access->setGrants($user, $wanted, $me->id));

        if ($before !== $after) {
            ProgramAuditLog::record('access.menus_updated', $user, ['menus' => $before], ['menus' => $after]);
        }

        return redirect()->route('access-control.index', ['user' => $user->id])
            ->with('success', 'Access for ' . trim($user->first_name . ' ' . $user->last_name) . ' saved: '
                . (count($after) ? count($after) . ' menu(s) given.' : 'no menus (only Summary and Your Profile).'));
    }

    /**
     * Super admin (role ADMIN): sees every menu on the portal and the app and
     * gives menus to others. Only a super admin can give or take it away,
     * and the last super admin can't be removed.
     */
    public function superAdmin(Request $request, User $user)
    {
        abort_unless($this->access->isAdmin(Auth::user()), 403, 'Only a super admin can do this.');

        $make = $request->boolean('make');
        $name = trim($user->first_name . ' ' . $user->last_name);

        if (!$make && $this->access->isAdmin($user) && User::where('role', 'ADMIN')->count() <= 1) {
            return back()->withErrors(['menus' => "{$name} is the only super admin. Make someone else super admin first."]);
        }

        $old = ['role' => $user->role];
        $user->update(['role' => $make ? 'ADMIN' : 'Customer Admin']);
        $this->access->forget($user);
        ProgramAuditLog::record('access.super_admin_' . ($make ? 'given' : 'removed'), $user, $old, ['role' => $user->role]);

        return redirect()->route('access-control.index', ['user' => $user->id])->with('success', $make
            ? "{$name} is now super admin: sees every menu and can give menus to others."
            : "{$name} is no longer super admin and now sees only the menus ticked below.");
    }

    /**
     * Menus this user may give to others: the sub-menus they have, and a
     * whole main menu only if they have every one of its sub-menus (having
     * one sub-menu shows the main menu header but must not let them give
     * the whole menu).
     *
     * @return array<int, string>
     */
    private function grantableBy(User $me): array
    {
        $keys = $this->access->keys($me);
        foreach ($this->access->catalog() as $main => $menu) {
            if (array_diff(array_keys($menu['children']), $keys)) {
                $keys = array_values(array_diff($keys, [$main]));
            }
        }

        return $keys;
    }

    private function lockReason(User $user): ?string
    {
        if ($this->access->isAdmin($user)) {
            return trim($user->first_name . ' ' . $user->last_name) . ' is a super admin and always sees every menu on the portal and in the app.';
        }
        if ((int) $user->id === (int) Auth::id() && !$this->access->isAdmin(Auth::user())) {
            return 'You can\'t change your own access. Ask an administrator.';
        }

        return null;
    }

    private function label(User $u): string
    {
        return trim($u->first_name . ' ' . $u->last_name)
            . ($u->mobile ? ' · 0' . $u->mobile : '')
            . ($u->email ? ' · ' . $u->email : '')
            . ($u->role === 'ADMIN' ? ' · Super admin' : '');
    }
}
