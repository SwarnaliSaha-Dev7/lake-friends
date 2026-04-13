@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="member-list-part position-relative">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2 flex-wrap">
                    <h2 class="fs-5 common-heading mb-md-0 fw-semibold">Godown Stock Approval List</h2>
                </div>
                <div class="table-responsive">
                    <table class="table rounded-3 overflow-hidden clubmemberlist2" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th class="text-white fw-medium align-middle text-nowrap">Sl No</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Item Name</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Type</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Details</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Reason / Notes</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Requested By</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Date</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($stockApprovalData as $data)
                                @php
                                    $payload = is_array($data->request_payload)
                                        ? (object) $data->request_payload
                                        : json_decode($data->request_payload);

                                    $isPurchase   = ($payload->movement_type ?? 'purchase') === 'purchase';
                                    $isAdjustment = ($payload->movement_type ?? '') === 'adjustment';
                                    $direction    = $payload->direction ?? 'in';
                                @endphp
                                <tr>
                                    <td class="text-nowrap">{{ $loop->iteration }}</td>
                                    <td class="text-nowrap fw-medium">
                                        {{ $data->entity->name ?? ($payload->item_name ?? '—') }}
                                        @if($payload->size_ml ?? null)
                                            <small class="text-muted">({{ $payload->size_ml }}ml)</small>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">
                                        @if($isPurchase)
                                            <span class="badge border rounded-pill px-3 py-1 bg-success-subtle text-success border-success">
                                                Stock In
                                            </span>
                                        @elseif($isAdjustment && $direction === 'in')
                                            <span class="badge border rounded-pill px-3 py-1 bg-info-subtle text-info border-info">
                                                Adjustment (+)
                                            </span>
                                        @else
                                            <span class="badge border rounded-pill px-3 py-1 bg-danger-subtle text-danger border-danger">
                                                Adjustment (−)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap small">
                                        @if($isPurchase)
                                            <span class="text-success fw-semibold">+{{ $payload->quantity ?? '—' }} BTL</span>
                                            @if(isset($payload->unit_price) && $payload->unit_price > 0)
                                                <br>
                                                <span class="text-muted">₹{{ number_format($payload->unit_price, 2) }}/BTL</span>
                                                <br>
                                                <span class="text-primary fw-semibold">
                                                    Total: ₹{{ number_format($payload->quantity * $payload->unit_price, 2) }}
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-muted">System: {{ $payload->system_qty ?? '—' }} BTL</span>
                                            <br>
                                            <span class="text-muted">Physical: {{ $payload->physical_qty ?? '—' }} BTL</span>
                                            <br>
                                            @if($direction === 'in')
                                                <span class="text-success fw-semibold">+{{ $payload->quantity }} BTL</span>
                                            @else
                                                <span class="text-danger fw-semibold">−{{ $payload->quantity }} BTL</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="text-nowrap small text-muted" style="max-width:180px; white-space:normal;">
                                        {{ $payload->notes ?? $payload->reason ?? '—' }}
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
                                    <td colspan="8" class="text-center text-muted py-4">No pending stock approvals found.</td>
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
    <div class="modal fade" id="approveConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-3">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Confirm Approve</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to approve this request? Stock will be updated immediately.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirmApproveBtn">Yes, Approve</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rejectConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-3">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Confirm Reject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to reject this request? No stock changes will be made.</p>
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
            url: '{{ route("godownStockApproval.approve", ":id") }}'.replace(':id', id),
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
            url: '{{ route("godownStockApproval.reject", ":id") }}'.replace(':id', id),
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
