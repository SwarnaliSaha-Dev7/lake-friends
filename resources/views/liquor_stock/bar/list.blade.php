@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')

@php
    $totalItems    = $liquorItems->count();
    $inStockCount  = 0;
    $lowStockCount = 0;
    $outOfStock    = 0;

    foreach ($liquorItems as $item) {
        $barStock  = $barStockMap[$item->id] ?? null;
        $barQty    = $barStock ? (int) $barStock->quantity : 0;
        $alertQty  = (int) ($item->low_stock_alert_qty ?? 0);
        $isBeer    = (bool) $item->is_beer;
        $sizeMl    = (int) ($item->size_ml ?? 1);
        // Convert bar qty to bottle equivalent for alert comparison
        $barBtlEq  = $isBeer ? $barQty : ($sizeMl > 0 ? floor($barQty / $sizeMl) : 0);

        if ($barQty === 0)                                           $outOfStock++;
        elseif ($alertQty > 0 && $barBtlEq <= $alertQty)            $lowStockCount++;
        else                                                         $inStockCount++;
    }
@endphp

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-between"
                style="background: linear-gradient(135deg, #1e9de8, #0d6efd);">
                <div>
                    <div class="small mb-1 opacity-75">Total Items</div>
                    <div class="fs-4 fw-bold">{{ $totalItems }}</div>
                </div>
                <i class="fa-solid fa-wine-bottle fs-2 opacity-50"></i>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-between"
                style="background: linear-gradient(135deg, #28c76f, #1a9e52);">
                <div>
                    <div class="small mb-1 opacity-75">In Stock</div>
                    <div class="fs-4 fw-bold">{{ $inStockCount }}</div>
                </div>
                <i class="fa-solid fa-circle-check fs-2 opacity-50"></i>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-between"
                style="background: linear-gradient(135deg, #ff9f43, #e08020);">
                <div>
                    <div class="small mb-1 opacity-75">Low Stock</div>
                    <div class="fs-4 fw-bold">{{ $lowStockCount }}</div>
                </div>
                <i class="fa-solid fa-triangle-exclamation fs-2 opacity-50"></i>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-between"
                style="background: linear-gradient(135deg, #ea5455, #c62828);">
                <div>
                    <div class="small mb-1 opacity-75">Out of Stock</div>
                    <div class="fs-4 fw-bold">{{ $outOfStock }}</div>
                </div>
                <i class="fa-solid fa-circle-xmark fs-2 opacity-50"></i>
            </div>
        </div>
    </div>

    {{-- Low / Out of Stock Alert --}}
    @php
        $alertItems = $liquorItems->filter(function ($item) use ($barStockMap) {
            $barStock  = $barStockMap[$item->id] ?? null;
            $barQty    = $barStock ? (int) $barStock->quantity : 0;
            $alertQty  = (int) ($item->low_stock_alert_qty ?? 0);
            $isBeer    = (bool) $item->is_beer;
            $sizeMl    = (int) ($item->size_ml ?? 1);
            $barBtlEq  = $isBeer ? $barQty : ($sizeMl > 0 ? floor($barQty / $sizeMl) : 0);
            return $barQty === 0 || ($alertQty > 0 && $barBtlEq <= $alertQty);
        });
    @endphp
    @if($alertItems->isNotEmpty())
        <div class="alert alert-warning border-warning d-flex align-items-start gap-3 mb-4 rounded-3" role="alert">
            <i class="fa-solid fa-triangle-exclamation fs-5 text-warning mt-1 flex-shrink-0"></i>
            <div>
                <div class="fw-semibold mb-1">Stock Alert — {{ $alertItems->count() }} item(s) need attention</div>
                <div class="d-flex flex-wrap gap-2 mt-1">
                    @foreach($alertItems as $aItem)
                        @php
                            $aBarQty  = isset($barStockMap[$aItem->id]) ? (int) $barStockMap[$aItem->id]->quantity : 0;
                            $aIsOut   = $aBarQty === 0;
                        @endphp
                        <span class="badge rounded-pill px-3 py-1 {{ $aIsOut ? 'bg-danger' : 'bg-warning text-dark' }}" style="font-size:0.78rem;">
                            <i class="fa-solid fa-{{ $aIsOut ? 'circle-xmark' : 'triangle-exclamation' }} me-1"></i>
                            {{ $aItem->name }} — {{ $aIsOut ? 'Out of Stock' : 'Low Stock' }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Bar Stock Table --}}
    <div class="row">
        <div class="col-12">
            <div class="member-list-part position-relative">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-3 flex-wrap">
                    <h2 class="fs-5 common-heading mb-0 fw-semibold">Current Bar Stock</h2>
                    <a href="{{ route('bar-stock.report') }}" class="btn btn-outline-info btn-sm fw-semibold">
                        <i class="fa-solid fa-chart-bar me-1"></i> Bar Report
                    </a>
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
                                <th class="text-white fw-medium align-middle text-nowrap">Bar Stock</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Godown Stock</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Status</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($liquorItems as $index => $item)
                                @php
                                    $barStock    = $barStockMap[$item->id]    ?? null;
                                    $godownStock = $godownStockMap[$item->id] ?? null;
                                    $barQty      = $barStock    ? (int) $barStock->quantity    : 0;
                                    $godownQty   = $godownStock ? (int) $godownStock->quantity : 0;
                                    $isPending   = in_array($item->id, $pendingItemIds);
                                    $isBeer      = (bool) $item->is_beer;
                                    $sizeMl      = (int) ($item->size_ml ?? 1);
                                    $alertQty    = (int) ($item->low_stock_alert_qty ?? 0);
                                    $barBtlEq    = $isBeer ? $barQty : ($sizeMl > 0 ? floor($barQty / $sizeMl) : 0);
                                    $isOut       = $barQty === 0;
                                    $isLow       = !$isOut && $alertQty > 0 && $barBtlEq <= $alertQty;

                                    $barDisplay  = $isBeer
                                        ? $barQty . ' BTL'
                                        : number_format($barQty) . ' ml' . ($barQty > 0 && $sizeMl > 0 ? ' (' . floor($barQty / $sizeMl) . ' BTL)' : '');
                                @endphp
                                <tr @if($isOut) class="table-danger" @elseif($isLow) class="table-warning" @endif>
                                    <td class="text-nowrap">{{ $index + 1 }}</td>
                                    <td class="text-nowrap fw-medium">{{ $item->name }}</td>
                                    <td class="text-nowrap">{{ $item->foodItemCat->name ?? '—' }}</td>
                                    <td class="text-nowrap">
                                        @if($isBeer)
                                            <span class="badge bg-warning text-dark">Beer</span>
                                        @else
                                            <span class="badge bg-info text-white">Spirit</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">{{ $item->size_ml ? $item->size_ml . ' ml' : '—' }}</td>
                                    <td class="text-nowrap fw-bold">{{ $barDisplay }}</td>
                                    <td class="text-nowrap">{{ $godownQty }} BTL</td>
                                    <td class="text-nowrap">
                                        @if($isOut)
                                            <span class="badge bg-danger">Out of Stock</span>
                                        @elseif($isLow)
                                            <span class="badge bg-warning text-dark">
                                                <i class="fa-solid fa-triangle-exclamation"></i> Low Stock
                                            </span>
                                        @else
                                            <span class="badge bg-success">In Stock</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">
                                        @if($isPending)
                                            <span class="badge bg-warning text-dark px-2 py-2">
                                                <i class="fa-solid fa-clock"></i> Pending Approval
                                            </span>
                                        @else
                                            <button type="button"
                                                class="btn btn-sm btn-primary btn-transfer-stock"
                                                data-id="{{ $item->id }}"
                                                data-name="{{ $item->name }}"
                                                data-is-beer="{{ $isBeer ? '1' : '0' }}"
                                                data-size-ml="{{ $item->size_ml ?? 0 }}"
                                                data-godown-qty="{{ $godownQty }}"
                                                @if($godownQty === 0) disabled title="No godown stock available" @endif>
                                                <i class="fa-solid fa-arrow-right-arrow-left"></i> Transfer to Bar
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">No liquor items found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Transfer Modal --}}
    <div class="modal fade" id="transferModal" tabindex="-1" aria-labelledby="transferModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-semibold" id="transferModalLabel">
                        <i class="fa-solid fa-arrow-right-arrow-left me-2 text-primary"></i>Transfer to Bar
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="transferForm">
                    @csrf
                    <input type="hidden" id="transfer_item_id" name="food_items_id">
                    <div class="modal-body">
                        <div class="row g-3 mb-3">
                            <div class="col-8">
                                <label class="form-label fw-semibold">Item</label>
                                <input type="text" id="transfer_item_name" class="form-control bg-light" readonly>
                            </div>
                            <div class="col-4">
                                <label class="form-label fw-semibold">Godown Available</label>
                                <input type="text" id="transfer_godown_display" class="form-control bg-light" readonly>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Bottles to Transfer <span class="text-danger">*</span></label>
                            <input type="number" id="transfer_bottles" name="bottles"
                                class="form-control shadow-none" min="1"
                                placeholder="Enter number of bottles">
                            <div class="form-text text-muted" id="transfer_hint"></div>
                        </div>
                        <div class="mb-3 p-3 rounded-3 border bg-light" id="transfer_preview_row" style="display:none;">
                            <div class="small text-muted mb-1">Bar Will Receive</div>
                            <div id="transfer_preview" class="fw-bold text-success fs-5"></div>
                        </div>
                        <div class="mb-1">
                            <label class="form-label fw-semibold">Notes <small class="text-muted fw-normal">(optional)</small></label>
                            <textarea name="notes" id="transfer_notes" class="form-control shadow-none"
                                rows="2" maxlength="500" placeholder="Reason for transfer..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-semibold" id="transferSubmitBtn">
                            <i class="fa-solid fa-arrow-right-arrow-left me-1"></i> Transfer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('customJS')
