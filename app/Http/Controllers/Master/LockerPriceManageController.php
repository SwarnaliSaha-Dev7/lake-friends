<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Services\LockerPurchaseService;
use Illuminate\Http\Request;

class LockerPriceManageController extends Controller
{
    public function index(LockerPurchaseService $lockerPurchase)
    {
        $page_title = 'Manage Locker Price';
        $title      = 'Locker Price';

        $lockerPrice = $lockerPurchase->settings(auth()->user()->club_id);

        return view('master_manage.locker_prices.index', compact('lockerPrice', 'page_title', 'title'));
    }

    public function update(Request $request, LockerPurchaseService $lockerPurchase)
    {
        $data = $request->validate([
            'club_first_price'   => 'required|numeric|min:0|decimal:0,2',
            'club_renewal_price' => 'required|numeric|min:0|decimal:0,2',
            'swim_price'         => 'required|numeric|min:0|decimal:0,2',
            'gst_percentage'     => 'required|numeric|between:0,100|decimal:0,2',
        ]);

        $lockerPurchase->settings(auth()->user()->club_id)->update($data);

        return redirect()
            ->route('manage-locker-prices.index')
            ->with('success', 'Locker price updated successfully!');
    }
}
