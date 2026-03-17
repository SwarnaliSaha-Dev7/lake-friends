@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="member-list-part position-relative">

                <div class="d-flex align-items-center justify-content-between gap-2 mb-3 flex-wrap">
                    <h2 class="fs-5 common-heading mb-0 fw-semibold">Offer Approval List</h2>
                </div>

                <div class="table-responsive">
                    <table class="table rounded-3 overflow-hidden clubmemberlist2" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th class="text-white fw-medium align-middle text-nowrap">Sl No</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Action</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Offer Name</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Offer Type</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Applicable On</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Discount</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Duration</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Items</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Submitted By</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Date</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Approve / Reject</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($offerApprovalData as $approval)
                                @php
                                    $payload    = is_array($approval->request_payload)
                                        ? $approval->request_payload
                                        : json_decode($approval->request_payload, true);
                                    $actionType = $approval->action_type; // create | update | delete
                                    $offer      = $approval->entity;

                                    // For display: use 'new' block for update, direct for create/delete
                                    $display = $actionType === 'update' ? ($payload['new'] ?? []) : $payload;

                                    $actionBadge = match($actionType) {
                                        'create' => ['label' => 'Create', 'class' => 'bg-success-subtle text-success border-success'],
                                        'update' => ['label' => 'Update', 'class' => 'bg-info-subtle text-info border-info'],
                                        'delete' => ['label' => 'Delete', 'class' => 'bg-danger-subtle text-danger border-danger'],
                                        default  => ['label' => ucfirst($actionType), 'class' => 'bg-secondary-subtle text-secondary border-secondary'],
                                    };
                                @endphp
                                <tr>
                                    <td class="text-nowrap">{{ $loop->iteration }}</td>

                                    <td class="text-nowrap">
                                        <span class="badge border rounded-pill px-3 py-2 {{ $actionBadge['class'] }}">
                                            {{ $actionBadge['label'] }}
                                        </span>
                                    </td>

                                    <td class="text-nowrap">
                                        @if($actionType === 'update' && ($payload['old']['name'] ?? '') !== ($display['name'] ?? ''))
                                            <small class="text-muted text-decoration-line-through d-block">{{ $payload['old']['name'] ?? '—' }}</small>
                                            <span class="fw-medium text-success">{{ $display['name'] ?? '—' }}</span>
                                        @else
                                            <span class="fw-medium">{{ $display['name'] ?? $offer?->name ?? '—' }}</span>
                                        @endif
                                    </td>

                                    <td class="text-nowrap">
                                        @if($actionType === 'update' && ($payload['old']['offer_type'] ?? '') !== ($display['offer_type'] ?? ''))
                                            <small class="text-muted text-decoration-line-through d-block">{{ $payload['old']['offer_type'] ?? '—' }}</small>
                                            <span class="badge bg-warning-subtle text-secondary border border-warning rounded-pill px-2 py-1">{{ $display['offer_type'] ?? '—' }}</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-secondary border border-warning rounded-pill px-2 py-1">
                                                {{ $display['offer_type'] ?? $offer?->offerType?->name ?? '—' }}
                                            </span>
                                        @endif
                                    </td>

                                    <td class="text-nowrap text-capitalize">
                                        @if($actionType === 'update' && ($payload['old']['applies_to'] ?? '') !== ($display['applies_to'] ?? ''))
                                            <small class="text-muted text-decoration-line-through d-block">{{ $payload['old']['applies_to'] ?? '—' }}</small>
                                            <span class="text-success">{{ $display['applies_to'] ?? '—' }}</span>
                                        @else
                                            {{ $display['applies_to'] ?? $offer?->applies_to ?? '—' }}
                                        @endif
                                    </td>

                                    <td class="text-nowrap">
                                        @php
                                            $val  = $display['discount_value'] ?? 0;
                                            $oldVal = $payload['old']['discount_value'] ?? null;
                                            $typeSlug = $offer?->offerType?->slug ?? '';
                                        @endphp
                                        @if($actionType === 'update' && $oldVal != $val)
                                            <small class="text-muted text-decoration-line-through d-block">
                                                @if(str_contains($typeSlug,'percent')) {{ $oldVal }}%
                                                @elseif(str_contains($typeSlug,'flat')) ₹{{ $oldVal }}
                                                @else —
                                                @endif
                                            </small>
                                        @endif
                                        @if($typeSlug === 'percentage') {{ $val }}%
                                        @elseif($typeSlug === 'flat') ₹{{ $val }}
                                        @else —
                                        @endif
                                    </td>

                                    <td class="text-nowrap">
                                        @php
                                            $start = $display['start_at'] ?? $offer?->start_at;
                                            $end   = $display['end_at']   ?? $offer?->end_at;
                                            $oldStart = $payload['old']['start_at'] ?? null;
                                            $oldEnd   = $payload['old']['end_at']   ?? null;
                                        @endphp
                                        @if($actionType === 'update' && ($oldStart !== $start || $oldEnd !== $end))
                                            <small class="text-muted text-decoration-line-through d-block">
                                                {{ $oldStart ? \Carbon\Carbon::parse($oldStart)->format('d M Y') : '—' }}
                                                → {{ $oldEnd ? \Carbon\Carbon::parse($oldEnd)->format('d M Y') : '—' }}
                                            </small>
                                        @endif
                                        @if($start && $end)
                                            @php $durationChanged = $actionType === 'update' && ($oldStart !== $start || $oldEnd !== $end); @endphp
                                            <small class="{{ $durationChanged ? 'text-success' : '' }}">
                                                {{ \Carbon\Carbon::parse($start)->format('d M Y') }}
                                                – {{ \Carbon\Carbon::parse($end)->format('d M Y') }}
                                            </small>
                                        @else
                                            —
                                        @endif
                                    </td>

                                    <td>
                                        @if($offer?->offerItems && $offer->offerItems->count())
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($offer->offerItems->take(3) as $item)
                                                    <span class="badge bg-info-subtle text-secondary border border-info rounded-pill px-2 py-1">
                                                        {{ $item->foodItem?->name }}
                                                    </span>
                                                @endforeach
                                                @if($offer->offerItems->count() > 3)
                                                    <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2 py-1">
                                                        +{{ $offer->offerItems->count() - 3 }} more
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            —
                                        @endif
                                    </td>

                                    <td class="text-nowrap">{{ $approval->operatorDetails?->name ?? '—' }}</td>

                                    <td class="text-nowrap">
                                        {{ \Carbon\Carbon::parse($approval->created_at)->format('d/m/Y') }}
                                    </td>

                                    <td class="text-nowrap">
                                        <button class="border-0 p-1 rounded-3 lh-1 action-btn approveBtn bg-success text-white px-2"
                                            data-id="{{ $approval->id }}">Approve</button>
                                        <button class="border-0 p-1 rounded-3 lh-1 action-btn rejectBtn bg-danger text-white px-2 ms-1"
                                            data-id="{{ $approval->id }}">Reject</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center text-muted py-4">No pending offer approvals.</td>
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
                    <p class="mb-0">Are you sure you want to <strong class="text-success">approve</strong> this request?</p>
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

    $(document).on('click', '.approveBtn', function () {
        $('#confirmApproveBtn').data('id', $(this).data('id'));
        $('#approveConfirmModal').modal('show');
    });

    $('#confirmApproveBtn').on('click', function () {
        const btn = $(this);
        const id  = btn.data('id');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>...');

        $.ajax({
            url: '{{ route("offerApproval.approve", ":id") }}'.replace(':id', id),
            type: 'GET',
            success: function (response) {
                if (response.statusCode == 200) {
                    toastr.success(response.message);
                    $('#approveConfirmModal').modal('hide');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    toastr.error(response.message ?? 'Something went wrong.');
                    btn.prop('disabled', false).html('Yes, Approve');
                }
            },
            error: function () {
                toastr.error('Something went wrong.');
                btn.prop('disabled', false).html('Yes, Approve');
            }
        });
    });

    $(document).on('click', '.rejectBtn', function () {
        $('#confirmRejectBtn').data('id', $(this).data('id'));
        $('#rejectConfirmModal').modal('show');
    });

    $('#confirmRejectBtn').on('click', function () {
        const btn = $(this);
        const id  = btn.data('id');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>...');

        $.ajax({
            url: '{{ route("offerApproval.reject", ":id") }}'.replace(':id', id),
            type: 'GET',
            success: function (response) {
                if (response.statusCode == 200) {
                    toastr.success(response.message);
                    $('#rejectConfirmModal').modal('hide');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    toastr.error(response.message ?? 'Something went wrong.');
                    btn.prop('disabled', false).html('Yes, Reject');
                }
            },
            error: function () {
                toastr.error('Something went wrong.');
                btn.prop('disabled', false).html('Yes, Reject');
            }
        });
    });

});
</script>
@endsection
