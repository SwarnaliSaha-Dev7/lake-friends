@extends('base.app')

@section('title', 'Food Report')
@section('page_title', 'Food Report')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="member-list-part position-relative">

                {{-- Header --}}
                <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
                    <h2 class="fs-5 common-heading mb-0 fw-semibold">Food Report</h2>
                </div>

                {{-- Stat cards --}}
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-between"
                            style="background: linear-gradient(135deg,#fbbf24,#d97706);">
                            <div>
                                <div class="small opacity-75 mb-1">Top Selling Food</div>
                                <div class="fs-5 fw-bold">{{ $topSelling['name'] ?? '—' }}</div>
                                @if($topSelling)
                                <div class="small opacity-75">{{ number_format($topSelling['qty']) }} plates sold</div>
                                @endif
                            </div>
                            <i class="fa-solid fa-utensils fs-2 opacity-50"></i>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-between"
                            style="background: linear-gradient(135deg,#66bb6a,#388e3c);">
                            <div>
                                <div class="small opacity-75 mb-1">Total Food Revenue</div>
                                <div class="fs-5 fw-bold">₹{{ number_format($totalRevenue, 0) }}</div>
                            </div>
                            <i class="fa-solid fa-indian-rupee-sign fs-2 opacity-50"></i>
                        </div>
                    </div>
                </div>

                {{-- Filters --}}
                <form method="GET" action="{{ route('food-report.index') }}" id="foodReportForm" class="row g-2 align-items-end mb-3">
                    {{-- Quick period buttons --}}
                    <div class="col-sm-auto">
                        @php
                            $isDaily   = $startDate === now()->toDateString() && $endDate === now()->toDateString();
                            $isWeekly  = $startDate === now()->startOfWeek()->toDateString() && $endDate === now()->endOfWeek()->toDateString();
                            $isMonthly = $startDate === now()->startOfMonth()->toDateString() && $endDate === now()->endOfMonth()->toDateString();
                        @endphp
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm period-btn {{ $isDaily ? 'btn-primary' : 'btn-outline-secondary' }}"
                                data-from="{{ now()->toDateString() }}" data-to="{{ now()->toDateString() }}">Daily</button>
                            <button type="button" class="btn btn-sm period-btn {{ $isWeekly ? 'btn-primary' : 'btn-outline-secondary' }}"
                                data-from="{{ now()->startOfWeek()->toDateString() }}" data-to="{{ now()->endOfWeek()->toDateString() }}">Weekly</button>
                            <button type="button" class="btn btn-sm period-btn {{ $isMonthly ? 'btn-primary' : 'btn-outline-secondary' }}"
                                data-from="{{ now()->startOfMonth()->toDateString() }}" data-to="{{ now()->endOfMonth()->toDateString() }}">Monthly</button>
                        </div>
                    </div>
                    <div class="col-sm-auto">
                        <label class="form-label small fw-semibold mb-1">From</label>
                        <input type="date" name="start_date" id="startDateInput" class="form-control form-control-sm shadow-none"
                            value="{{ $startDate }}">
                    </div>
                    <div class="col-sm-auto">
                        <label class="form-label small fw-semibold mb-1">To</label>
                        <input type="date" name="end_date" id="endDateInput" class="form-control form-control-sm shadow-none"
                            value="{{ $endDate }}">
                    </div>
                    <div class="col-sm-auto">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fa-solid fa-filter me-1"></i> Filter
                        </button>
                        @if(request()->hasAny(['start_date', 'end_date']))
                            <a href="{{ route('food-report.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
                                <i class="fa-solid fa-xmark me-1"></i> Reset
                            </a>
                        @endif
                    </div>
                    <div class="col-sm-auto ms-sm-auto d-flex gap-1">
                        <a id="pdfDownloadBtn"
                            href="{{ route('food-report.download', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                            class="btn btn-sm btn-outline-danger fw-semibold" target="_blank">
                            <i class="fa-solid fa-file-pdf me-1"></i> PDF
                        </a>
                    </div>
                </form>

                {{-- Table --}}
                <div class="table-responsive">
                    <table class="table rounded-3 overflow-hidden clubmemberlist2" id="foodReportTable" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th class="text-white fw-medium align-middle text-nowrap">Order No</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Member</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Date & Time</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Item</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Qty</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Offer</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Unit Price</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $hasRows = false; $grandTotal = 0; @endphp
                            @foreach($orders as $order)
                                @foreach($order->items as $item)
                                    @php
                                        $hasRows     = true;
                                        $grandTotal += $item->total_amount;
                                        $offer = null;
                                        if ($item->offer_applied) {
                                            $of   = is_array($item->offer_applied) ? $item->offer_applied : (array) $item->offer_applied;
                                            $slug = $of['type_slug'] ?? '';
                                            if ($slug === 'b1g1') {
                                                $offer = ['label' => 'B1G1', 'class' => 'bg-warning-subtle text-warning border-warning'];
                                            } elseif ($slug === 'percentage' && !empty($of['discount_value'])) {
                                                $offer = ['label' => $of['discount_value'] . '% off', 'class' => 'bg-success-subtle text-success border-success'];
                                            } elseif ($slug === 'flat' && !empty($of['discount_value'])) {
                                                $offer = ['label' => '₹' . $of['discount_value'] . ' off', 'class' => 'bg-info-subtle text-info border-info'];
                                            }
                                        }
                                    @endphp
                                    <tr>
                                        <td class="text-nowrap fw-medium">{{ $order->session->session_no ?? $order->order_no }}</td>
                                        <td class="text-nowrap">{{ $order->member->name ?? '—' }}</td>
                                        <td class="text-nowrap text-muted small">{{ $order->created_at->format('d M Y, h:i A') }}</td>
                                        <td class="text-nowrap">{{ $item->foodItem->name ?? '—' }}</td>
                                        <td class="text-nowrap">{{ $item->quantity }}</td>
                                        <td class="text-nowrap">
                                            @if($offer)
                                                <span class="badge border rounded-pill px-2 py-1 {{ $offer['class'] }}" style="font-size:0.72rem;">
                                                    {{ $offer['label'] }}
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-nowrap">₹{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-nowrap fw-semibold">₹{{ number_format($item->total_amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                            @if(!$hasRows)
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">No food orders found for this period.</td>
                                </tr>
                            @endif
                        </tbody>
                        @if($hasRows)
                        <tfoot>
                            <tr class="fw-bold" style="background:#f1f3f5;">
                                <td colspan="7" class="text-end pe-3">Total</td>
                                <td class="text-nowrap text-primary">₹{{ number_format($grandTotal, 2) }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('customJS')
<script>
$(document).ready(function () {

    $('.period-btn').on('click', function () {
        $('#startDateInput').val($(this).data('from'));
        $('#endDateInput').val($(this).data('to'));
        $('#foodReportForm').submit();
    });

    function updatePdfLink() {
        var base = '{{ route("food-report.download") }}';
        $('#pdfDownloadBtn').attr('href', base + '?start_date=' + $('#startDateInput').val() + '&end_date=' + $('#endDateInput').val());
    }
    $('#startDateInput, #endDateInput').on('change', updatePdfLink);


});
</script>
@endsection
