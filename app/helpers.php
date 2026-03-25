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

/* ── Financial year helpers ───────────────────────────────────────────────── */

if (!function_exists('financialYearLabel')) {
    /** Returns "25-26" style label for the FY that contains $carbon. */
    function financialYearLabel(\Carbon\Carbon $carbon): string
    {
        $month = (int) $carbon->month;
        $year  = (int) $carbon->year;
        if ($month >= 4) {
            return sprintf('%02d-%02d', $year % 100, ($year + 1) % 100);
        }
        return sprintf('%02d-%02d', ($year - 1) % 100, $year % 100);
    }
}

if (!function_exists('financialYearRange')) {
    /** Returns [startDateString, endDateString] for the FY that contains $carbon. */
    function financialYearRange(\Carbon\Carbon $carbon): array
    {
        $month = (int) $carbon->month;
        $year  = (int) $carbon->year;
        $startYear = $month >= 4 ? $year : $year - 1;
        return [
            \Carbon\Carbon::create($startYear, 4, 1)->toDateString(),
            \Carbon\Carbon::create($startYear + 1, 3, 31)->toDateString(),
        ];
    }
}

/* ── Sequence generators ──────────────────────────────────────────────────── */

if (!function_exists('generateSessionNo')) {
    function generateSessionNo($forDate = null): string
    {
        $clubId  = club_id();
        $carbon  = $forDate ? \Carbon\Carbon::parse($forDate) : now();
        $fy      = financialYearLabel($carbon);
        [$fyStart, $fyEnd] = financialYearRange($carbon);

        $last = OrderSession::where('club_id', $clubId)
            ->whereBetween('created_at', [$fyStart . ' 00:00:00', $fyEnd . ' 23:59:59'])
            ->whereNotNull('session_no')
            ->latest('id')
            ->value('session_no');

        $lastNum = $last ? (int) substr($last, -4) : 0;

        return 'LF/' . $fy . '/' . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('generateOrderNo')) {
    function generateOrderNo($forDate = null): string
    {
        $clubId  = club_id();
        $carbon  = $forDate ? \Carbon\Carbon::parse($forDate) : now();
        $fy      = financialYearLabel($carbon);
        [$fyStart, $fyEnd] = financialYearRange($carbon);

        $last = RestaurantOrder::where('club_id', $clubId)
            ->whereBetween('created_at', [$fyStart . ' 00:00:00', $fyEnd . ' 23:59:59'])
            ->whereNotNull('order_no')
            ->latest('id')
            ->value('order_no');

        $lastNum = $last ? (int) substr($last, -4) : 0;

        return 'LF/' . $fy . '/' . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('generateMrNo')) {
    function generateMrNo($forDate = null): string
    {
        $clubId  = club_id();
        $carbon  = $forDate ? \Carbon\Carbon::parse($forDate) : now();
        $fy      = financialYearLabel($carbon);
        [$fyStart, $fyEnd] = financialYearRange($carbon);

        $range = [$fyStart . ' 00:00:00', $fyEnd . ' 23:59:59'];

        $lastFromOrders = RestaurantOrder::where('club_id', $clubId)
            ->whereBetween('created_at', $range)
            ->whereNotNull('mr_no')
            ->latest('id')
            ->value('mr_no');

        $lastFromSessions = OrderSession::where('club_id', $clubId)
            ->whereBetween('created_at', $range)
            ->whereNotNull('mr_no')
            ->latest('id')
            ->value('mr_no');

        $lastFromPayments = PaymentHistory::where('club_id', $clubId)
            ->whereBetween('created_at', $range)
            ->whereNotNull('mr_no')
            ->latest('id')
            ->value('mr_no');

        $nums = array_filter([
            $lastFromOrders   ? (int) substr($lastFromOrders,   -4) : 0,
            $lastFromSessions ? (int) substr($lastFromSessions, -4) : 0,
            $lastFromPayments ? (int) substr($lastFromPayments, -4) : 0,
        ]);

        $lastNum = $nums ? max($nums) : 0;

        return 'LF/' . $fy . '/' . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('generateBillNo')) {
    function generateBillNo($forDate = null): string
    {
        $clubId  = club_id();
        $carbon  = $forDate ? \Carbon\Carbon::parse($forDate) : now();
        $fy      = financialYearLabel($carbon);
        [$fyStart, $fyEnd] = financialYearRange($carbon);

        $range = [$fyStart . ' 00:00:00', $fyEnd . ' 23:59:59'];

        $lastFromOrders = RestaurantOrder::where('club_id', $clubId)
            ->whereBetween('created_at', $range)
            ->whereNotNull('bill_no')
            ->latest('id')
            ->value('bill_no');

        $lastFromSessions = OrderSession::where('club_id', $clubId)
            ->whereBetween('created_at', $range)
            ->whereNotNull('bill_no')
            ->latest('id')
            ->value('bill_no');

        $lastFromPayments = PaymentHistory::where('club_id', $clubId)
            ->whereBetween('created_at', $range)
            ->whereNotNull('bill_no')
            ->latest('id')
            ->value('bill_no');

        $nums = array_filter([
            $lastFromOrders   ? (int) substr($lastFromOrders,   -4) : 0,
            $lastFromSessions ? (int) substr($lastFromSessions, -4) : 0,
            $lastFromPayments ? (int) substr($lastFromPayments, -4) : 0,
        ]);

        $lastNum = $nums ? max($nums) : 0;

        return 'LF/' . $fy . '/' . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
    }
}