<script>
$(document).ready(function () {

    var currentIsBeer    = false;
    var currentSizeMl    = 0;
    var currentGodownQty = 0;

    $(document).on('click', '.btn-transfer-stock', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        var $btn      = $(this);
        var id        = $btn.data('id');
        var name      = $btn.data('name');
        var isBeer    = $btn.data('is-beer') == '1';
        var sizeMl    = parseInt($btn.data('size-ml'))    || 0;
        var godownQty = parseInt($btn.data('godown-qty')) || 0;

        currentIsBeer    = isBeer;
        currentSizeMl    = sizeMl;
        currentGodownQty = godownQty;

        $('#transfer_item_id').val(id);
        $('#transfer_item_name').val(name);
        $('#transfer_godown_display').val(godownQty + ' BTL');
        $('#transfer_bottles').val('').attr('max', godownQty);
        $('#transfer_notes').val('');
        $('#transfer_preview_row').hide();
        $('#transfer_preview').text('');

        var hint = isBeer
            ? 'Each bottle = 1 bottle in bar.'
            : (sizeMl ? 'Each bottle = ' + sizeMl + ' ml in bar.' : 'Bottles will be converted to ml.');
        $('#transfer_hint').text(hint);

        $('#transferModal').modal('show');
        setTimeout(function () { $('#transfer_bottles').focus(); }, 400);
    });

    $(document).on('input', '#transfer_bottles', function () {
        var bottles = parseInt($(this).val()) || 0;
        if (bottles <= 0) {
            $('#transfer_preview_row').hide();
            return;
        }

        var display;
        if (currentIsBeer) {
            display = bottles + ' BTL';
        } else {
            var ml  = bottles * currentSizeMl;
            display = ml.toLocaleString() + ' ml  (' + bottles + ' × ' + currentSizeMl + ' ml)';
        }

        $('#transfer_preview').text(display);
        $('#transfer_preview_row').show();
    });

    $('#transferForm').on('submit', function (e) {
        e.preventDefault();

        var bottles = parseInt($('#transfer_bottles').val()) || 0;

        if (!$('#transfer_item_id').val()) {
            toastr.warning('Item not selected.');
            return;
        }
        if (bottles < 1) {
            toastr.warning('Enter at least 1 bottle.');
            return;
        }
        if (bottles > currentGodownQty) {
            toastr.error('Cannot transfer more than godown stock (' + currentGodownQty + ' BTL).');
            return;
        }

        var $btn = $('#transferSubmitBtn');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Transferring...');

        $.ajax({
            url: '{{ route("bar-stock.transfer") }}',
            method: 'POST',
            data: {
                _token:        '{{ csrf_token() }}',
                food_items_id: $('#transfer_item_id').val(),
                bottles:       bottles,
                notes:         $('#transfer_notes').val(),
            },
            success: function (res) {
                if (res.statusCode === 200) {
                    toastr.success(res.message);
                    $('#transferModal').modal('hide');
                    setTimeout(function () { location.reload(); }, 800);
                } else {
                    toastr.error(res.message || 'Transfer failed.');
                }
            },
            error: function (xhr) {
                var msg = xhr.responseJSON && xhr.responseJSON.error
                    ? xhr.responseJSON.error
                    : 'Server error. Please try again.';
                toastr.error(msg);
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="fa-solid fa-arrow-right-arrow-left me-1"></i> Transfer');
            }
        });
    });

});
</script>
@endsection
