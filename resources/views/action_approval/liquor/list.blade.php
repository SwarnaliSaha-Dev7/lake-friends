@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="member-list-part position-relative">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2 flex-wrap">
                    <h2 class="fs-5 common-heading mb-md-0 fw-semibold">Liquor Approval List</h2>
                </div>
                <div class="table-responsive">
                    <table class="table rounded-3 overflow-hidden clubmemberlist2" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th class="text-white fw-medium align-middle text-nowrap">Sl No</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Item Name</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Action Type</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Details</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Maker</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Date</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($liquorApprovalData as $data)
                                @php
                                    $payload = is_array($data->request_payload) ? (object) $data->request_payload : json_decode($data->request_payload);

                                    $actionLabel = match($data->module) {
                                        'liquor_item_create' => 'Add Item',
                                        'liquor_item_delete' => 'Delete Item',
                                        'liquor_price_update' => 'Price Change',
                                        default => ucfirst(str_replace('_', ' ', $data->module)),
                                    };

                                    $actionBadgeClass = match($data->module) {
                                        'liquor_item_create'  => 'bg-success-subtle text-success border-success',
                                        'liquor_item_delete'  => 'bg-danger-subtle text-danger border-danger',
                                        'liquor_price_update' => 'bg-info-subtle text-info border-info',
                                        default               => 'bg-secondary-subtle text-secondary border-secondary',
                                    };
                                @endphp
                                <tr>
                                    <td class="text-nowrap">{{ $loop->iteration }}</td>
                                    <td class="text-nowrap fw-medium">{{ $data->entity->name ?? ($payload->item_name ?? '—') }}</td>
                                    <td class="text-nowrap">
                                        <span class="badge border rounded-pill px-3 py-1 {{ $actionBadgeClass }}">{{ $actionLabel }}</span>
                                    </td>
                                    <td class="text-nowrap small text-muted">
                                        @if($data->module === 'liquor_price_update')
                                            ₹{{ $payload->old_price }} → ₹{{ $payload->new_price }}
                                        @elseif($data->module === 'liquor_item_create')
                                            Price: ₹{{ $payload->price ?? '—' }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-nowrap">{{ $data->operatorDetails->name ?? '—' }}</td>
                                    <td class="text-nowrap">{{ \Carbon\Carbon::parse($data->created_at)->format('d/m/Y') }}</td>
                                    <td class="text-nowrap">
                                        <button class="border-0 p-1 rounded-3 lh-1 action-btn approveBtn bg-success text-white"
                                            title="Approve" data-id="{{ $data->id }}">Approve</button>
                                        <button class="border-0 p-1 rounded-3 lh-1 action-btn rejectBtn bg-danger text-white ms-1"
                                            title="Reject" data-id="{{ $data->id }}">Reject</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No pending approvals found.</td>
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
    <!-- Approve Confirm Modal -->
    <div class="modal fade" id="approveConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-3">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Confirm Approve</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to approve this request?</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirmApproveBtn">Yes, Approve</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Confirm Modal -->
    <div class="modal fade" id="rejectConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-3">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Confirm Reject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to reject this request?</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmRejectBtn">Yes, Reject</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('customJS')
<script>
$(document).ready(function () {

    $('.approveBtn').on('click', function () {
        $('#confirmApproveBtn').data('id', $(this).data('id'));
        $('#approveConfirmModal').modal('show');
    });

    $('#confirmApproveBtn').on('click', function () {
        var btn = $(this);
        var id  = btn.data('id');
        btn.prop('disabled', true).css({'opacity': '1', 'cursor': 'not-allowed'});

        $.ajax({
            url: '{{ route("liquorApproval.approve", ":id") }}'.replace(':id', id),
            type: 'GET',
            success: function (response) {
                if (response.statusCode == 200) {
                    toastr.success(response.message);
                    setTimeout(function () { location.reload(); }, 1500);
                } else {
                    toastr.error(response.message || 'Something went wrong');
                    btn.prop('disabled', false).css({'opacity': '', 'cursor': ''});
                }
            },
            error: function () {
                toastr.error('Something went wrong.');
                btn.prop('disabled', false).css({'opacity': '', 'cursor': ''});
            }
        });
    });

    $('.rejectBtn').on('click', function () {
        $('#confirmRejectBtn').data('id', $(this).data('id'));
        $('#rejectConfirmModal').modal('show');
    });

    $('#confirmRejectBtn').on('click', function () {
        var btn = $(this);
        var id  = btn.data('id');
        btn.prop('disabled', true).css({'opacity': '1', 'cursor': 'not-allowed'});

        $.ajax({
            url: '{{ route("liquorApproval.reject", ":id") }}'.replace(':id', id),
            type: 'GET',
            success: function (response) {
                if (response.statusCode == 200) {
                    toastr.success(response.message);
                    setTimeout(function () { location.reload(); }, 1500);
                } else {
                    toastr.error(response.message || 'Something went wrong');
                    btn.prop('disabled', false).css({'opacity': '', 'cursor': ''});
                }
            },
            error: function () {
                toastr.error('Something went wrong.');
                btn.prop('disabled', false).css({'opacity': '', 'cursor': ''});
            }
        });
    });

});
</script>
@endsection
