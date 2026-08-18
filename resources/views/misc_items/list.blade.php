@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="member-list-part position-relative">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2 flex-wrap">
                    <h2 class="fs-5 common-heading mb-md-0 fw-semibold">Misc Items List</h2>
                    <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addmiscitem">+ Add Item</button>
                </div>
                <div class="table-responsive">
                    <table class="table rounded-3 overflow-hidden clubmemberlist2" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th class="text-white fw-medium text-nowrap">Sl. No.</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Name</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Code</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Category</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Unit</th>
                                <th class="text-white fw-medium align-middle text-nowrap">GST %</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Price Override</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Status</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Price</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($miscItemsList as $items)
                                <tr>
                                    <td class="text-nowrap">{{ $loop->iteration }}</td>
                                    <td class="text-nowrap">{{ $items->name }}</td>
                                    <td class="text-nowrap">{{ $items->code }}</td>
                                    <td class="text-nowrap">{{ $items->miscItemCat->name ?? '—' }}</td>
                                    <td class="text-nowrap">{{ $items->unit ?? 'pcs' }}</td>
                                    <td class="text-nowrap">{{ number_format($items->gst_percentage, 2) }}%</td>
                                    <td class="text-nowrap">
                                        @if($items->is_price_editable)
                                            <span class="badge bg-info-subtle text-info border border-info rounded-pill px-2">Allowed</span>
                                        @else
                                            <span class="text-muted">Fixed</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">
                                        @if(in_array($items->id, $pendingCreateIds))
                                            <span class="badge bg-warning-subtle text-warning border border-warning rounded-pill px-2">Pending Approval</span>
                                        @elseif($items->is_active == 1)
                                            <span class="text-success">Active</span>
                                        @else
                                            <span class="text-secondary">Inactive</span>
                                        @endif
                                        @if(in_array($items->id, $pendingUpdateIds))
                                            <span class="badge bg-warning-subtle text-warning border border-warning rounded-pill px-2 ms-1">Edit Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">&#8377;{{ $items->miscItemPrice->price ?? '0' }}</td>
                                    <td class="text-nowrap">
                                        <button class="border-0 bg-light p-1 rounded-3 lh-1 action-btn editMiscItem"
                                            data-id="{{ $items->id }}" title="Edit">
                                            <small><i class="fa-solid fa-pen-to-square"></i></small>
                                        </button>

                                        @if(in_array($items->id, $pendingDeleteIds))
                                            <span class="badge bg-danger-subtle text-danger border border-danger rounded-pill px-2 ms-1">Delete Pending</span>
                                        @elseif(in_array($items->id, $pendingCreateIds))
                                            {{-- item not yet approved, block deletion --}}
                                        @else
                                            <button class="border-0 bg-light p-1 rounded-3 lh-1 action-btn delete-misc-row ms-1"
                                                data-id="{{ $items->id }}"
                                                data-pending="{{ in_array($items->id, $pendingAnyIds) ? '1' : '0' }}"
                                                title="Delete">
                                                <small><i class="fa-solid fa-trash"></i></small>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">No misc items found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modalComponent')

    {{-- Add Misc Item Modal --}}
    <div class="modal fade" id="addmiscitem" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fs-5 fw-semibold">Add Misc Item</h5>
                    <button type="button" class="btn-close bg-transparent fs-5 lh-1" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-regular fa-circle-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="miscItemForm" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="form-label fw-semibold text-dark mb-0">
                                    <span class="text-info rounded-3 label-icon p-1 d-inline-flex align-items-center justify-content-center me-2">
                                        <i class="fa-solid fa-box"></i>
                                    </span> Item Details
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-part mb-3">
                                    <label class="form-label"><small>Item Name</small></label>
                                    <input type="text" name="itemName" id="add_itemName" class="form-control py-2 shadow-none" placeholder="e.g. T-Shirt" required>
                                    <div class="error-div text-danger small"></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-part mb-3">
                                    <label class="form-label"><small>Category</small></label>
                                    <select name="itemCat" id="add_itemCat" class="form-select py-2 shadow-none" required>
                                        <option value="" selected disabled hidden>Select Category</option>
                                        @foreach($miscCatList as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="error-div text-danger small"></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-part mb-3">
                                    <label class="form-label"><small>Item Code</small></label>
                                    <input type="text" name="itemCode" id="add_itemCode" class="form-control py-2 shadow-none" placeholder="Item Code" required>
                                    <div class="error-div text-danger small"></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-part mb-3">
                                    <label class="form-label"><small>Price</small></label>
                                    <input type="number" name="itemPrice" id="add_itemPrice" class="form-control py-2 shadow-none" placeholder="Item Price" min="0" step="0.01" required>
                                    <div class="error-div text-danger small"></div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-part mb-3">
                                    <label class="form-label"><small>Unit</small></label>
                                    <input type="text" name="unit" id="add_unit" class="form-control py-2 shadow-none" placeholder="pcs / hour / day" value="pcs">
                                    <div class="error-div text-danger small"></div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-part mb-3">
                                    <label class="form-label"><small>GST % <span class="text-muted">(defaults to club Misc GST rate)</span></small></label>
                                    <input type="number" name="gstPercentage" id="add_gstPercentage" class="form-control py-2 shadow-none" placeholder="e.g. 18" min="0" max="100" step="0.01" value="{{ $globalMiscGstPercentage }}" required>
                                    <div class="error-div text-danger small"></div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-part mb-3">
                                    <label class="form-label w-100"><small>Status</small></label>
                                    <select name="itemstatus" id="add_itemstatus" class="form-select py-2 shadow-none" required>
                                        <option value="" selected disabled hidden>Select Status</option>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                    <div class="error-div text-danger small"></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-part mb-3 d-flex align-items-center gap-2 mt-2">
                                    <input type="checkbox" name="isPriceEditable" id="add_isPriceEditable" value="1" class="form-check-input">
                                    <label class="form-check-label" for="add_isPriceEditable"><small>Allow price override at billing (e.g. Banquet Rent)</small></label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-part mb-3">
                                    <label class="form-label"><small>Item Image</small></label>
                                    <label class="file-upload-box position-relative text-center border rounded-3 w-100 p-2">
                                        <input type="file" name="itemImage" id="add_itemImage" class="file-input opacity-0 position-absolute start-0 w-100 add-item-image">
                                        <div class="upload-content" id="add_uploadContent">
                                            <i class="fa-solid fa-arrow-up-from-bracket"></i>
                                            <small class="d-block text-muted">PNG & JPEG, max 5MB</small>
                                        </div>
                                        <img id="add_itemPreview" src="" width="80" class="rounded d-none mt-1">
                                    </label>
                                    <div class="error-div text-danger small"></div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-part mb-3">
                                    <label class="form-label"><small>Description (optional)</small></label>
                                    <textarea name="description" id="add_description" class="form-control py-2 shadow-none" rows="2" maxlength="500"></textarea>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="text-end mod-footer">
                                    <button type="submit" class="btn btn-primary fw-semibold" id="miscItem_submit">Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Misc Item Modal --}}
    <div class="modal fade" id="editmiscitem" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fs-5 fw-semibold">Edit Misc Item</h5>
                    <button type="button" class="btn-close bg-transparent fs-5 lh-1" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-regular fa-circle-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="pendingUpdateMessage" class="alert alert-warning py-2 px-3 small mb-3" style="display:none;"></div>
                    <form id="editMiscItemForm" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="edit_item_id" id="edit_item_id">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="form-label fw-semibold text-dark mb-0">
                                    <span class="text-info rounded-3 label-icon p-1 d-inline-flex align-items-center justify-content-center me-2">
                                        <i class="fa-solid fa-box"></i>
                                    </span> Item Details
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-part mb-3">
                                    <label class="form-label"><small>Item Name</small></label>
                                    <input type="text" name="itemName" id="edit_itemName" class="form-control py-2 shadow-none" placeholder="Item Name" required>
                                    <div class="error-div text-danger small"></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-part mb-3">
                                    <label class="form-label"><small>Category</small></label>
                                    <select name="itemCat" id="edit_itemCat" class="form-select py-2 shadow-none" required>
                                        <option value="" selected disabled hidden>Select Category</option>
                                        @foreach($miscCatList as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="error-div text-danger small"></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-part mb-3">
                                    <label class="form-label"><small>Item Code</small></label>
                                    <input type="text" name="itemCode" id="edit_itemCode" class="form-control py-2 shadow-none" placeholder="Item Code" required>
                                    <div class="error-div text-danger small"></div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-part mb-3">
                                    <label class="form-label"><small>Unit</small></label>
                                    <input type="text" name="unit" id="edit_unit" class="form-control py-2 shadow-none" placeholder="pcs / hour / day">
                                    <div class="error-div text-danger small"></div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-part mb-3">
                                    <label class="form-label"><small>GST %</small></label>
                                    <input type="number" name="gstPercentage" id="edit_gstPercentage" class="form-control py-2 shadow-none" min="0" max="100" step="0.01" required>
                                    <div class="error-div text-danger small"></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-part mb-3">
                                    <label class="form-label w-100"><small>Status</small></label>
                                    <select name="itemstatus" id="edit_itemstatus" class="form-select py-2 shadow-none" required>
                                        <option value="" selected disabled hidden>Select Status</option>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                    <div class="error-div text-danger small"></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-part mb-3 d-flex align-items-center gap-2 mt-2">
                                    <input type="checkbox" name="isPriceEditable" id="edit_isPriceEditable" value="1" class="form-check-input">
                                    <label class="form-check-label" for="edit_isPriceEditable"><small>Allow price override at billing</small></label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-part mb-3">
                                    <label class="form-label"><small>Item Image</small></label>
                                    <label class="file-upload-box position-relative text-center border rounded-3 w-100 p-2">
                                        <input type="file" name="itemImage" id="edit_itemImage" class="file-input opacity-0 position-absolute start-0 w-100 edit-item-image">
                                        <div class="upload-content" id="edit_uploadContent">
                                            <i class="fa-solid fa-arrow-up-from-bracket"></i>
                                            <small class="d-block text-muted">PNG & JPEG, max 5MB</small>
                                        </div>
                                        <img id="edit_itemPreview" src="" width="80" class="rounded d-none mt-1">
                                    </label>
                                    <div class="error-div text-danger small"></div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-part mb-3">
                                    <label class="form-label"><small>Description (optional)</small></label>
                                    <textarea name="description" id="edit_description" class="form-control py-2 shadow-none" rows="2" maxlength="500"></textarea>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="text-end mod-footer">
                                    <button type="submit" class="btn btn-primary fw-semibold" id="editMiscItem_submit">Update</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    {{-- Price Manage Section --}}
                    <hr class="my-3">
                    <div class="row">
                        <div class="col-12 mb-2">
                            <div class="form-label fw-semibold text-dark mb-0">
                                <span class="text-info rounded-3 label-icon p-1 d-inline-flex align-items-center justify-content-center me-2">
                                    <i class="fa-solid fa-tag"></i>
                                </span> Price Manage
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-part mb-2 d-flex align-items-center gap-2">
                                <label class="form-label mb-0"><small>Current Price</small></label>
                                <input type="hidden" id="price_item_id">
                                <input type="number" id="edit_itemPrice" class="form-control py-2 shadow-none" placeholder="Current price" readonly style="max-width:160px;">
                                <button type="button" class="btn btn-info fw-semibold changePriceBtn"
                                    data-bs-toggle="modal" data-bs-target="#changemiscprice">Change Price</button>
                            </div>
                            <div id="pendingPriceMessage" class="mb-2 text-warning fw-semibold small" style="display:none;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Change Price Modal --}}
    <div class="modal fade" id="changemiscprice" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fs-5 fw-semibold">Change Misc Item Price</h5>
                    <button type="button" class="btn-close bg-transparent fs-5 lh-1" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-regular fa-circle-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="changeMiscPriceForm">
                        <div class="mb-3">
                            <label class="form-label"><small>New Price</small></label>
                            <input type="number" name="newPrice" id="newMiscPrice" class="form-control py-2 shadow-none"
                                placeholder="New Price" min="0" step="0.01" required>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary fw-semibold">Submit for Approval</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Confirm Modal --}}
    <div class="modal fade" id="deleteMiscConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-3">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to delete this item?</p>
                </div>
                <input type="hidden" id="delete_misc_id" value="">
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmMiscDeleteBtn">Yes, Delete</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('customJS')
<script>
$(document).ready(function () {

    // ── ADD item image preview ──
    $('#add_itemImage').on('change', function () {
        if (this.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#add_itemPreview').attr('src', e.target.result).removeClass('d-none');
                $('#add_uploadContent').hide();
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    // ── EDIT item image preview ──
    $('#edit_itemImage').on('change', function () {
        if (this.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#edit_itemPreview').attr('src', e.target.result).removeClass('d-none');
                $('#edit_uploadContent').hide();
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    // ── ADD form submit ──
    $('#miscItemForm').on('submit', function (e) {
        e.preventDefault();

        var isValid = true;

        if ($('#add_itemName').val() === '') {
            $('#add_itemName').next('.error-div').text('Item name is required');
            isValid = false;
        } else {
            $('#add_itemName').next('.error-div').text('');
        }

        if (!$('#add_itemCat').val()) {
            $('#add_itemCat').next('.error-div').text('Please select a category');
            isValid = false;
        } else {
            $('#add_itemCat').next('.error-div').text('');
        }

        if ($('#add_itemCode').val() === '') {
            $('#add_itemCode').next('.error-div').text('Item code is required');
            isValid = false;
        } else {
            $('#add_itemCode').next('.error-div').text('');
        }

        var price = $('#add_itemPrice').val();
        if (price === '' || parseFloat(price) < 0) {
            $('#add_itemPrice').next('.error-div').text('Enter a valid price');
            isValid = false;
        } else {
            $('#add_itemPrice').next('.error-div').text('');
        }

        if (!$('#add_itemstatus').val()) {
            $('#add_itemstatus').next('.error-div').text('Status is required');
            isValid = false;
        } else {
            $('#add_itemstatus').next('.error-div').text('');
        }

        if (!isValid) return;

        var $btn = $('#miscItem_submit');
        var origText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

        var formData = new FormData($('#miscItemForm')[0]);
        if (!$('#add_isPriceEditable').is(':checked')) {
            formData.set('isPriceEditable', '0');
        }

        $.ajax({
            url: '{{ route("manage-misc-items.store") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                $btn.html(origText).prop('disabled', false);
                if (response.statusCode == 200) {
                    toastr.success(response.message);
                    $('#miscItemForm')[0].reset();
                    $('#add_itemPreview').addClass('d-none').attr('src', '');
                    $('#add_uploadContent').show();
                    setTimeout(function () { location.reload(); }, 1500);
                } else {
                    toastr.error(response.error || response.message || 'Something went wrong.');
                }
            },
            error: function () {
                $btn.html(origText).prop('disabled', false);
                toastr.error('Something went wrong.');
            }
        });
    });

    // ── EDIT button click: load data ──
    $(document).on('click', '.editMiscItem', function () {
        var id = $(this).data('id');

        $('#pendingPriceMessage').hide().text('');
        $('.changePriceBtn').prop('disabled', false).css({'opacity': '', 'cursor': ''});
        $('#pendingUpdateMessage').hide().text('');
        $('#editMiscItem_submit').prop('disabled', false);

        $.ajax({
            url: "{{ url('manage-misc-items') }}/" + id + '/edit',
            type: 'GET',
            success: function (response) {
                var data = response.data;

                $('#edit_item_id').val(data.id);
                $('#price_item_id').val(data.id);
                $('#edit_itemName').val(data.name);
                $('#edit_itemCat').val(data.misc_category_id);
                $('#edit_itemCode').val(data.code);
                $('#edit_unit').val(data.unit);
                $('#edit_gstPercentage').val(data.gst_percentage);
                $('#edit_isPriceEditable').prop('checked', !!data.is_price_editable);
                $('#edit_description').val(data.description);
                $('#edit_itemstatus').val(data.is_active ? '1' : '0');
                $('#edit_itemPrice').val(data.misc_item_price ? data.misc_item_price.price : '');

                if (data.image) {
                    $('#edit_itemPreview').attr('src', data.image).removeClass('d-none');
                    $('#edit_uploadContent').hide();
                } else {
                    $('#edit_itemPreview').addClass('d-none').attr('src', '');
                    $('#edit_uploadContent').show();
                }

                if (response.pendingApproval && response.pendingApproval.request_payload) {
                    var payload = response.pendingApproval.request_payload;
                    if (typeof payload === 'string') { payload = JSON.parse(payload); }
                    $('#pendingPriceMessage')
                        .text('Price change request is pending: ₹' + payload.old_price + ' → ₹' + payload.new_price + ' (awaiting approval)')
                        .show();
                    $('.changePriceBtn').prop('disabled', true).css({'opacity': '1', 'cursor': 'not-allowed'});
                    $('#editMiscItem_submit').prop('disabled', true);
                }

                if (response.pendingUpdateApproval) {
                    $('#pendingUpdateMessage')
                        .text('An edit request for this item is already pending approval. You can view the current details below, but a new edit can\'t be submitted until that one is resolved.')
                        .show();
                    $('#editMiscItem_submit').prop('disabled', true);
                    $('.changePriceBtn').prop('disabled', true).css({'opacity': '1', 'cursor': 'not-allowed'});
                }

                $('#editmiscitem').modal('show');
            },
            error: function () {
                toastr.error('Failed to load item data.');
            }
        });
    });

    // ── EDIT form submit ──
    $('#editMiscItemForm').on('submit', function (e) {
        e.preventDefault();

        var id = $('#edit_item_id').val();
        var formData = new FormData(this);
        if (!$('#edit_isPriceEditable').is(':checked')) {
            formData.set('isPriceEditable', '0');
        }

        var $btn = $('#editMiscItem_submit');
        var origText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

        $.ajax({
            url: "{{ url('manage-misc-items') }}/" + id,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                $btn.html(origText).prop('disabled', false);
                if (response.statusCode == 200) {
                    toastr.success(response.message);
                    $('#editmiscitem').modal('hide');
                    setTimeout(function () { location.reload(); }, 1500);
                } else {
                    toastr.error(response.error || response.message || 'Something went wrong.');
                }
            },
            error: function () {
                $btn.html(origText).prop('disabled', false);
                toastr.error('Something went wrong.');
            }
        });
    });

    // ── PRICE CHANGE form submit ──
    $('#changeMiscPriceForm').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
            url: '{{ route("miscItemPriceApproval.request") }}',
            type: 'POST',
            data: {
                item_id:   $('#price_item_id').val(),
                new_price: $('#newMiscPrice').val(),
                _token:    '{{ csrf_token() }}'
            },
            success: function (response) {
                if (response.statusCode == 200) {
                    toastr.success(response.message);
                    $('#changemiscprice').modal('hide');
                    setTimeout(function () { location.reload(); }, 1500);
                } else {
                    toastr.error(response.error || 'Something went wrong.');
                }
            },
            error: function () {
                toastr.error('Something went wrong.');
            }
        });
    });

    // ── DELETE button click ──
    $(document).on('click', '.delete-misc-row', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        if ($(this).data('pending') == '1') {
            toastr.warning('This item has a pending approval request. Please wait for it to be resolved before deleting.');
            return false;
        }
        $('#delete_misc_id').val($(this).data('id'));
        $('#deleteMiscConfirmModal').modal('show');
        return false;
    });

    // ── CONFIRM DELETE ──
    $('#confirmMiscDeleteBtn').on('click', function () {
        var id  = $('#delete_misc_id').val();
        var btn = $(this);
        btn.prop('disabled', true);

        $.ajax({
            url: "{{ url('manage-misc-items') }}/" + id,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function (response) {
                btn.prop('disabled', false);
                if (response.statusCode == 200) {
                    toastr.success(response.message);
                    $('#deleteMiscConfirmModal').modal('hide');
                    setTimeout(function () { location.reload(); }, 1000);
                } else {
                    toastr.error(response.message || response.error || 'Something went wrong.');
                }
            },
            error: function () {
                btn.prop('disabled', false);
                toastr.error('Delete failed.');
            }
        });
    });

});
</script>
@endsection
