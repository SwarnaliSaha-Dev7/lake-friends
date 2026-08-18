<?php

namespace App\Http\Controllers;

use App\Models\MiscBill;
use App\Models\MiscBillItem;
use App\Models\MiscCategory;
use App\Models\MiscItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MiscBillController extends Controller
{
    public function index()
    {
        try {
            $page_title = 'Misc Billing';
            $title      = 'Misc Billing';
            $clubId     = club_id();

            $miscItems = MiscItem::where('club_id', $clubId)
                ->where('is_active', 1)
                ->with(['miscItemPrice', 'miscItemCat'])
                ->get()
                ->map(function ($item) {
                    return [
                        'id'                 => $item->id,
                        'name'               => $item->name,
                        'code'               => $item->code,
                        'category'           => $item->miscItemCat->name ?? '—',
                        'unit'               => $item->unit ?: 'pcs',
                        'gst_percentage'     => (float) $item->gst_percentage,
                        'price'              => $item->miscItemPrice?->price ?? 0,
                        'is_price_editable'  => (bool) $item->is_price_editable,
                    ];
                });

            $categories = MiscCategory::where('club_id', $clubId)->get();

            $todayBills = MiscBill::where('club_id', $clubId)
                ->whereDate('created_at', now())
                ->with('items.miscItem')
                ->latest()
                ->get();

            $todayTotal = $todayBills->where('status', 'paid')->sum('net_amount');

            return view('misc_bills.index', compact(
                'miscItems', 'categories', 'todayBills', 'todayTotal', 'page_title', 'title'
            ));
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $clubId = club_id();

            $request->validate([
                'items'                    => 'required|array|min:1',
                'items.*.misc_item_id'     => 'required|integer',
                'items.*.quantity'         => 'required|numeric|min:0.01',
                'items.*.unit_price'       => 'nullable|numeric|min:0',
                'payment_mode'             => 'required|in:cash,card,upi,bank_transfer,cheque,other',
                'payment_reference'        => 'nullable|string|max:100',
                'buyer_name'               => 'nullable|string|max:255',
                'buyer_contact'            => 'nullable|string|max:20',
                'remarks'                  => 'nullable|string',
            ]);

            // Recompute every line's money server-side from the DB-held item —
            // client-submitted totals are only trusted for unit_price when the
            // item explicitly allows a price override (e.g. Banquet Rent).
            $subtotal = 0;
            $gstTotal = 0;
            $lines    = [];

            foreach ($request->items as $line) {
                $item = MiscItem::where('club_id', $clubId)
                    ->where('id', $line['misc_item_id'])
                    ->where('is_active', 1)
                    ->firstOrFail();

                $quantity = (float) $line['quantity'];
                $catalogPrice = $item->miscItemPrice?->price ?? 0;

                $unitPrice = $item->is_price_editable && isset($line['unit_price'])
                    ? (float) $line['unit_price']
                    : (float) $catalogPrice;

                $gstPct      = (float) $item->gst_percentage;
                $totalAmount = round($quantity * $unitPrice, 2);
                $lineGst     = round($totalAmount * $gstPct / 100, 2);

                $subtotal += $totalAmount;
                $gstTotal += $lineGst;

                $lines[] = [
                    'misc_item_id'   => $item->id,
                    'quantity'       => $quantity,
                    'unit'           => $item->unit,
                    'unit_price'     => $unitPrice,
                    'gst_percentage' => $gstPct,
                    'total_amount'   => $totalAmount,
                    'gst_amount'     => $lineGst,
                    'ac_head'        => $item->miscItemCat->name ?? $item->name,
                ];
            }

            $netAmount = round($subtotal + $gstTotal, 2);

            $bill = MiscBill::create([
                'club_id'           => $clubId,
                'bill_no'           => generateMiscBillNo(),
                'mr_no'             => generateMrNo(),
                'buyer_name'        => $request->buyer_name,
                'buyer_contact'     => $request->buyer_contact,
                'ac_head'           => $lines[0]['ac_head'] ?? 'Misc Billing',
                'payment_mode'      => $request->payment_mode,
                'payment_reference' => $request->payment_reference,
                'subtotal'          => round($subtotal, 2),
                'discount_amount'   => 0,
                'gst_amount'        => round($gstTotal, 2),
                'net_amount'        => $netAmount,
                'status'            => 'paid',
                'remarks'           => $request->remarks,
                'created_by'        => Auth::id(),
            ]);

            foreach ($lines as $line) {
                MiscBillItem::create([
                    'misc_bill_id'   => $bill->id,
                    'misc_item_id'   => $line['misc_item_id'],
                    'quantity'       => $line['quantity'],
                    'unit'           => $line['unit'],
                    'unit_price'     => $line['unit_price'],
                    'gst_percentage' => $line['gst_percentage'],
                    'total_amount'   => $line['total_amount'],
                    'gst_amount'     => $line['gst_amount'],
                ]);
            }

            DB::commit();

            return response()->json([
                'statusCode'   => 200,
                'message'      => 'Bill created successfully.',
                'bill_id'      => $bill->id,
                'bill_no'      => $bill->bill_no,
                'receipt_url'  => route('misc-bills.receipt', $bill->id),
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
        }
    }

    public function show(string $id)
    {
        try {
            $bill = MiscBill::with(['items.miscItem', 'createdBy', 'cancelledBy'])
                ->where('club_id', club_id())
                ->findOrFail($id);

            return response()->json(['statusCode' => 200, 'data' => $bill]);
        } catch (\Throwable $th) {
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
        }
    }

    public function downloadReceipt(string $id)
    {
        try {
            $bill = MiscBill::with(['items.miscItem', 'createdBy'])
                ->where('club_id', club_id())
                ->findOrFail($id);

            $halfGst = round((float) $bill->gst_amount / 2, 2);

            $receipt = [
                'date'          => $bill->created_at->format('d-m-Y'),
                'bill_no'       => $bill->bill_no,
                'receipt_no'    => $bill->mr_no,
                'payment_mode'  => ucfirst(str_replace('_', ' ', $bill->payment_mode)),
                'received_from' => $bill->buyer_name ?: 'Cash Customer',
                'buyer_contact' => $bill->buyer_contact,
                'taxable_amount'=> number_format((float) $bill->subtotal, 2),
                'cgst'          => number_format($halfGst, 2),
                'sgst'          => number_format($halfGst, 2),
                'net_amount'    => number_format((float) $bill->net_amount, 2),
                'amount_words'  => amountToWords((float) $bill->net_amount),
                'collected_by'  => $bill->createdBy?->name ?? '—',
                'printed_at'    => now()->format('d-m-Y h:iA'),
            ];

            $pdf = Pdf::loadView('misc_bills.receipt', compact('bill', 'receipt'))
                ->setPaper('a4', 'portrait');

            $filename = 'misc-receipt-' . str_replace('/', '-', $bill->bill_no) . '.pdf';

            return $pdf->download($filename);
        } catch (\Throwable $th) {
            return back()->with('error', $th->getMessage());
        }
    }

    public function history(Request $request)
    {
        try {
            $clubId    = club_id();
            $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
            $endDate   = $request->input('end_date', now()->toDateString());

            $bills = MiscBill::where('club_id', $clubId)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->with(['items.miscItem', 'createdBy'])
                ->latest()
                ->get();

            $paidBills     = $bills->where('status', 'paid');
            $totalBills    = $paidBills->count();
            $totalRevenue  = $paidBills->sum('net_amount');
            $totalGst      = $paidBills->sum('gst_amount');
            $avgBill       = $totalBills > 0 ? $totalRevenue / $totalBills : 0;

            return view('misc_bills.history', compact(
                'bills', 'startDate', 'endDate', 'totalBills', 'totalRevenue', 'totalGst', 'avgBill'
            ));
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function downloadReport(Request $request)
    {
        try {
            $clubId    = club_id();
            $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
            $endDate   = $request->input('end_date', now()->toDateString());

            $bills = MiscBill::where('club_id', $clubId)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->with(['items.miscItem'])
                ->latest()
                ->get();

            $paidBills    = $bills->where('status', 'paid');
            $totalBills   = $paidBills->count();
            $totalRevenue = $paidBills->sum('net_amount');
            $totalGst     = $paidBills->sum('gst_amount');
            $avgBill      = $totalBills > 0 ? $totalRevenue / $totalBills : 0;

            $byDate = $bills->groupBy(fn($b) => $b->created_at->toDateString());

            $itemSummary = $paidBills->flatMap(fn($b) => $b->items)
                ->groupBy('misc_item_id')
                ->map(function ($rows) {
                    $first = $rows->first();
                    return [
                        'name'   => $first->miscItem->name ?? '—',
                        'qty'    => $rows->sum('quantity'),
                        'amount' => $rows->sum('total_amount'),
                    ];
                })
                ->sortByDesc('amount')
                ->values();

            $modeSummary = $paidBills->groupBy('payment_mode')
                ->map(fn($rows, $mode) => [
                    'mode'   => ucfirst(str_replace('_', ' ', $mode)),
                    'count'  => $rows->count(),
                    'amount' => $rows->sum('net_amount'),
                ])
                ->values();

            $pdf = Pdf::loadView('misc_bills.report_pdf', compact(
                'byDate', 'startDate', 'endDate', 'totalBills', 'totalRevenue',
                'totalGst', 'avgBill', 'itemSummary', 'modeSummary'
            ))->setPaper('a4', 'landscape');

            return $pdf->download('misc_sales_report_' . $startDate . '_to_' . $endDate . '.pdf');
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function cancel(string $id)
    {
        try {
            $bill = MiscBill::where('club_id', club_id())->findOrFail($id);

            if ($bill->status === 'cancelled') {
                return response()->json(['statusCode' => 422, 'message' => 'Bill is already cancelled.']);
            }

            $bill->update([
                'status'        => 'cancelled',
                'cancelled_by'  => Auth::id(),
                'cancelled_at'  => now(),
                'cancel_reason' => request('reason'),
            ]);

            return response()->json(['statusCode' => 200, 'message' => 'Bill cancelled successfully.']);
        } catch (\Throwable $th) {
            return response()->json(['statusCode' => 500, 'error' => $th->getMessage()]);
        }
    }
}
