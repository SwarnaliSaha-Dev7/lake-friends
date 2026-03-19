@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')

    {{-- Date Filter --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="member-list-part p-3">
                <div class="fw-semibold mb-2">Filter by date</div>
                <form method="GET" action="{{ route('bar-stock.report') }}">
                    <div class="d-flex align-items-end gap-3 flex-wrap">
                        <div>
                            <label class="form-label mb-1"><small>From</small></label>
                            <input type="date" name="from" class="form-control shadow-none py-2"
                                value="{{ $from->toDateString() }}">
                        </div>
                        <div>
                            <label class="form-label mb-1"><small>To</small></label>
                            <input type="date" name="to" class="form-control shadow-none py-2"
                                value="{{ $to->toDateString() }}">
                        </div>
                        <div>
                            <button type="submit" class="btn btn-info fw-semibold">Apply</button>
                            <a href="{{ route('bar-stock.report') }}" class="btn btn-outline-secondary ms-1">Today</a>
                        </div>
                        <div class="ms-auto">
                            <a href="{{ route('bar-stock.report.download', request()->only('from','to')) }}"
                                class="btn btn-danger fw-semibold">
                                <i class="fa-solid fa-file-pdf me-1"></i> Download PDF
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-between"
                style="background: linear-gradient(135deg, #1e9de8, #0d6efd);">
                <div>
                    <div class="small mb-1 opacity-75">Opening Stock</div>
                    <div class="fs-4 fw-bold">{{ number_format($totalOpening, 2) }} BTL</div>
                    <div class="small opacity-75" style="font-size:10px;">Bottle equivalents</div>
                </div>
                <i class="fa-solid fa-wine-bottle fs-2 opacity-50"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-between"
                style="background: linear-gradient(135deg, #28c76f, #1a9e52);">
                <div>
                    <div class="small mb-1 opacity-75">IN from Godown</div>
                    <div class="fs-4 fw-bold">+{{ number_format($totalIn, 2) }} BTL</div>
                    <div class="small opacity-75" style="font-size:10px;">Bottle equivalents</div>
                </div>
                <i class="fa-solid fa-arrow-down fs-2 opacity-50"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-between"
                style="background: linear-gradient(135deg, #ff9f43, #e08020);">
                <div>
                    <div class="small mb-1 opacity-75">OUT (Sales)</div>
                    <div class="fs-4 fw-bold">−{{ number_format($totalOut, 2) }} BTL</div>
                    <div class="small opacity-75" style="font-size:10px;">Bottle equivalents</div>
                </div>
                <i class="fa-solid fa-arrow-up fs-2 opacity-50"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-between"
                style="background: linear-gradient(135deg, #7367f0, #5e50ee);">
                <div>
                    <div class="small mb-1 opacity-75">Closing Stock</div>
                    <div class="fs-4 fw-bold">{{ number_format($totalClosing, 2) }} BTL</div>
                    <div class="small opacity-75" style="font-size:10px;">Bottle equivalents</div>
                </div>
                <i class="fa-solid fa-boxes-stacked fs-2 opacity-50"></i>
            </div>
        </div>
    </div>

    {{-- Report Table --}}
    <div class="row">
        <div class="col-12">
            <div class="member-list-part position-relative">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2 flex-wrap">
                    <h2 class="fs-5 common-heading mb-0 fw-semibold">
                        Bar Stock Report
                        <small class="text-muted fw-normal fs-6 ms-2">
                            {{ $from->format('d M Y') }}
                            @if($from->toDateString() !== $to->toDateString())
                                — {{ $to->format('d M Y') }}
                            @endif
                        </small>
                    </h2>
                    <small class="text-muted">* Spirits shown in ml | Beer shown in BTL | Totals in bottle equivalents</small>
                </div>
                <div class="table-responsive">
                    <table class="table rounded-3 overflow-hidden clubmemberlist2" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th class="text-white fw-medium text-nowrap">Sl.</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Item Name</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Category</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Type</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Size</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Opening</th>
                                <th class="text-white fw-medium align-middle text-nowrap">IN from Godown (+)</th>
                                <th class="text-white fw-medium align-middle text-nowrap">OUT Sales (−)</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Closing</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reportData as $index => $row)
                                @php
                                    $alertQty = (int) ($row['item']->low_stock_alert_qty ?? 0);
                                    $sizeMl   = $row['size_ml'];
                                    $isBeer   = $row['is_beer'];
                                    $unit     = $row['unit'];
                                    $closing  = $row['closing_qty'];
                                    $closingBtlEq = $isBeer ? $closing : ($sizeMl > 0 ? floor($closing / $sizeMl) : 0);
                                    $isOut    = $closing === 0;
                                    $isLow    = !$isOut && $alertQty > 0 && $closingBtlEq <= $alertQty;

                                    $fmtQty = function($qty, $prefix = '') use ($isBeer, $sizeMl, $unit) {
                                        if ($qty <= 0) return '—';
                                        if ($isBeer) return $prefix . number_format($qty) . ' BTL';
                                        $btl = $sizeMl > 0 ? (int) floor($qty / $sizeMl) : 0;
                                        $rem = $sizeMl > 0 ? ($qty % $sizeMl) : $qty;
                                        $breakdown = $btl > 0
                                            ? ' (' . $btl . ' BTL' . ($rem > 0 ? ' ' . number_format($rem) . ' ml' : '') . ')'
                                            : '';
                                        return $prefix . number_format($qty) . ' ml' . $breakdown;
                                    };
                                @endphp
                                <tr @if($isOut && $row['opening_qty'] > 0) class="table-danger" @elseif($isLow) class="table-warning" @endif>
                                    <td class="text-nowrap">{{ $index + 1 }}</td>
                                    <td class="text-nowrap fw-medium">{{ $row['item']->name }}</td>
                                    <td class="text-nowrap">{{ $row['item']->foodItemCat->name ?? '—' }}</td>
                                    <td class="text-nowrap">
                                        @if($isBeer)
                                            <span class="badge bg-warning text-dark">Beer</span>
                                        @else
                                            <span class="badge bg-info text-white">Spirit</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">{{ $sizeMl ? $sizeMl . ' ml' : '—' }}</td>
                                    <td class="text-nowrap">{{ $fmtQty($row['opening_qty']) }}</td>
                                    <td class="text-nowrap text-success fw-semibold">{{ $fmtQty($row['in_qty'], '+') }}</td>
                                    <td class="text-nowrap text-danger fw-semibold">{{ $fmtQty($row['out_qty'], '−') }}</td>
                                    <td class="text-nowrap fw-bold">
                                        {{ $fmtQty($closing) }}
                                        @if($isOut && $row['opening_qty'] > 0)
                                            <span class="badge bg-danger ms-1">Empty</span>
                                        @elseif($isLow)
                                            <span class="badge bg-warning text-dark ms-1">
                                                <i class="fa-solid fa-triangle-exclamation"></i> Low
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection
