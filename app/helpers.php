<?php
use App\Models\PaymentHistory;

if (!function_exists('club_id')) {
    function club_id()
    {
        return auth()->check() ? auth()->user()->club_id : null;
    }
}

if (!function_exists('generateMrNo')) {
    function generateMrNo()
    {
        $clubId = club_id();
        $date = now()->format('Ymd');

        $lastMr = PaymentHistory::where('club_id', $clubId)
            ->whereDate('created_at', now())
            ->latest('created_at')
            ->value('mr_no');

        $lastNumber = $lastMr ? (int) substr($lastMr, -6) : 0;

        $nextNumber = $lastNumber + 1;

        return 'MR/LP/'.$date.'/'.str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('generateBillNo')) {
    function generateBillNo()
    {
        $clubId = club_id();
        $date = now()->format('Ymd');

        $lastBill = PaymentHistory::where('club_id', $clubId)
            ->whereDate('created_at', now())
            ->latest('created_at')
            ->value('bill_no');

        $lastNumber = $lastBill ? (int) substr($lastBill, -6) : 0;

        $nextNumber = $lastNumber + 1;

        return 'BILL/LP/'.$date.'/'.str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
