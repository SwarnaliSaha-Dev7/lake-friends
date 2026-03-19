@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="member-list-part position-relative">

                {{-- Header --}}
                <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
                    <h2 class="fs-5 common-heading mb-0 fw-semibold">Bar Order History</h2>
                    <a href="{{ route('bar-orders.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fa-solid fa-arrow-left me-1"></i> Today's Orders
                    </a>
                </div>

                {{-- Stat cards --}}
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-between"
                            style="background: linear-gradient(135deg,#29b6f6,#0288d1);">
                            <div>
                                <div class="small opacity-75 mb-1">Top Selling Liquor</div>
                                <div class="fs-5 fw-bold">{{ $topSellingLiquor }}</div>
                            </div>
                            <i class="fa-solid fa-wine-bottle fs-2 opacity-50"></i>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-between"
                            style="background: linear-gradient(135deg,#66bb6a,#388e3c);">
                            <div>
                                <div class="small opacity-75 mb-1">Total Selling</div>
                                <div class="fs-5 fw-bold">₹{{ number_format($totalSelling, 0) }}</div>
                            </div>
                            <i class="fa-solid fa-indian-rupee-sign fs-2 opacity-50"></i>
                        </div>
                    </div>
                </div>

                {{-- Filters --}}
                <form method="GET" action="{{ route('bar-orders.history') }}" class="row g-2 align-items-end mb-3">
                    <div class="col-sm-auto">
                        <label class="form-label small fw-semibold mb-1">From</label>
                        <input type="date" name="start_date" class="form-control form-control-sm shadow-none"
                            value="{{ $startDate }}">
                    </div>
                    <div class="col-sm-auto">
                        <label class="form-label small fw-semibold mb-1">To</label>
                        <input type="date" name="end_date" class="form-control form-control-sm shadow-none"
                            value="{{ $endDate }}">
                    </div>
                    <div class="col-sm-auto">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fa-solid fa-filter me-1"></i> Filter
                        </button>
                        @if(request()->hasAny(['start_date', 'end_date']))
                            <a href="{{ route('bar-orders.history') }}" class="btn btn-sm btn-outline-secondary ms-1">
                                <i class="fa-solid fa-xmark me-1"></i> Reset
                            </a>
                        @endif
                    </div>

                </form>

                {{-- Table --}}
                <div class="table-responsive">
                    <table class="table rounded-3 overflow-hidden clubmemberlist2" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th class="text-white fw-medium align-middle text-nowrap">Order No</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Member</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Date & Time</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Item</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Volume</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Unit Price</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $hasRows = false; $grandTotal = 0; @endphp
                            @foreach($orders as $order)
                                @foreach($order->items->whereIn('unit', ['ml', 'btl']) as $item)
                                    @php
                                        $hasRows    = true;
                                        $isBeer     = $item->unit === 'btl';
                                        $volLabel   = $isBeer
                                            ? $item->quantity . ' BTL'
                                            : (($item->metadata['volume_ml'] ?? '?') . 'ml × ' . $item->quantity);
                                        $grandTotal += $item->total_amount;
                                    @endphp
                                    <tr>
                                        <td class="text-nowrap fw-medium">{{ $order->order_no }}</td>
                                        <td class="text-nowrap">{{ $order->member->name ?? '—' }}</td>
                                        <td class="text-nowrap text-muted small">{{ $order->created_at->format('d M Y, h:i A') }}</td>
                                        <td class="text-nowrap">{{ $item->foodItem->name ?? '—' }}</td>
                                        <td class="text-nowrap">{{ $volLabel }}</td>
                                        <td class="text-nowrap">Rs {{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-nowrap fw-semibold">Rs {{ number_format($item->total_amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                            @if(!$hasRows)
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No bar orders found for this period.</td>
                                </tr>
                            @endif
                        </tbody>
                        @if($hasRows)
                        <tfoot>
                            <tr class="fw-bold" style="background:#f1f3f5;">
                                <td colspan="6" class="text-end pe-3">Total</td>
                                <td class="text-nowrap text-primary">Rs {{ number_format($grandTotal, 2) }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>

            </div>
        </div>
    </div>
@endsection

