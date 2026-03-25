<?php
use App\Models\OrderSession;
use App\Models\PaymentHistory;
use App\Models\RestaurantOrder;

if (!function_exists('club_id')) {
    function club_id()
    {
        return auth()->check() ? auth()->user()->club_id : null;
    }
}

if (!function_exists('generateMrNo')) {
    function generateMrNo($forDate = null)
    {
        $clubId  = club_id();
        $carbon  = $forDate ? \Carbon\Carbon::parse($forDate) : now();
        $dateStr = $carbon->format('Ymd');
        $date    = $carbon->toDateString();

        // Check latest mr_no across both orders and sessions for this date
        $lastFromOrders = RestaurantOrder::where('club_id', $clubId)
            ->whereDate('created_at', $date)
            ->whereNotNull('mr_no')
            ->latest('id')
            ->value('mr_no');

        $lastFromSessions = OrderSession::where('club_id', $clubId)
            ->whereDate('created_at', $date)
            ->whereNotNull('mr_no')
            ->latest('id')
            ->value('mr_no');

        $lastFromPayments = PaymentHistory::where('club_id', $clubId)
            ->whereDate('created_at', $date)
            ->whereNotNull('mr_no')
            ->latest('id')
            ->value('mr_no');

        $nums = array_filter([
            $lastFromOrders   ? (int) substr($lastFromOrders,   -6) : 0,
            $lastFromSessions ? (int) substr($lastFromSessions, -6) : 0,
            $lastFromPayments ? (int) substr($lastFromPayments, -6) : 0,
        ]);

        $lastNumber = $nums ? max($nums) : 0;

        return 'MR/LP/' . $dateStr . '/' . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('generateBillNo')) {
    function generateBillNo($forDate = null)
    {
        $clubId  = club_id();
        $carbon  = $forDate ? \Carbon\Carbon::parse($forDate) : now();
        $dateStr = $carbon->format('Ymd');
        $date    = $carbon->toDateString();

        // Check latest bill_no across both orders and sessions for this date
        $lastFromOrders = RestaurantOrder::where('club_id', $clubId)
            ->whereDate('created_at', $date)
            ->whereNotNull('bill_no')
            ->latest('id')
            ->value('bill_no');

        $lastFromSessions = OrderSession::where('club_id', $clubId)
            ->whereDate('created_at', $date)
            ->whereNotNull('bill_no')
            ->latest('id')
            ->value('bill_no');

        $lastFromPayments = PaymentHistory::where('club_id', $clubId)
            ->whereDate('created_at', $date)
            ->whereNotNull('bill_no')
            ->latest('id')
            ->value('bill_no');

        $nums = array_filter([
            $lastFromOrders   ? (int) substr($lastFromOrders,   -6) : 0,
            $lastFromSessions ? (int) substr($lastFromSessions, -6) : 0,
            $lastFromPayments ? (int) substr($lastFromPayments, -6) : 0,
        ]);

        $lastNumber = $nums ? max($nums) : 0;

        return 'BILL/LP/' . $dateStr . '/' . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
    }
}
