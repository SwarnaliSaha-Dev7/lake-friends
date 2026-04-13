@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="member-list-part position-relative">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2 flex-wrap">
                    <h2 class="fs-5 common-heading mb-md-0 fw-semibold">Godown Stock List</h2>
                    <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addStockModal">+ Add Stock</button>
                </div>
                <div class="table-responsive">
                    <table class="table rounded-3 overflow-hidden clubmemberlist2" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th class="text-white fw-medium text-nowrap">Sl. No.</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Item Name</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Category</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Size (ml)</th>
                                <th class="text-white fw-medium align-middle text-nowrap">System Stock</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Status</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($liquorItems as $item)
                                @php
                                    $stock          = $stockMap[$item->id] ?? null;
                                    $qty            = $stock ? (int) $stock->quantity : 0;
                                    $isPending      = in_array($item->id, $pendingItemIds);
                                    $alertQty       = (int) ($item->low_stock_alert_qty ?? 0);
                                    $isOutOfStock   = $qty === 0;
                                    $isLowStock     = !$isOutOfStock && $alertQty > 0 && $qty <= $alertQty;
                                @endphp
                                <tr @if($isOutOfStock) class="table-danger" @elseif($isLowStock) class="table-warning" @endif>
                                    <td class="text-nowrap">{{ $loop->iteration }}</td>
                                    <td class="text-nowrap fw-medium">{{ $item->name }}</td>
                                    <td class="text-nowrap">{{ $item->foodItemCat->name ?? '—' }}</td>
                                    <td class="text-nowrap">{{ $item->size_ml ? $item->size_ml.' ml' : '—' }}</td>
                                    <td class="text-nowrap fw-semibold">
                                        {{ $qty }} BTL
                                        @if($isOutOfStock)
                                            <span class="badge bg-danger ms-1">Out of Stock</span>
                                        @elseif($isLowStock)
                                            <span class="badge bg-warning text-dark ms-1">
                                                <i class="fa-solid fa-triangle-exclamation"></i> Low (min {{ $alertQty }})
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">
                                        @if($isPending)
                                            <span class="badge bg-warning-subtle text-warning border border-warning rounded-pill px-2">Pending Approval</span>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">
                                        <button class="btn btn-sm btn-outline-info add-stock-btn"
                                            data-id="{{ $item->id }}"
                                            data-name="{{ $item->name }}"
                                            data-size="{{ $item->size_ml }}"
                                            title="Add Stock">
                                            <i class="fa-solid fa-plus"></i> Add
                                        </button>

                                        @if(!$isPending)
                                        <button class="btn btn-sm btn-outline-warning ms-1 adjust-stock-btn"
                                            data-id="{{ $item->id }}"
                                            data-name="{{ $item->name }}"
                                            data-qty="{{ $qty }}"
                                            data-size="{{ $item->size_ml }}"
                                            title="Physical Count Adjustment">
                                            <i class="fa-solid fa-scale-balanced"></i> Adjust
                                        </button>
                                        @else
                                        <span class="text-muted small ms-1">Adjust blocked</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold" style="background-color: #f0f0f0;">
                                <td colspan="4" class="text-end pe-3">Total</td>
                                <td class="text-nowrap">{{ $stockMap->sum('quantity') }} BTL</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modalComponent')

    {{-- Add Stock Modal --}}
    <div class="modal fade" id="addStockModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fs-5 fw-semibold">Add Stock to Godown</h5>
                    <button type="button" class="btn-close bg-transparent fs-5 lh-1" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-regular fa-circle-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addStockForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><small>Item</small></label>
                            <select name="food_items_id" id="stock_item_select" class="form-select py-2 shadow-none w-100" required>
                                <option value="">Select Item</option>
                                @foreach($liquorItems as $item)
                                    <option value="{{ $item->id }}" data-size="{{ $item->size_ml }}">
                                        {{ $item->name }}{{ $item->size_ml ? ' ('.$item->size_ml.'ml)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="error-div text-danger small"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <small>Quantity <span class="badge bg-secondary fw-normal ms-1">Bottle</span></small>
                            </label>
                            <input type="number" name="quantity" id="stock_quantity" class="form-control py-2 shadow-none"
                                placeholder="Enter number of bottles" min="1" required>
                            <div class="text-muted mt-1" style="font-size:0.78rem;" id="stock_size_hint"></div>
                            <div class="error-div text-danger small"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <small>Purchase Price per Bottle (₹) <span class="text-danger">*</span></small>
                            </label>
                            <input type="number" name="unit_price" id="stock_unit_price" class="form-control py-2 shadow-none"
                                placeholder="e.g. 1500" min="0" step="0.01" required>
                            <div class="text-muted mt-1" style="font-size:0.78rem;" id="stock_total_hint"></div>
                            <div class="error-div text-danger small"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><small>Notes (optional)</small></label>
                            <textarea name="notes" id="stock_notes" class="form-control py-2 shadow-none" rows="2"
                                placeholder="e.g. Supplier name, invoice no."></textarea>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary fw-semibold" id="addStock_submit">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Physical Count Adjustment Modal --}}
    <div class="modal fade" id="adjustStockModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fs-5 fw-semibold">Physical Stock Count</h5>
                    <button type="button" class="btn-close bg-transparent fs-5 lh-1" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-regular fa-circle-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="adjustStockForm">
                        @csrf
                        <input type="hidden" name="food_items_id" id="adj_item_id">

                        {{-- Item info --}}
                        <div class="mb-3 p-3 rounded-3 bg-light">
                            <div class="fw-semibold" id="adj_item_name"></div>
                            <div class="text-muted small" id="adj_item_size"></div>
                        </div>

                        {{-- System stock vs physical count --}}
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold"><small>System Stock</small></label>
                                <div class="form-control py-2 bg-light fw-bold" id="adj_system_qty" style="cursor:default;"></div>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold"><small>Physical Count <span class="badge bg-secondary fw-normal">Bottle</span></small></label>
                                <input type="number" name="physical_count" id="adj_physical_count"
                                    class="form-control py-2 shadow-none" placeholder="Count you see" min="0" required>
                                <div class="error-div text-danger small"></div>
                            </div>
                        </div>

                        {{-- Difference (live) --}}
                        <div class="mb-3 p-3 rounded-3 border" id="adj_diff_box" style="display:none;">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-semibold">Adjustment:</span>
                                <span class="fw-bold fs-5" id="adj_diff_label"></span>
                            </div>
                            <div class="text-muted small mt-1" id="adj_diff_note"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold"><small>Reason <span class="text-danger">*</span></small></label>
                            <textarea name="reason" id="adj_reason" class="form-control py-2 shadow-none" rows="2"
                                placeholder="e.g. Entry error, breakage, theft, physical verification" required></textarea>
                            <div class="error-div text-danger small"></div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-warning fw-semibold text-dark" id="adjustStock_submit">
                                Submit Adjustment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('customJS')
<script>
$(document).ready(function () {

    // ── ADD STOCK ──

    $('#stock_item_select').select2({
        placeholder: 'Search item...',
        allowClear: true,
        dropdownParent: $('#addStockModal'),
        width: '100%',
    });

    $('#stock_item_select').on('change', function () {
        var selected = $(this).find(':selected');
        var size = selected.data('size');
        $('#stock_size_hint').text(size ? 'Each bottle = ' + size + ' ml' : '');
        $(this).next('.error-div').text('');
    });

    $('.add-stock-btn').on('click', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        var id = $(this).data('id');
        $('#stock_item_select').val(id).trigger('change');
        $('#stock_quantity').val('');
        $('#stock_unit_price').val('');
        $('#stock_notes').val('');
        $('#stock_total_hint').text('');
        $('#addStockModal').modal('show');
        return false;
    });

    // Reset select2 when modal is closed
    $('#addStockModal').on('hidden.bs.modal', function () {
        $('#stock_item_select').val('').trigger('change');
        $('#stock_size_hint').text('');
        $('#stock_total_hint').text('');
    });

    // Live total value hint
    function updateTotalHint() {
        var qty   = parseFloat($('#stock_quantity').val())   || 0;
        var price = parseFloat($('#stock_unit_price').val()) || 0;
        if (qty > 0 && price > 0) {
            var total = qty * price;
            $('#stock_total_hint').text('Total purchase value: ₹' + total.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        } else {
            $('#stock_total_hint').text('');
        }
    }
    $('#stock_quantity').on('input', updateTotalHint);
    $('#stock_unit_price').on('input', updateTotalHint);

    $('#addStockForm').on('submit', function (e) {
        e.preventDefault();

        var isValid = true;
        var selectedItem = $('#stock_item_select').val();
        if (!selectedItem || selectedItem === '') {
            $('#stock_item_select').next('.error-div').text('Please select an item.');
            isValid = false;
        } else {
            $('#stock_item_select').next('.error-div').text('');
        }
        var qty = $('#stock_quantity').val();
        if (!qty || parseInt(qty) < 1) {
            $('#stock_quantity').next('.error-div').text('Enter a valid quantity (min 1).');
            isValid = false;
        } else {
            $('#stock_quantity').next('.error-div').text('');
        }
        var unitPrice = $('#stock_unit_price').val();
        if (!unitPrice || parseFloat(unitPrice) < 0) {
            $('#stock_unit_price').next('.error-div').text('Enter a valid purchase price.');
            isValid = false;
        } else {
            $('#stock_unit_price').next('.error-div').text('');
        }
        if (!isValid) return;

        var $btn = $('#addStock_submit');
        var orig = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

        $.ajax({
            url: '{{ route("godown-stock.store") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                $btn.html(orig).prop('disabled', false);
                if (response.statusCode == 200) {
                    toastr.success(response.message);
                    $('#addStockModal').modal('hide');
                    setTimeout(function () { location.reload(); }, 1500);
                } else {
                    toastr.error(response.error || response.message || 'Something went wrong.');
                }
            },
            error: function () {
                $btn.html(orig).prop('disabled', false);
                toastr.error('Something went wrong.');
            }
        });
    });

    // ── PHYSICAL COUNT ADJUSTMENT ──

    var adjSystemQty = 0;

    $('.adjust-stock-btn').on('click', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        adjSystemQty = parseInt($(this).data('qty')) || 0;
        var size     = $(this).data('size');

        $('#adj_item_id').val($(this).data('id'));
        $('#adj_item_name').text($(this).data('name'));
        $('#adj_item_size').text(size ? 'Bottle size: ' + size + ' ml' : '');
        $('#adj_system_qty').text(adjSystemQty + ' BTL');
        $('#adj_physical_count').val('');
        $('#adj_reason').val('');
        $('#adj_diff_box').hide();

        $('#adjustStockModal').modal('show');
        return false;
    });

    // Live difference calculation
    $('#adj_physical_count').on('input', function () {
        var physical = parseInt($(this).val());
        if (isNaN(physical) || physical < 0) {
            $('#adj_diff_box').hide();
            return;
        }

        var diff = physical - adjSystemQty;
        var diffBox  = $('#adj_diff_box');
        var diffLabel = $('#adj_diff_label');
        var diffNote  = $('#adj_diff_note');

        diffBox.show().removeClass('border-success border-danger border-secondary');

        if (diff === 0) {
            diffLabel.text('No change').attr('class', 'fw-bold fs-5 text-secondary');
            diffNote.text('Physical count matches system stock.');
            diffBox.addClass('border-secondary');
        } else if (diff > 0) {
            diffLabel.text('+' + diff + ' BTL').attr('class', 'fw-bold fs-5 text-success');
            diffNote.text('System stock will increase by ' + diff + ' bottle(s).');
            diffBox.addClass('border-success');
        } else {
            diffLabel.text(diff + ' BTL').attr('class', 'fw-bold fs-5 text-danger');
            diffNote.text('System stock will decrease by ' + Math.abs(diff) + ' bottle(s).');
            diffBox.addClass('border-danger');
        }
    });

    $('#adjustStockForm').on('submit', function (e) {
        e.preventDefault();

        var isValid = true;
        var physical = $('#adj_physical_count').val();
        if (physical === '' || parseInt(physical) < 0) {
            $('#adj_physical_count').next('.error-div').text('Enter a valid physical count (0 or more).');
            isValid = false;
        } else {
            $('#adj_physical_count').next('.error-div').text('');
        }
        if (!$('#adj_reason').val().trim()) {
            $('#adj_reason').next('.error-div').text('Reason is required.');
            isValid = false;
        } else {
            $('#adj_reason').next('.error-div').text('');
        }
        if (!isValid) return;

        var $btn = $('#adjustStock_submit');
        var orig = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

        $.ajax({
            url: '{{ route("godown-stock.adjust") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                $btn.html(orig).prop('disabled', false);
                if (response.statusCode == 200) {
                    toastr.success(response.message);
                    $('#adjustStockModal').modal('hide');
                    setTimeout(function () { location.reload(); }, 1500);
                } else {
                    toastr.error(response.error || response.message || 'Something went wrong.');
                }
            },
            error: function () {
                $btn.html(orig).prop('disabled', false);
                toastr.error('Something went wrong.');
            }
        });
    });

});
</script>
@endsection
