@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="member-list-part position-relative">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2 flex-wrap">
                    <h2 class="fs-5 common-heading mb-md-0 fw-semibold">Liquor Menu Approval List</h2>
                </div>
                <div class="table-responsive">
                    <table class="table rounded-3 overflow-hidden clubmemberlist2" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th class="text-white fw-medium align-middle text-nowrap">Sl No</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Menu Item</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Action</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Details</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Maker</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Date</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Approve/Reject</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($approvalData as $data)
                                @php
                                    $payload = is_array($data->request_payload)
                                        ? (object) $data->request_payload
                                        : json_decode($data->request_payload);

                                    $actionLabel = match($data->module) {
                                        'liquor_serving_create' => 'Add',
                                        'liquor_serving_update' => 'Edit',
                                        'liquor_serving_delete' => 'Delete',
                                        default => '—',
                                    };
                                    $actionClass = match($data->module) {
                                        'liquor_serving_create' => 'bg-success-subtle text-success border-success',
                                        'liquor_serving_update' => 'bg-info-subtle text-info border-info',
                                        'liquor_serving_delete' => 'bg-danger-subtle text-danger border-danger',
                                        default => 'bg-secondary-subtle text-secondary border-secondary',
                                    };
                                @endphp
                                <tr>
                                    <td class="text-nowrap">{{ $loop->iteration }}</td>
                                    <td class="text-nowrap fw-medium">
                                        {{ $payload->item_name ?? ($payload->name ?? ($data->entity->name ?? '—')) }}
                                    </td>
                                    <td class="text-nowrap">
                                        <span class="badge border rounded-pill px-3 py-1 {{ $actionClass }}">{{ $actionLabel }}</span>
                                    </td>
                                    <td class="text-nowrap small text-muted">
                                        @if($data->module === 'liquor_serving_create')
                                            {{ $payload->volume_ml ?? '—' }}ml @ Rs {{ $payload->price ?? '—' }}
                                        @elseif($data->module === 'liquor_serving_update')
                                            @php $old = (object)($payload->old ?? []); $new = (object)($payload->new ?? []); @endphp
                                            {{ $old->volume_ml ?? '—' }}ml→{{ $new->volume_ml ?? '—' }}ml,
                                            Rs{{ $old->price ?? '—' }}→Rs{{ $new->price ?? '—' }}
                                        @else
                                            {{ $payload->item_name ?? $data->entity->name ?? '—' }}
                                        @endif
                                    </td>
                                    <td class="text-nowrap">{{ $data->operatorDetails->name ?? '—' }}</td>
                                    <td class="text-nowrap">{{ \Carbon\Carbon::parse($data->created_at)->format('d/m/Y') }}</td>
                                    <td class="text-nowrap">
                                        <button class="border-0 p-1 rounded-3 lh-1 action-btn bg-success text-white approveBtn"
                                            data-id="{{ $data->id }}">Approve</button>
                                        <button class="border-0 p-1 rounded-3 lh-1 action-btn bg-danger text-white ms-1 rejectBtn"
                                            data-id="{{ $data->id }}">Reject</button>
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

@section('customJS')
<script>
$(document).ready(function () {

    $(document).on('click', '.approveBtn', function () {
        var $btn = $(this);
        var id   = $btn.data('id');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.get('{{ url("manage-liquor-serving-approval/approve") }}/' + id, function (res) {
            if (res.statusCode === 200) {
                toastr.success('Approved successfully.');
                $btn.closest('tr').fadeOut(400, function () { $(this).remove(); });
            } else {
                toastr.error(res.message || 'Something went wrong.');
                $btn.prop('disabled', false).html('Approve');
            }
        }).fail(function () {
            toastr.error('Something went wrong.');
            $btn.prop('disabled', false).html('Approve');
        });
    });

    $(document).on('click', '.rejectBtn', function () {
        var $btn = $(this);
        var id   = $btn.data('id');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.get('{{ url("manage-liquor-serving-approval/reject") }}/' + id, function (res) {
            if (res.statusCode === 200) {
                toastr.success('Rejected.');
                $btn.closest('tr').fadeOut(400, function () { $(this).remove(); });
            } else {
                toastr.error(res.message || 'Something went wrong.');
                $btn.prop('disabled', false).html('Reject');
            }
        }).fail(function () {
            toastr.error('Something went wrong.');
            $btn.prop('disabled', false).html('Reject');
        });
    });

});
</script>
@endsection
