@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="member-list-part position-relative">

                <div class="d-flex align-items-center justify-content-between gap-2 mb-3 flex-wrap">
                    <h2 class="fs-5 common-heading mb-0 fw-semibold">Liquor Menu</h2>
                    <button type="button" class="btn btn-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#addServingModal">
                        <i class="fa-solid fa-plus me-1"></i> Add Menu Item
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table rounded-3 overflow-hidden clubmemberlist2" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th class="text-white fw-medium align-middle text-nowrap">Sl No</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Type</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Base Item</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Menu Name</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Volume</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Price</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Status</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($servings as $serving)
                                @php
                                    $isPendingCreate = in_array($serving->id, $pendingCreateIds);
                                    $isPendingUpdate = in_array($serving->id, $pendingUpdateIds);
                                    $isPendingDelete = in_array($serving->id, $pendingDeleteIds);
                                    $hasPending      = in_array($serving->id, $pendingAnyIds);
                                @endphp
                                <tr>
                                    <td class="text-nowrap">{{ $loop->iteration }}</td>
                                    <td class="text-nowrap">
                                        @if($serving->is_cocktail)
                                            <span class="badge border rounded-pill px-2 py-1 bg-purple-subtle text-purple border-purple"
                                                style="background:#f3e8ff;color:#7c3aed;border-color:#c4b5fd!important;">
                                                Cocktail
                                            </span>
                                        @else
                                            <span class="badge border rounded-pill px-2 py-1 bg-info-subtle text-info border-info">
                                                Spirit
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap fw-medium">{{ $serving->foodItem->name ?? '—' }}</td>
                                    <td class="text-nowrap">
                                        {{ $serving->name }}
                                        @if($isPendingCreate)
                                            <span class="badge bg-warning-subtle text-warning border border-warning rounded-pill px-2 ms-1" style="font-size:0.65rem;">Pending Add</span>
                                        @elseif($isPendingUpdate)
                                            <span class="badge bg-info-subtle text-info border border-info rounded-pill px-2 ms-1" style="font-size:0.65rem;">Pending Edit</span>
                                        @elseif($isPendingDelete)
                                            <span class="badge bg-danger-subtle text-danger border border-danger rounded-pill px-2 ms-1" style="font-size:0.65rem;">Pending Delete</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">
                                        @if($serving->is_cocktail)
                                            {{ $serving->volume_ml }} ml <small class="text-muted">(deduct)</small>
                                        @else
                                            {{ $serving->volume_ml }} ml
                                        @endif
                                    </td>
                                    <td class="text-nowrap fw-semibold">Rs {{ number_format($serving->price, 2) }}</td>
                                    <td class="text-nowrap">
                                        @if($isPendingCreate)
                                            <span class="badge border rounded-pill px-3 py-1 bg-warning-subtle text-warning border-warning">Pending Approval</span>
                                        @elseif($serving->is_active)
                                            <span class="badge border rounded-pill px-3 py-1 bg-success-subtle text-success border-success">Active</span>
                                        @else
                                            <span class="badge border rounded-pill px-3 py-1 bg-secondary-subtle text-secondary border-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">
                                        @if(!$hasPending)
                                            <button class="border-0 bg-light p-1 rounded-3 lh-1 action-btn btn-edit-serving"
                                                data-id="{{ $serving->id }}" title="Edit">
                                                <small><i class="fa-regular fa-pen-to-square"></i></small>
                                            </button>
                                            <button class="border-0 bg-light p-1 rounded-3 lh-1 action-btn ms-1 btn-delete-serving"
                                                data-id="{{ $serving->id }}" data-name="{{ $serving->name }}" title="Delete">
                                                <small><i class="fa-regular fa-trash-can text-danger"></i></small>
                                            </button>
                                        @else
                                            <span class="text-muted small">Pending...</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">No liquor menu items found. Add one to get started.</td>
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

    {{-- Add Serving Modal --}}
    <div class="modal fade" id="addServingModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" style="max-width:460px;">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-semibold" id="addServingModalTitle">Add Liquor Menu Item</h5>
                    <button type="button" class="btn-close bg-transparent fs-5 lh-1" data-bs-dismiss="modal">
                        <i class="fa-regular fa-circle-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addServingForm">

                        {{-- Cocktail Toggle --}}
                        <div class="mb-3 d-flex align-items-center gap-3 p-2 bg-light rounded-3">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch" id="addIsCocktail">
                                <label class="form-check-label fw-medium" for="addIsCocktail">Is Cocktail?</label>
                            </div>
                            <small class="text-muted">Toggle ON to add a cocktail recipe</small>
                        </div>

                        {{-- Cocktail Name (only when cocktail) --}}
                        <div class="mb-3" id="addCocktailNameWrapper" style="display:none;">
                            <label class="form-label fw-medium">Cocktail Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control shadow-none" id="addCocktailName"
                                placeholder="e.g. Bloody Mary, Screwdriver..." maxlength="200">
                        </div>

                        {{-- Spirit Item (non-cocktail) --}}
                        <div class="mb-3" id="addSpiritWrapper">
                            <label class="form-label fw-medium">Liquor Item <span class="text-danger">*</span></label>
                            <select class="form-select shadow-none select2-liquor" name="food_item_id" id="addFoodItemId">
                                <option value="">-- Search & Select Item --</option>
                                @foreach($liquorItems as $item)
                                    <option value="{{ $item->id }}" data-name="{{ $item->name }}">
                                        {{ $item->name }}{{ $item->size_ml ? ' ('.$item->size_ml.'ml BTL)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Beer items are served by bottle — only spirit items shown.</small>
                        </div>

                        {{-- Base Spirit (cocktail only) --}}
                        <div class="mb-3" id="addBaseItemWrapper" style="display:none;">
                            <label class="form-label fw-medium">Base Spirit / Item <span class="text-danger">*</span></label>
                            <select class="form-select shadow-none select2-all-liquor" id="addBaseItemId">
                                <option value="">-- Search & Select Base Item --</option>
                                @foreach($allLiquorItems as $item)
                                    <option value="{{ $item->id }}" data-name="{{ $item->name }}">
                                        {{ $item->name }}{{ $item->size_ml ? ' ('.$item->size_ml.'ml BTL)' : '' }}
                                        {{ $item->is_beer ? '(Beer)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Stock will be deducted from this item when cocktail is ordered.</small>
                        </div>

                        {{-- Volume --}}
                        <div class="mb-3">
                            <label class="form-label fw-medium" id="addVolumeMlLabel">Volume (ml) <span class="text-danger">*</span></label>
                            <select class="form-select shadow-none" id="addVolumeMl">
                                <option value="">-- Select Volume --</option>
                                <option value="30">30 ml (Single Peg)</option>
                                <option value="60">60 ml (Double Peg)</option>
                            </select>
                            <small class="text-muted" id="addVolumeHint">Amount deducted from bar stock per serving.</small>
                        </div>

                        {{-- Price --}}
                        <div class="mb-3">
                            <label class="form-label fw-medium">Price (Rs) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control shadow-none" id="addPrice"
                                placeholder="0.00" min="0" step="0.01">
                        </div>

                        {{-- Preview Name (non-cocktail only) --}}
                        <div class="mb-2 p-2 bg-light rounded-3 small text-muted" id="addPreviewName" style="display:none;">
                            Menu name: <strong id="addPreviewNameText"></strong>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary fw-semibold" id="addServingBtn">Submit</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Serving Modal --}}
    <div class="modal fade" id="editServingModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" style="max-width:440px;">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-semibold" id="editServingModalTitle">Edit Liquor Menu Item</h5>
                    <button type="button" class="btn-close bg-transparent fs-5 lh-1" data-bs-dismiss="modal">
                        <i class="fa-regular fa-circle-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editServingForm">
                        <input type="hidden" id="editServingId">
                        <input type="hidden" id="editIsCocktail" value="0">

                        {{-- Base item (readonly) --}}
                        <div class="mb-3">
                            <label class="form-label fw-medium" id="editItemLabel">Item</label>
                            <input type="text" class="form-control bg-light" id="editItemName" readonly>
                        </div>

                        {{-- Cocktail name (editable when cocktail) --}}
                        <div class="mb-3" id="editCocktailNameWrapper" style="display:none;">
                            <label class="form-label fw-medium">Cocktail Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control shadow-none" id="editCocktailName" maxlength="200">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium" id="editVolumeMlLabel">Volume (ml) <span class="text-danger">*</span></label>
                            <select class="form-select shadow-none" id="editVolumeMl" required>
                                <option value="">-- Select Volume --</option>
                                <option value="30">30 ml (Single Peg)</option>
                                <option value="60">60 ml (Double Peg)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Price (Rs) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control shadow-none" id="editPrice" min="0" step="0.01" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary fw-semibold" id="saveEditServingBtn">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Confirm Modal --}}
    <div class="modal fade" id="deleteServingModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
            <div class="modal-content border-0 shadow">
                <div class="modal-body text-center p-4">
                    <div class="mb-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle"
                            style="width:56px;height:56px;background:#fee2e2;">
                            <i class="fa-solid fa-trash fs-4 text-danger"></i>
                        </span>
                    </div>
                    <h5 class="fw-semibold mb-1">Delete Menu Item?</h5>
                    <p class="text-muted small mb-4">Delete <strong id="deleteServingName"></strong>? This action will be sent for approval.</p>
                    <div class="d-flex justify-content-center gap-3">
                        <button class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-danger px-4" id="confirmDeleteServingBtn">Yes, Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('customJS')
<script>
$(document).ready(function () {

    /* ── Add Modal: Init Select2 ── */
    $('#addServingModal').on('shown.bs.modal', function () {
        if (!$('#addFoodItemId').data('select2')) {
            $('#addFoodItemId').select2({
                dropdownParent: $('#addServingModal'),
                placeholder:    'Search spirit item...',
                allowClear:     true,
                width:          '100%',
            });
        }
        if (!$('#addBaseItemId').data('select2')) {
            $('#addBaseItemId').select2({
                dropdownParent: $('#addServingModal'),
                placeholder:    'Search base item...',
                allowClear:     true,
                width:          '100%',
            });
        }
    });

    /* ── Cocktail toggle ── */
    function toggleCocktailMode(isCocktail) {
        if (isCocktail) {
            $('#addServingModalTitle').text('Add Cocktail');
            $('#addCocktailNameWrapper').show();
            $('#addBaseItemWrapper').show();
            $('#addSpiritWrapper').hide();
            $('#addPreviewName').hide();
            $('#addVolumeMlLabel').html('Deduction Volume (ml) <span class="text-danger">*</span>');
            $('#addVolumeHint').text('ml of base item deducted from bar stock per cocktail order.');
        } else {
            $('#addServingModalTitle').text('Add Liquor Menu Item');
            $('#addCocktailNameWrapper').hide();
            $('#addBaseItemWrapper').hide();
            $('#addSpiritWrapper').show();
            $('#addVolumeMlLabel').html('Volume (ml) <span class="text-danger">*</span>');
            $('#addVolumeHint').text('Amount deducted from bar stock per serving.');
            updateAddPreview();
        }
    }

    $('#addIsCocktail').on('change', function () {
        toggleCocktailMode(this.checked);
    });

    function updateAddPreview() {
        var itemName = $('#addFoodItemId option:selected').data('name') || '';
        var ml       = $('#addVolumeMl').val();
        if (itemName && ml && !$('#addIsCocktail').is(':checked')) {
            $('#addPreviewNameText').text(itemName + ' ' + ml + 'ml');
            $('#addPreviewName').show();
        } else {
            $('#addPreviewName').hide();
        }
    }

    $('#addFoodItemId').on('change', updateAddPreview);
    $('#addVolumeMl').on('change', updateAddPreview);

    /* ── Add serving ── */
    $('#addServingBtn').on('click', function () {
        var $btn        = $(this);
        var isCocktail  = $('#addIsCocktail').is(':checked');
        var foodItemId  = isCocktail ? $('#addBaseItemId').val() : $('#addFoodItemId').val();
        var volumeMl    = $('#addVolumeMl').val();
        var price       = $('#addPrice').val();
        var cocktailName = $('#addCocktailName').val().trim();

        if (!foodItemId)  { toastr.warning('Please select ' + (isCocktail ? 'a base item.' : 'a liquor item.')); return; }
        if (!volumeMl)    { toastr.warning('Please select volume.'); return; }
        if (!price)       { toastr.warning('Please enter a price.'); return; }
        if (isCocktail && !cocktailName) { toastr.warning('Please enter the cocktail name.'); return; }

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Submitting...');

        var payload = {
            _token:       '{{ csrf_token() }}',
            food_item_id: foodItemId,
            volume_ml:    volumeMl,
            price:        price,
            is_cocktail:  isCocktail ? 1 : 0,
        };
        if (isCocktail) payload.cocktail_name = cocktailName;

        $.ajax({
            url:         '{{ route("liquor-servings.store") }}',
            type:        'POST',
            contentType: 'application/json',
            data:        JSON.stringify(payload),
            success: function (res) {
                if (res.statusCode === 200) {
                    toastr.success(res.message);
                    $('#addServingModal').modal('hide');
                    setTimeout(() => location.reload(), 800);
                } else {
                    toastr.error(res.message || res.error || 'Something went wrong.');
                }
                $btn.prop('disabled', false).html('Submit');
            },
            error: function () {
                toastr.error('Something went wrong.');
                $btn.prop('disabled', false).html('Submit');
            }
        });
    });

    /* Reset add form on modal hide */
    $('#addServingModal').on('hidden.bs.modal', function () {
        $('#addServingForm')[0].reset();
        $('#addIsCocktail').prop('checked', false);
        toggleCocktailMode(false);
        if ($('#addFoodItemId').data('select2')) {
            $('#addFoodItemId').val('').trigger('change');
        }
        if ($('#addBaseItemId').data('select2')) {
            $('#addBaseItemId').val('').trigger('change');
        }
        $('#addPreviewName').hide();
    });

    /* ── Edit serving ── */
    $(document).on('click', '.btn-edit-serving', function () {
        var id = $(this).data('id');

        $.get('{{ url("liquor-servings") }}/' + id + '/edit', function (res) {
            if (res.statusCode !== 200) { toastr.error('Failed to load.'); return; }
            var s           = res.data;
            var isCocktail  = s.is_cocktail == 1 || s.is_cocktail === true;

            $('#editServingId').val(s.id);
            $('#editIsCocktail').val(isCocktail ? 1 : 0);
            $('#editVolumeMl').val(s.volume_ml);
            $('#editPrice').val(s.price);

            if (isCocktail) {
                $('#editServingModalTitle').text('Edit Cocktail');
                $('#editItemLabel').text('Base Item (readonly)');
                $('#editItemName').val(s.food_item ? s.food_item.name : '—');
                $('#editCocktailName').val(s.name);
                $('#editCocktailNameWrapper').show();
                $('#editVolumeMlLabel').html('Deduction Volume (ml) <span class="text-danger">*</span>');
            } else {
                $('#editServingModalTitle').text('Edit Liquor Menu Item');
                $('#editItemLabel').text('Item');
                $('#editItemName').val(s.food_item ? s.food_item.name : s.name);
                $('#editCocktailNameWrapper').hide();
                $('#editVolumeMlLabel').html('Volume (ml) <span class="text-danger">*</span>');
            }

            $('#editServingModal').modal('show');
        });
    });

    $('#saveEditServingBtn').on('click', function () {
        var $btn       = $(this);
        var id         = $('#editServingId').val();
        var isCocktail = $('#editIsCocktail').val() == 1;

        if (isCocktail && !$('#editCocktailName').val().trim()) {
            toastr.warning('Please enter the cocktail name.');
            return;
        }

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

        var payload = {
            _token:    '{{ csrf_token() }}',
            _method:   'PUT',
            volume_ml: $('#editVolumeMl').val(),
            price:     $('#editPrice').val(),
        };
        if (isCocktail) payload.cocktail_name = $('#editCocktailName').val().trim();

        $.ajax({
            url:         '{{ url("liquor-servings") }}/' + id,
            type:        'POST',
            contentType: 'application/json',
            data:        JSON.stringify(payload),
            success: function (res) {
                if (res.statusCode === 200) {
                    toastr.success(res.message);
                    $('#editServingModal').modal('hide');
                    setTimeout(() => location.reload(), 800);
                } else {
                    toastr.error(res.message || res.error || 'Something went wrong.');
                }
                $btn.prop('disabled', false).html('Save Changes');
            },
            error: function () {
                toastr.error('Something went wrong.');
                $btn.prop('disabled', false).html('Save Changes');
            }
        });
    });

    /* ── Delete serving ── */
    var deleteServingId = null;

    $(document).on('click', '.btn-delete-serving', function () {
        deleteServingId = $(this).data('id');
        $('#deleteServingName').text($(this).data('name'));
        $('#deleteServingModal').modal('show');
    });

    $('#confirmDeleteServingBtn').on('click', function () {
        var $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>...');

        $.ajax({
            url:         '{{ url("liquor-servings") }}/' + deleteServingId,
            type:        'POST',
            contentType: 'application/json',
            data:        JSON.stringify({ _token: '{{ csrf_token() }}', _method: 'DELETE' }),
            success: function (res) {
                if (res.statusCode === 200) {
                    toastr.success(res.message);
                    $('#deleteServingModal').modal('hide');
                    setTimeout(() => location.reload(), 800);
                } else {
                    toastr.error(res.message || 'Something went wrong.');
                }
                $btn.prop('disabled', false).html('Yes, Delete');
            },
            error: function () {
                toastr.error('Something went wrong.');
                $btn.prop('disabled', false).html('Yes, Delete');
            }
        });
    });

});
</script>
@endsection
