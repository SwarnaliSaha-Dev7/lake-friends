@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')

    {{-- Date Filter --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="member-list-part p-3">
                <div class="fw-semibold mb-2">Filter by date</div>
                <form method="GET" action="{{ route('godown-stock.report') }}" id="reportFilterForm">
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
                            <a href="{{ route('godown-stock.report') }}" class="btn btn-outline-secondary ms-1">Today</a>
                        </div>
                        <div class="ms-auto">
                            <a href="{{ route('godown-stock.report.download', request()->only('from','to')) }}"
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
                    <div class="small mb-1 opacity-75">Total Opening Stock</div>
                    <div class="fs-4 fw-bold">{{ number_format($totalOpening) }} BTL</div>
                </div>
                <i class="fa-solid fa-wine-bottle fs-2 opacity-50"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-between"
                style="background: linear-gradient(135deg, #28c76f, #1a9e52);">
                <div>
                    <div class="small mb-1 opacity-75">Total IN (Period)</div>
                    <div class="fs-4 fw-bold">+{{ number_format($totalIn) }} BTL</div>
                </div>
                <i class="fa-solid fa-arrow-down fs-2 opacity-50"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-between"
                style="background: linear-gradient(135deg, #ff9f43, #e08020);">
                <div>
                    <div class="small mb-1 opacity-75">Total OUT (Period)</div>
                    <div class="fs-4 fw-bold">−{{ number_format($totalOut) }} BTL</div>
                </div>
                <i class="fa-solid fa-arrow-up fs-2 opacity-50"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-between"
                style="background: linear-gradient(135deg, #ea5455, #c62828);">
                <div>
                    <div class="small mb-1 opacity-75">Transferred to Bar</div>
                    <div class="fs-4 fw-bold">−{{ number_format($totalTransfer) }} BTL</div>
                </div>
                <i class="fa-solid fa-arrow-right-arrow-left fs-2 opacity-50"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-between"
                style="background: linear-gradient(135deg, #7367f0, #5e50ee);">
                <div>
                    <div class="small mb-1 opacity-75">Total Closing Stock</div>
                    <div class="fs-4 fw-bold">{{ number_format($totalClosing) }} BTL</div>
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
                        Stock Report
                        <small class="text-muted fw-normal fs-6 ms-2">
                            {{ $from->format('d M Y') }}
                            @if($from->toDateString() !== $to->toDateString())
                                — {{ $to->format('d M Y') }}
                            @endif
                        </small>
                    </h2>
                </div>
                <div class="table-responsive">
                    <table class="table rounded-3 overflow-hidden clubmemberlist2" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th class="text-white fw-medium text-nowrap">Sl. No.</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Item Name</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Category</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Size (ml)</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Opening Stock</th>
                                <th class="text-white fw-medium align-middle text-nowrap">IN (+)</th>
                                <th class="text-white fw-medium align-middle text-nowrap">OUT (−)</th>
                                <th class="text-white fw-medium align-middle text-nowrap">To Bar (−)</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Closing Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reportData as $index => $row)
                                @php
                                    $alertQty     = (int) ($row['item']->low_stock_alert_qty ?? 0);
                                    $isOutOfStock = $row['closing_qty'] === 0;
                                    $isLowStock   = !$isOutOfStock && $alertQty > 0 && $row['closing_qty'] <= $alertQty;
                                @endphp
                                <tr @if($isOutOfStock) class="table-danger" @elseif($isLowStock) class="table-warning" @endif>
                                    <td class="text-nowrap">{{ $index + 1 }}</td>
                                    <td class="text-nowrap fw-medium">{{ $row['item']->name }}</td>
                                    <td class="text-nowrap">{{ $row['item']->foodItemCat->name ?? '—' }}</td>
                                    <td class="text-nowrap">{{ $row['item']->size_ml ? $row['item']->size_ml.' ml' : '—' }}</td>
                                    <td class="text-nowrap">{{ $row['opening_qty'] }} BTL</td>
                                    <td class="text-nowrap text-success fw-semibold">
                                        {{ $row['in_qty'] > 0 ? '+'.$row['in_qty'] : '—' }}
                                    </td>
                                    <td class="text-nowrap text-danger fw-semibold">
                                        {{ $row['out_qty'] > 0 ? '−'.$row['out_qty'] : '—' }}
                                    </td>
                                    <td class="text-nowrap fw-semibold" style="color: #ea5455;">
                                        {{ $row['transfer_qty'] > 0 ? '−'.$row['transfer_qty'] : '—' }}
                                    </td>
                                    <td class="text-nowrap fw-bold">
                                        {{ $row['closing_qty'] }} BTL
                                        @if($isOutOfStock)
                                            <span class="badge bg-danger ms-1">Out of Stock</span>
                                        @elseif($isLowStock)
                                            <span class="badge bg-warning text-dark ms-1">
                                                <i class="fa-solid fa-triangle-exclamation"></i> Low
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold" style="background-color: #f0f0f0;">
                                <td colspan="4" class="text-end pe-3">Total</td>
                                <td class="text-nowrap">{{ $totalOpening }} BTL</td>
                                <td class="text-nowrap text-success">+{{ $totalIn }}</td>
                                <td class="text-nowrap text-danger">−{{ $totalOut }}</td>
                                <td class="text-nowrap fw-semibold" style="color: #ea5455;">−{{ $totalTransfer }}</td>
                                <td class="text-nowrap">{{ $totalClosing }} BTL</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection
