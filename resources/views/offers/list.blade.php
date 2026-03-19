@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="member-list-part position-relative">

                <div class="d-flex align-items-center justify-content-between gap-2 mb-3 flex-wrap">
                    <div>
                        <h2 class="fs-5 common-heading mb-1 fw-semibold">Promotions</h2>
                        <p class="mb-0 text-muted">Manage drink &amp; food specials &amp; offers</p>
                    </div>
                    <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#createoffer">
                        + Create Offer
                    </button>
                </div>

                <div class="bg-white py-3 px-4 rounded-3 border">
                    <div class="table-responsive">
                        <table class="table rounded-3 overflow-hidden clubmemberlist2" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th class="text-white fw-medium align-middle text-nowrap">Promotion</th>
                                    <th class="text-white fw-medium align-middle text-nowrap">Type</th>
                                    <th class="text-white fw-medium align-middle text-nowrap">Applicable On</th>
                                    <th class="text-white fw-medium align-middle text-nowrap">Duration</th>
                                    <th class="text-white fw-medium align-middle text-nowrap">Items</th>
                                    <th class="text-white fw-medium align-middle text-nowrap">Status</th>
                                    <th class="text-white fw-medium align-middle text-nowrap">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($offers as $offer)
                                    <tr>
                                        <td class="text-nowrap">
                                            <p class="fw-medium mb-1">{{ $offer->name }}</p>
                                            @if($offer->discount_value > 0)
                                                <small class="text-muted">
                                                    @if($offer->offerType?->slug === 'percentage')
                                                        {{ $offer->discount_value }}% off
                                                    @elseif($offer->offerType?->slug === 'flat')
                                                        &#8377;{{ $offer->discount_value }} off
                                                    @endif
                                                </small>
                                            @endif
                                        </td>
                                        <td class="text-nowrap">
                                            <span class="badge bg-warning-subtle text-secondary border border-warning rounded-pill px-3 py-2 fs-6">
                                                {{ $offer->offerType?->name ?? '—' }}
                                            </span>
                                        </td>
                                        <td class="text-nowrap text-capitalize">{{ $offer->applies_to ?? '—' }}</td>
                                        <td class="text-nowrap">
                                            @if($offer->start_at && $offer->end_at)
                                                <small>{{ \Carbon\Carbon::parse($offer->start_at)->format('d M Y') }}</small>
                                                <br>
                                                <small class="text-muted">to {{ \Carbon\Carbon::parse($offer->end_at)->format('d M Y') }}</small>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                @forelse($offer->offerItems->take(3) as $item)
                                                    <span class="badge bg-info-subtle text-secondary border border-info rounded-pill px-2 py-1">
                                                        {{ $item->foodItem?->name }}
                                                    </span>
                                                @empty
                                                    <span class="text-muted">—</span>
                                                @endforelse
                                                @if($offer->offerItems->count() > 3)
                                                    <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2 py-1">
                                                        +{{ $offer->offerItems->count() - 3 }} more
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-nowrap">
                                            @php
                                                $statusMap = [
                                                    'pending'  => ['label' => 'Pending',  'class' => 'text-warning'],
                                                    'active'   => ['label' => 'Active',   'class' => 'text-success'],
                                                    'rejected' => ['label' => 'Rejected', 'class' => 'text-danger'],
                                                    'expired'  => ['label' => 'Expired',  'class' => 'text-secondary'],
                                                    'draft'    => ['label' => 'Draft',    'class' => 'text-muted'],
                                                ];
                                                $s = $statusMap[$offer->status] ?? ['label' => ucfirst($offer->status), 'class' => 'text-muted'];
                                            @endphp
                                            <span class="fw-medium {{ $s['class'] }}">{{ $s['label'] }}</span>
                                        </td>
                                        <td class="text-nowrap">
                                            <button class="border-0 bg-light p-1 rounded-3 lh-1 action-btn edit-offer"
                                                data-id="{{ $offer->id }}" title="Edit">
                                                <small><i class="fa-solid fa-pen-to-square"></i></small>
                                            </button>
                                            <button class="border-0 bg-light p-1 rounded-3 lh-1 action-btn delete-offer ms-1"
                                                data-id="{{ $offer->id }}" title="Delete">
                                                <small><i class="fa-solid fa-trash"></i></small>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No offers found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('modalComponent')

    {{-- ── CREATE OFFER MODAL ──────────────────────────────────────────────── --}}
    <div class="modal fade" id="createoffer" tabindex="-1" aria-labelledby="createofferLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fs-5 fw-semibold" id="createofferLabel">Create New Offer</h5>
                    <button type="button" class="btn-close bg-transparent fs-5 lh-1" data-bs-dismiss="modal"
                        aria-label="Close"><i class="fa-regular fa-circle-xmark"></i></button>
                </div>
                <div class="modal-body">
                    <form id="createOfferForm">
                        @csrf
                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="form-label fw-semibold text-dark">
                                    <span class="text-info rounded-3 label-icon p-1 d-inline-flex align-items-center justify-content-center me-2">
                                        <i class="fa-solid fa-tag"></i>
                                    </span> Offer Details
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-part mb-3">
                                    <label class="form-label"><small>Offer Name <span class="text-danger">*</span></small></label>
                                    <input type="text" name="name" id="offerName" class="form-control py-2 shadow-none" placeholder="e.g. Weekend Special">
                                    <div class="error-div text-danger small"></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-part mb-3">
                                    <label class="form-label"><small>Offer Type <span class="text-danger">*</span></small></label>
                                    <select name="offer_type_id" id="offerTypeId" class="form-select py-2 shadow-none">
                                        <option value="" selected hidden disabled>Select Offer Type</option>
                                        @foreach($offerTypes as $type)
                                            <option value="{{ $type->id }}" data-slug="{{ $type->slug }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="error-div text-danger small"></div>
                                </div>
                            </div>
                            <div class="col-lg-6 d-none" id="discountValueGroup">
                                <div class="form-part mb-3">
                                    <label class="form-label"><small id="discountLabel">Discount Value <span class="text-danger">*</span></small></label>
                                    <div class="input-group">
                                        <input type="number" name="discount_value" id="discountValue" class="form-control py-2 shadow-none" placeholder="0" min="0" step="0.01">
                                        <span class="input-group-text" id="discountSuffix">%</span>
                                    </div>
                                    <div class="error-div text-danger small"></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-part mb-3">
                                    <label class="form-label"><small>Offer Duration <span class="text-danger">*</span></small></label>
                                    <input type="text" id="offerDateRange" class="form-control py-2 shadow-none" placeholder="Select date range" readonly>
                                    <input type="hidden" name="start_at" id="offerStartAt">
                                    <input type="hidden" name="end_at"   id="offerEndAt">
                                    <div class="text-danger small" id="dateRangeError"></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-part mb-3">
                                    <label class="form-label"><small>Applicable On <span class="text-danger">*</span></small></label>
                                    <select name="applies_to" id="appliesToSelect" class="form-select py-2 shadow-none">
                                        <option value="" selected hidden disabled>Select</option>
                                        <option value="food">Food</option>
                                        <option value="liquor">Liquor</option>
                                        <option value="both">Both</option>
                                    </select>
                                    <div class="error-div text-danger small"></div>
                                </div>
                            </div>
                            <div class="col-12 d-none" id="itemsGroup">
                                <div class="form-part mb-3">
                                    <label class="form-label"><small>Select Items <span class="text-danger">*</span></small></label>
                                    <select name="items[]" id="itemsSelect" class="form-select shadow-none" multiple></select>
                                    <div class="text-danger small" id="itemsError"></div>
                                </div>
                            </div>
                            <div class="col-12 mt-2">
                                <div class="text-end mod-footer">
                                    <button type="button" class="btn btn-info fw-semibold" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary fw-semibold" id="createOffer_submit">Submit for Approval</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ── EDIT OFFER MODAL ────────────────────────────────────────────────── --}}
    <div class="modal fade" id="editoffer" tabindex="-1" aria-labelledby="editofferLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fs-5 fw-semibold" id="editofferLabel">Edit Offer</h5>
                    <button type="button" class="btn-close bg-transparent fs-5 lh-1" data-bs-dismiss="modal"
                        aria-label="Close"><i class="fa-regular fa-circle-xmark"></i></button>
                </div>
                <div class="modal-body">
                    <form id="editOfferForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="edit_offer_id">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="form-label fw-semibold text-dark">
                                    <span class="text-info rounded-3 label-icon p-1 d-inline-flex align-items-center justify-content-center me-2">
                                        <i class="fa-solid fa-tag"></i>
                                    </span> Edit Offer Details
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-part mb-3">
                                    <label class="form-label"><small>Offer Name <span class="text-danger">*</span></small></label>
                                    <input type="text" name="name" id="edit_offerName" class="form-control py-2 shadow-none" placeholder="e.g. Weekend Special">
                                    <div class="error-div text-danger small"></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-part mb-3">
                                    <label class="form-label"><small>Offer Type <span class="text-danger">*</span></small></label>
                                    <select name="offer_type_id" id="edit_offerTypeId" class="form-select py-2 shadow-none">
                                        <option value="" selected hidden disabled>Select Offer Type</option>
                                        @foreach($offerTypes as $type)
                                            <option value="{{ $type->id }}" data-slug="{{ $type->slug }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="error-div text-danger small"></div>
                                </div>
                            </div>
                            <div class="col-lg-6 d-none" id="edit_discountValueGroup">
                                <div class="form-part mb-3">
                                    <label class="form-label"><small id="edit_discountLabel">Discount Value <span class="text-danger">*</span></small></label>
                                    <div class="input-group">
                                        <input type="number" name="discount_value" id="edit_discountValue" class="form-control py-2 shadow-none" placeholder="0" min="0" step="0.01">
                                        <span class="input-group-text" id="edit_discountSuffix">%</span>
                                    </div>
                                    <div class="error-div text-danger small"></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-part mb-3">
                                    <label class="form-label"><small>Offer Duration <span class="text-danger">*</span></small></label>
                                    <input type="text" id="edit_offerDateRange" class="form-control py-2 shadow-none" placeholder="Select date range" readonly>
                                    <input type="hidden" name="start_at" id="edit_offerStartAt">
                                    <input type="hidden" name="end_at"   id="edit_offerEndAt">
                                    <div class="text-danger small" id="edit_dateRangeError"></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-part mb-3">
                                    <label class="form-label"><small>Applicable On <span class="text-danger">*</span></small></label>
                                    <select name="applies_to" id="edit_appliesToSelect" class="form-select py-2 shadow-none">
                                        <option value="" selected hidden disabled>Select</option>
                                        <option value="food">Food</option>
                                        <option value="liquor">Liquor</option>
                                        <option value="both">Both</option>
                                    </select>
                                    <div class="error-div text-danger small"></div>
                                </div>
                            </div>
                            <div class="col-12" id="edit_itemsGroup">
                                <div class="form-part mb-3">
                                    <label class="form-label"><small>Select Items <span class="text-danger">*</span></small></label>
                                    <select name="items[]" id="edit_itemsSelect" class="form-select shadow-none" multiple></select>
                                    <div class="text-danger small" id="edit_itemsError"></div>
                                </div>
                            </div>
                            <div class="col-12 mt-2">
                                <div class="text-end mod-footer">
                                    <button type="button" class="btn btn-info fw-semibold" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary fw-semibold" id="editOffer_submit">Submit Edit for Approval</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ── PENDING BLOCK MODAL ─────────────────────────────────────────────── --}}
    <div class="modal fade" id="pendingBlockModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-3">
                <div class="modal-header border-0">
                    <h5 class="modal-title"><i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>Action Blocked</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0" id="pendingBlockMessage"></p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── DELETE CONFIRM MODAL ────────────────────────────────────────────── --}}
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-3">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Submit Delete Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">This will submit a <strong>delete request</strong> for approval. The offer will be deleted only after checker approval.</p>
                </div>
                <input type="hidden" id="delete_offer_id">
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Yes, Submit</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('customJS')
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
$(document).ready(function () {

    const foodItems    = @json($foodItems);
    const liquorItems  = @json($liquorItems);
    const takenItemIds = @json($takenItemIds);  // IDs already in an active offer

    let currentEditOwnIds = [];  // items belonging to the offer being edited

    // ── Shared: load items into a select2 ─────────────────────────────────
    function loadItemsInto(selectId, modalId, type, selectedIds = [], ownIds = []) {
        const $sel = $('#' + selectId);
        $sel.empty();

        let items = [];
        if (type === 'food')   items = foodItems;
        if (type === 'liquor') items = liquorItems;
        if (type === 'both')   items = [...foodItems, ...liquorItems];

        items.forEach(function (item) {
            const isTaken   = takenItemIds.includes(item.id) && !ownIds.includes(item.id);
            const baseLabel = type === 'both' ? item.name + ' (' + (item.item_type || '') + ')' : item.name;
            const label     = isTaken ? baseLabel + ' — already has active offer' : baseLabel;
            const selected  = selectedIds.includes(item.id);
            const option    = new Option(label, item.id, selected, selected);
            if (isTaken) {
                $(option).prop('disabled', true).css('color', '#aaa');
            }
            $sel.append(option);
        });

        $sel.trigger('change');
    }

    // ── Shared: show/hide discount field ──────────────────────────────────
    function handleDiscountField(slug, prefix) {
        const group  = $('#' + prefix + 'discountValueGroup');
        const label  = $('#' + prefix + 'discountLabel');
        const suffix = $('#' + prefix + 'discountSuffix');
        const input  = $('#' + prefix + 'discountValue');

        if (slug === 'percentage') {
            group.removeClass('d-none');
            label.html('Percentage (%) <span class="text-danger">*</span>');
            suffix.text('%');
            input.attr('placeholder', 'e.g. 10');
        } else if (slug === 'flat') {
            group.removeClass('d-none');
            label.html('Flat Amount (₹) <span class="text-danger">*</span>');
            suffix.text('₹');
            input.attr('placeholder', 'e.g. 100');
        } else {
            group.addClass('d-none');
            input.val('');
        }
    }

    // ── Shared: validate offer form ────────────────────────────────────────
    function validateOfferForm(prefix) {
        let isValid = true;

        const name = $('#' + prefix + 'offerName').val().trim();
        if (!name) {
            $('#' + prefix + 'offerName').addClass('is-invalid').next('.error-div').text('Offer name is required');
            isValid = false;
        } else {
            $('#' + prefix + 'offerName').removeClass('is-invalid').next('.error-div').text('');
        }

        if (!$('#' + prefix + 'offerTypeId').val()) {
            $('#' + prefix + 'offerTypeId').addClass('is-invalid').next('.error-div').text('Please select an offer type');
            isValid = false;
        } else {
            $('#' + prefix + 'offerTypeId').removeClass('is-invalid').next('.error-div').text('');
        }

        const slug = $('#' + prefix + 'offerTypeId').find(':selected').data('slug');
        if ((slug === 'percentage' || slug === 'flat') && !$('#' + prefix + 'discountValue').val()) {
            $('#' + prefix + 'discountValue').addClass('is-invalid');
            $('#' + prefix + 'discountValue').closest('.input-group').next('.error-div').text('Discount value is required');
            isValid = false;
        } else {
            $('#' + prefix + 'discountValue').removeClass('is-invalid');
            $('#' + prefix + 'discountValue').closest('.input-group').next('.error-div').text('');
        }

        if (!$('#' + prefix + 'offerStartAt').val() || !$('#' + prefix + 'offerEndAt').val()) {
            $('#' + prefix + 'dateRangeError').text('Please select offer duration');
            isValid = false;
        } else {
            $('#' + prefix + 'dateRangeError').text('');
        }

        if (!$('#' + prefix + 'appliesToSelect').val()) {
            $('#' + prefix + 'appliesToSelect').addClass('is-invalid').next('.error-div').text('Please select applicable on');
            isValid = false;
        } else {
            $('#' + prefix + 'appliesToSelect').removeClass('is-invalid').next('.error-div').text('');
        }

        const selItems = $('#' + prefix + 'itemsSelect').val();
        if (!selItems || selItems.length === 0) {
            $('#' + prefix + 'itemsError').text('Please select at least one item');
            isValid = false;
        } else {
            $('#' + prefix + 'itemsError').text('');
        }

        return isValid;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // CREATE OFFER
    // ═══════════════════════════════════════════════════════════════════════

    $('#offerDateRange').daterangepicker({ autoUpdateInput: false, minDate: moment(), locale: { cancelLabel: 'Clear', format: 'DD/MM/YYYY' } });
    $('#offerDateRange').on('apply.daterangepicker', function (ev, picker) {
        $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
        $('#offerStartAt').val(picker.startDate.format('YYYY-MM-DD'));
        $('#offerEndAt').val(picker.endDate.format('YYYY-MM-DD'));
        $('#dateRangeError').text('');
    });
    $('#offerDateRange').on('cancel.daterangepicker', function () { $(this).val(''); $('#offerStartAt,#offerEndAt').val(''); });

    $('#itemsSelect').select2({ dropdownParent: $('#createoffer'), placeholder: 'Search and select items...', allowClear: true, width: '100%' });

    $('#offerTypeId').on('change', function () { handleDiscountField($(this).find(':selected').data('slug'), ''); });

    $('#appliesToSelect').on('change', function () {
        const val = $(this).val();
        if (val) { $('#itemsGroup').removeClass('d-none'); loadItemsInto('itemsSelect', 'createoffer', val, [], []); $('#itemsError').text(''); }
        else { $('#itemsGroup').addClass('d-none'); }
    });

    $('#createoffer').on('hidden.bs.modal', function () {
        $('#createOfferForm')[0].reset();
        $('#offerDateRange').val(''); $('#offerStartAt,#offerEndAt').val('');
        $('#discountValueGroup').addClass('d-none'); $('#itemsGroup').addClass('d-none');
        $('#itemsSelect').empty().trigger('change');
        $('.error-div').text(''); $('#dateRangeError,#itemsError').text('');
        $('#createOffer_submit').prop('disabled', false).html('Submit for Approval');
    });

    $('#createOfferForm').on('submit', function (e) {
        e.preventDefault();
        if (!validateOfferForm('')) return;

        const $btn = $('#createOffer_submit');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

        $.ajax({
            url: "{{ route('manage-offers.store') }}",
            type: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                if (response.statusCode == 200) {
                    toastr.success(response.message);
                    $('#createoffer').modal('hide');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    toastr.error(response.error ?? 'Something went wrong.');
                    $btn.prop('disabled', false).html('Submit for Approval');
                }
            },
            error: function () {
                toastr.error('Something went wrong.');
                $btn.prop('disabled', false).html('Submit for Approval');
            }
        });
    });

    // ═══════════════════════════════════════════════════════════════════════
    // EDIT OFFER
    // ═══════════════════════════════════════════════════════════════════════

    $('#edit_offerDateRange').daterangepicker({ autoUpdateInput: false, locale: { cancelLabel: 'Clear', format: 'DD/MM/YYYY' } });
    $('#edit_offerDateRange').on('apply.daterangepicker', function (ev, picker) {
        $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
        $('#edit_offerStartAt').val(picker.startDate.format('YYYY-MM-DD'));
        $('#edit_offerEndAt').val(picker.endDate.format('YYYY-MM-DD'));
        $('#edit_dateRangeError').text('');
    });
    $('#edit_offerDateRange').on('cancel.daterangepicker', function () { $(this).val(''); $('#edit_offerStartAt,#edit_offerEndAt').val(''); });

    $('#edit_itemsSelect').select2({ dropdownParent: $('#editoffer'), placeholder: 'Search and select items...', allowClear: true, width: '100%' });

    $('#edit_offerTypeId').on('change', function () { handleDiscountField($(this).find(':selected').data('slug'), 'edit_'); });

    $('#edit_appliesToSelect').on('change', function () {
        const val     = $(this).val();
        const current = $('#edit_itemsSelect').val() ?? [];
        if (val) { loadItemsInto('edit_itemsSelect', 'editoffer', val, current.map(Number), currentEditOwnIds); $('#edit_itemsError').text(''); }
    });

    // Edit button click → check pending
    $(document).on('click', '.edit-offer', function () {
        const id = $(this).data('id');

        $.ajax({
            url: '/manage-offers/' + id + '/edit',
            type: 'GET',
            success: function (response) {
                if (response.statusCode === 423) {
                    $('#pendingBlockMessage').text(response.message);
                    $('#pendingBlockModal').modal('show');
                    return;
                }
                if (response.statusCode !== 200) {
                    toastr.error(response.error ?? 'Something went wrong.');
                    return;
                }

                const d = response.data;
                $('#edit_offer_id').val(d.id);
                $('#edit_offerName').val(d.name);
                $('#edit_offerTypeId').val(d.offer_type_id).trigger('change');
                handleDiscountField(d.offer_type_slug, 'edit_');
                $('#edit_discountValue').val(d.discount_value);
                $('#edit_appliesToSelect').val(d.applies_to);

                // Date range
                if (d.start_at && d.end_at) {
                    const start = moment(d.start_at);
                    const end   = moment(d.end_at);
                    $('#edit_offerDateRange').val(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
                    $('#edit_offerDateRange').data('daterangepicker').setStartDate(start);
                    $('#edit_offerDateRange').data('daterangepicker').setEndDate(end);
                    $('#edit_offerStartAt').val(d.start_at);
                    $('#edit_offerEndAt').val(d.end_at);
                }

                // Load items with pre-selected (own items excluded from "taken" check)
                currentEditOwnIds = d.item_ids;
                loadItemsInto('edit_itemsSelect', 'editoffer', d.applies_to, d.item_ids, d.item_ids);

                $('#editoffer').modal('show');
            },
            error: function () { toastr.error('Something went wrong.'); }
        });
    });

    $('#editoffer').on('hidden.bs.modal', function () {
        currentEditOwnIds = [];
        $('#editOfferForm')[0].reset();
        $('#edit_offerDateRange').val(''); $('#edit_offerStartAt,#edit_offerEndAt').val('');
        $('#edit_discountValueGroup').addClass('d-none');
        $('#edit_itemsSelect').empty().trigger('change');
        $('.error-div').text(''); $('#edit_dateRangeError,#edit_itemsError').text('');
        $('#editOffer_submit').prop('disabled', false).html('Submit Edit for Approval');
    });

    $('#editOfferForm').on('submit', function (e) {
        e.preventDefault();
        if (!validateOfferForm('edit_')) return;

        const id   = $('#edit_offer_id').val();
        const $btn = $('#editOffer_submit');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

        $.ajax({
            url: '/manage-offers/' + id,
            type: 'POST',
            data: $(this).serialize() + '&_method=PUT',
            success: function (response) {
                if (response.statusCode == 200) {
                    toastr.success(response.message);
                    $('#editoffer').modal('hide');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    toastr.error(response.error ?? 'Something went wrong.');
                    $btn.prop('disabled', false).html('Submit Edit for Approval');
                }
            },
            error: function () {
                toastr.error('Something went wrong.');
                $btn.prop('disabled', false).html('Submit Edit for Approval');
            }
        });
    });

    // ═══════════════════════════════════════════════════════════════════════
    // DELETE OFFER (maker-checker)
    // ═══════════════════════════════════════════════════════════════════════

    $(document).on('click', '.delete-offer', function () {
        const id = $(this).data('id');

        // First check for pending approval
        $.ajax({
            url: '/manage-offers/' + id + '/edit',
            type: 'GET',
            success: function (response) {
                if (response.statusCode === 423) {
                    $('#pendingBlockMessage').text(response.message);
                    $('#pendingBlockModal').modal('show');
                    return;
                }
                $('#delete_offer_id').val(id);
                $('#deleteConfirmModal').modal('show');
            },
            error: function () { toastr.error('Something went wrong.'); }
        });
    });

    $('#confirmDeleteBtn').on('click', function () {
        const $btn = $(this);
        const id   = $('#delete_offer_id').val();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>...');

        $.ajax({
            url: '/manage-offers/' + id,
            type: 'POST',
            data: { _token: "{{ csrf_token() }}", _method: 'DELETE' },
            success: function (response) {
                if (response.statusCode == 200) {
                    toastr.success(response.message);
                    $('#deleteConfirmModal').modal('hide');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    toastr.error(response.error ?? 'Something went wrong.');
                    $btn.prop('disabled', false).html('Yes, Submit');
                }
            },
            error: function () {
                toastr.error('Something went wrong.');
                $btn.prop('disabled', false).html('Yes, Submit');
            }
        });
    });

});
</script>
@endsection
