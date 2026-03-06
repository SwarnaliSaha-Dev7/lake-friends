@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="member-list-part position-relative">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2 flex-wrap">
                    <h2 class="fs-5 common-heading mb-md-0 fw-semibold">Approval list</h2>
                    {{-- <div class="d-flex gap-2">
                        <div class="d-flex justify-content-end">
                            <select id="statusFilter"
                                class="form-select form-select-sm w-auto fs-6 rounded-2 ps-3 shadow-none">
                                <option value="" selected disabled hidden>Status filter</option>
                                <option value="">All</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                                <option value="Blocked">Blocked</option>
                            </select>
                        </div>
                        <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addswimmingmember">+ Add swimming member</button>
                    </div> --}}
                </div>
                <div class="table-responsive">
                    <table class="table rounded-3 overflow-hidden clubmemberlist2" cellspacing="0"
                        width="100%">
                        <thead>
                            <tr>
                                <th class="text-white fw-medium align-middle text-nowrap">Type</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Date</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Maker
                                </th>
                                <th class="text-white fw-medium align-middle text-nowrap">Status</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $data)
                                <tr>
                                    <td class="text-nowrap">{{$data->module}}</td>
                                    <td class="text-nowrap">{{ \Carbon\Carbon::parse($data->created_at)->format('d/m/Y') }}</td>
                                    <td class="text-nowrap">{{$data->operatorDetails->name}}</td>
                                    <td class="text-nowrap">{{$data->status ?? 'Pending'}}</td>

                                    {{-- @if ($member->status == 'active')
                                        <td class="text-success text-nowrap">Active</td>
                                    @elseif ($member->status == 'pending_approval')
                                        <td class="text-warning text-nowrap">Pending</td>
                                    @elseif ($member->status == 'suspended')
                                        <td class="text-secondary text-nowrap">Suspended</td>
                                    @elseif ($member->status == 'terminated')
                                        <td class="text-danger text-nowrap">Terminated</td>
                                    @endif --}}

                                    <td class="text-nowrap">
                                        <button class="border-0 p-1 rounded-3 lh-1 action-btn approveBtn bg-success text-white"
                                            title="Approve" data-id="{{$data->id}}" id="">Approve</button>
                                        <button class="border-0 p-1 rounded-3 lh-1 action-btn rejectBtn bg-danger text-white"
                                            title="Reject" data-id="{{$data->id}}">Reject</button>

                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modalComponent')

    <!-- Approve Modal -->
    <div class="modal fade" id="approveConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-3">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Confirm Approve</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to approve this?</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-success" id="confirmApproveBtn" data-id="">
                        Yes, Approve
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- Reject Modal -->
    <div class="modal fade" id="rejectConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-3">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Confirm Reject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to reject this?</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmRejectBtn" data-id="">
                        Yes, Reject
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('customJS')
<script>
    $(document).ready(function() {


        $('.rejectBtn').on('click', function(){
            $('#confirmRejectBtn').data('id', $(this).data('id'));
            $('#rejectConfirmModal').modal('show');
        });

        $('#confirmRejectBtn').on('click', function(){
            let id = $(this).data('id');
            let originalBtn = $(this).prop('outerHTML'); // save original button
            $(this).prop('disabled', true).replaceWith('<span class="spinner-border spinner-border-sm text-danger"></span>');

            $.ajax({
                url: '{{route("actionApproval.reject", ":id")}}'.replace(':id', id),
                type: 'GET',
                success: function(response){
                    console.log(response)
                    if (response.statusCode == 200) {
                        toastr.success(response.message);

                        // $('.spinner-border').replaceWith(originalBtn);
                        $('.spinner-border').hide();
                        setTimeout(() => location.reload(), 1500);
                    }
                    else{
                        toastr.error('Something Went Wrong').
                        console.log(response);
                    }

                },
                error: function(){
                    toastr.error('Something Went Wrong.');
                }
            });
        });

        $('.approveBtn').on('click', function(){
            $('#confirmApproveBtn').data('id', $(this).data('id'));
            $('#approveConfirmModal').modal('show');
        });

        $('#confirmApproveBtn').on('click', function(){
            let id = $(this).data('id');
            let originalBtn = $(this).prop('outerHTML'); // save original button
            $(this).prop('disabled', true).replaceWith('<span class="spinner-border spinner-border-sm text-success"></span>');

            $.ajax({
                url: '{{route("actionApproval.approve", ":id")}}'.replace(':id', id),
                type: 'GET',
                success: function(response){
                    console.log(response)
                    if (response.statusCode == 200) {
                        toastr.success(response.message);

                        // $('.spinner-border').replaceWith(originalBtn);
                        $('.spinner-border').hide();
                        setTimeout(() => location.reload(), 1500);
                    }
                    else{
                        toastr.error('Something Went Wrong').
                        console.log(response);
                    }

                },
                error: function(){
                    toastr.error('Something Went Wrong.');
                }
            });
        });



    });
</script>
@endsection
