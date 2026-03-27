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

/**
 * Atomically increments the sequence counter for the given name+FY and returns
 * the next value. Uses a dedicated `sequences` table with a row-level lock
 * (SELECT ... FOR UPDATE) to prevent duplicate numbers under concurrent requests.
 * Must be called inside an active DB transaction.
 */
if (!function_exists('_nextSequenceValue')) {
    function _nextSequenceValue(string $seqName, int $clubId, string $fy): int
    {
        // Single atomic SQL: insert with last_value=1, or increment if row exists.
        // Safe under concurrent requests — no separate read-then-write race.
        \Illuminate\Support\Facades\DB::statement(
            "INSERT INTO sequences (club_id, sequence_name, fy_label, last_value)
             VALUES (?, ?, ?, 1)
             ON DUPLICATE KEY UPDATE last_value = last_value + 1",
            [$clubId, $seqName, $fy]
        );

        return (int) \Illuminate\Support\Facades\DB::table('sequences')
            ->where('club_id', $clubId)
            ->where('sequence_name', $seqName)
            ->where('fy_label', $fy)
            ->value('last_value');
    }
}

if (!function_exists('generateSessionNo')) {
    function generateSessionNo($forDate = null): string
    {
        $clubId = club_id();
        $carbon = $forDate ? \Carbon\Carbon::parse($forDate) : now();
        $fy     = financialYearLabel($carbon);
        $next   = _nextSequenceValue('session_no', $clubId, $fy);
        return 'LF/' . $fy . '/' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('generateOrderNo')) {
    function generateOrderNo($forDate = null): string
    {
        $clubId = club_id();
        $carbon = $forDate ? \Carbon\Carbon::parse($forDate) : now();
        $fy     = financialYearLabel($carbon);
        $next   = _nextSequenceValue('order_no', $clubId, $fy);
        return 'LF/' . $fy . '/' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('generateMrNo')) {
    function generateMrNo($forDate = null): string
    {
        $clubId = club_id();
        $carbon = $forDate ? \Carbon\Carbon::parse($forDate) : now();
        $fy     = financialYearLabel($carbon);
        $next   = _nextSequenceValue('mr_no', $clubId, $fy);
        return 'LF/' . $fy . '/' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('generateBillNo')) {
    function generateBillNo($forDate = null): string
    {
        $clubId = club_id();
        $carbon = $forDate ? \Carbon\Carbon::parse($forDate) : now();
        $fy     = financialYearLabel($carbon);
        $next   = _nextSequenceValue('bill_no', $clubId, $fy);
        return 'LF/' . $fy . '/' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
