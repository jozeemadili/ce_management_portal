<?php

namespace App\Http\Controllers\API\Pledges;

use App\Http\Controllers\Concerns\AuthorizesPledges;
use App\Http\Controllers\Controller;
use App\Models\Pledge;
use App\Models\PledgePaymentMethod;

class PledgeScanController extends Controller
{
    use AuthorizesPledges;

    /**
     * Landing page for the QR code printed on a pledge's PDF. Meant for
     * staff scanning a physical pledge card/receipt at an event: shows the
     * pledge + member at a glance and offers a quick "record fulfillment"
     * shortcut (posts to the existing contribution-record endpoint, so the
     * recording logic itself isn't duplicated here).
     */
    public function show(Pledge $pledge)
    {
        $this->authorizePledge('PLEDGES_VIEW');

        $pledge->load(['campaign', 'member.church', 'contributions' => fn($q) => $q->orderByDesc('payment_date')]);

        $paymentMethods = PledgePaymentMethod::where('is_active', true)->orderBy('name')->get();

        return view('portal.pledges.scan.show', compact('pledge', 'paymentMethods'));
    }
}
