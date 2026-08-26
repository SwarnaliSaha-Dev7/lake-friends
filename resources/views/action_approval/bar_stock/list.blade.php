@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="member-list-part position-relative">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2 flex-wrap">
                    <h2 class="fs-5 common-heading mb-0 fw-semibold">Pending Bar Stock Requests</h2>
                </div>
                <div class="table-responsive">
                    <table class="table rounded-3 overflow-hidden clubmemberlist2" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th class="text-white fw-medium text-nowrap">Sl. No.</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Item Name</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Type</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Detail</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Notes / Reason</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Requested By</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Requested At</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transferApprovalData as $index => $approval)
                                @php
                                    $payload = is_array($approval->request_payload)
                                        ? $approval->request_payload
                                        : json_decode($approval->request_payload, true);
                                    $isBeer  = $payload['is_beer'] ?? false;
                                    $isAdjustment = $approval->module === 'bar_stock_adjustment';

                                    if ($isAdjustment) {
                                        $unit   = $isBeer ? 'BTL' : 'ml';
                                        $detail = number_format($payload['system_qty'] ?? 0) . ' → ' . number_format($payload['physical_qty'] ?? 0) . " {$unit} "
                                            . (($payload['direction'] ?? 'in') === 'in' ? '(+' : '(−') . number_format($payload['quantity'] ?? 0) . " {$unit})";
                                    } else {
                                        $barQty = $payload['bar_qty'] ?? 0;
                                        $sizeMl = $payload['size_ml'] ?? null;
                                        $detail = ($payload['bottles'] ?? 0) . ' BTL (Godown) → ' . ($isBeer
                                            ? "{$barQty} BTL"
                                            : number_format($barQty) . ' ml' . ($sizeMl ? " ({$payload['bottles']}×{$sizeMl}ml)" : ''));
                                    }
                                @endphp
                                <tr>
                                    <td class="text-nowrap">{{ $index + 1 }}</td>
                                    <td class="text-nowrap fw-medium">{{ $payload['item_name'] ?? '—' }}</td>
                                    <td class="text-nowrap">
                                        @if($isAdjustment)
                                            <span class="badge bg-warning-subtle text-warning border border-warning">Adjustment</span>
                                        @else
                                            <span class="badge bg-info-subtle text-info border border-info">Transfer</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">{{ $detail }}</td>
                                    <td>{{ $payload['reason'] ?? $payload['notes'] ?? '—' }}</td>
                                    <td class="text-nowrap">{{ $approval->operatorDetails->name ?? '—' }}</td>
                                    <td class="text-nowrap">{{ $approval->created_at->format('d M Y, h:i A') }}</td>
                                    <td class="text-nowrap">
                                        <button class="btn btn-sm btn-success btn-approve-transfer"
                                            data-id="{{ $approval->id }}">
                                            <i class="fa-solid fa-check"></i> Approve
                                        </button>
                                        <button class="btn btn-sm btn-danger btn-reject-transfer ms-1"
                                            data-id="{{ $approval->id }}">
                                            <i class="fa-solid fa-xmark"></i> Reject
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">No pending bar stock requests.</td>
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

    {{-- Approve Confirm Modal --}}
    <div class="modal fade" id="approveConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-3">
                <div class="modal-header border-0">
                    <h6 class="modal-title fw-semibold">Confirm Approval</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to <strong class="text-success">approve</strong> this request?</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirmApproveBtn">Yes, Approve</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Reject Confirm Modal --}}
    <div class="modal fade" id="rejectConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-3">
                <div class="modal-header border-0">
                    <h6 class="modal-title fw-semibold">Confirm Rejection</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to <strong class="text-danger">reject</strong> this request?</p>
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

        let pendingApproveId  = null;
        let pendingApproveBtn = null;
        let pendingRejectId   = null;
        let pendingRejectBtn  = null;

        $(document).on('click', '.btn-approve-transfer', function () {
            pendingApproveId  = $(this).data('id');
            pendingApproveBtn = $(this);
            $('#approveConfirmModal').modal('show');
        });

        $('#confirmApproveBtn').on('click', function () {
            if (!pendingApproveId) return;
            const id  = pendingApproveId;
            const btn = pendingApproveBtn;
            $('#approveConfirmModal').modal('hide');
            btn.prop('disabled', true);

            $.ajax({
                url: "{{ url('manage-bar-stock-approval') }}/approve/" + id,
                method: 'GET',
                success: function (res) {
                    if (res.statusCode === 200) {
                        toastr.success(res.message);
                        btn.closest('tr').fadeOut(400, function () { $(this).remove(); });
                    } else {
                        toastr.error(res.message || 'Could not approve.');
                        btn.prop('disabled', false);
                    }
                },
                error: function () {
                    toastr.error('Server error.');
                    btn.prop('disabled', false);
                }
            });
        });

        $(document).on('click', '.btn-reject-transfer', function () {
            pendingRejectId  = $(this).data('id');
            pendingRejectBtn = $(this);
            $('#rejectConfirmModal').modal('show');
        });

        $('#confirmRejectBtn').on('click', function () {
            if (!pendingRejectId) return;
            const id  = pendingRejectId;
            const btn = pendingRejectBtn;
            $('#rejectConfirmModal').modal('hide');
            btn.prop('disabled', true);

            $.ajax({
                url: "{{ url('manage-bar-stock-approval') }}/reject/" + id,
                method: 'GET',
                success: function (res) {
                    if (res.statusCode === 200) {
                        toastr.warning(res.message);
                        btn.closest('tr').fadeOut(400, function () { $(this).remove(); });
                    } else {
                        toastr.error(res.message || 'Could not reject.');
                        btn.prop('disabled', false);
                    }
                },
                error: function () {
                    toastr.error('Server error.');
                    btn.prop('disabled', false);
                }
            });
        });

    });
</script>
@endsection
