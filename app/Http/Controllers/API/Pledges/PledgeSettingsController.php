<?php

namespace App\Http\Controllers\API\Pledges;

use App\Http\Controllers\Concerns\AuthorizesPledges;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\PledgePaymentMethod;
use Illuminate\Http\Request;

class PledgeSettingsController extends Controller
{
    use AuthorizesPledges;

    /**
     * Module settings landing page. Most Pledges "settings" are per-campaign
     * (live display toggles, anonymous pledging, live presentation) and are
     * already editable from the Campaign edit form and the Live Presentation
     * screen's controls - this page is the reference for the module-wide
     * pieces: which permission codes exist, and the Payment Methods lookup
     * used by the Record Contribution form.
     */
    public function index()
    {
        $this->authorizePledge('PLEDGES_MANAGE_SETTINGS');

        $permissions = Permission::where('code', 'like', 'PLEDGES_%')->orderBy('code')->get();
        $paymentMethods = PledgePaymentMethod::orderBy('name')->get();

        return view('portal.pledges.settings.index', compact('permissions', 'paymentMethods'));
    }

    public function storePaymentMethod(Request $request)
    {
        $this->authorizePledge('PLEDGES_MANAGE_SETTINGS');

        $data = $request->validate([
            'name' => 'required|string|max:100|unique:pledge_payment_methods,name',
        ]);

        PledgePaymentMethod::create($data);

        return back()->with('success', 'Payment method added.');
    }

    public function togglePaymentMethod(PledgePaymentMethod $paymentMethod)
    {
        $this->authorizePledge('PLEDGES_MANAGE_SETTINGS');

        $paymentMethod->update(['is_active' => !$paymentMethod->is_active]);

        return back()->with('success', $paymentMethod->name . ' is now ' . ($paymentMethod->is_active ? 'active' : 'inactive') . '.');
    }
}
