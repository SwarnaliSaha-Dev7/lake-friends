@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')
    <div class="repeat-holder">
        <div class="row">
            <div class="col-12">
                <div class="member-list-part position-relative">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2 flex-wrap">
                        <h2 class="fs-5 common-heading mb-md-0 fw-semibold">Operator list</h2>
                        <div class="d-flex gap-2">
                            <div class="d-flex justify-content-end">
                                <select id="statusFilter"
                                    class="form-select form-select-sm w-auto fs-6 rounded-2 ps-3 shadow-none">
                                    <option value="" selected disabled hidden>Status filter</option>
                                    <option value="">All</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                    {{-- <option value="Blocked">Blocked</option> --}}
                                </select>
                            </div>
                            <a href="{{ route('manage-operators.create') }}"><button class="btn btn-info">+ Add</button></a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table rounded-3 overflow-hidden clubmemberlist2" cellspacing="0"
                            width="100%">
                            <thead>
                                <tr>
                                    <th class="text-white fw-medium align-middle text-nowrap">Sl No.</th>
                                    <th class="text-white fw-medium align-middle text-nowrap">Name</th>
                                    <th class="text-white fw-medium align-middle text-nowrap">Email</th>
                                    <th class="text-white fw-medium align-middle text-nowrap">Satus</th>
                                    <th class="text-white fw-medium align-middle text-nowrap">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($operatorList as $key => $operator)
                                    <tr>
                                        <td class="text-nowrap">{{ $key + 1 }}</td>
                                        <td class="text-nowrap">{{ $operator->name }}</td>
                                        <td class="text-nowrap">{{ $operator->email }}</td>
                                        @if($operator->status == 1)
                                            <td class="text-success text-nowrap">Active</td>
                                        @else
                                            <td class="text-danger text-nowrap">Inactive</td>
                                        @endif

                                        <td class="text-nowrap">

                                            <a href="{{ route('manage-operators.edit', $operator->id) }}"><button
                                                class="border-0 bg-light p-1 rounded-3 lh-1 action-btn" title="Edit"><small><i
                                                        class="fa-solid fa-pen-to-square"></i></small></button></a>

                                            <form id="delete-form-{{ $operator->id }}" action="{{ route('manage-operators.destroy', $operator->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="border-0 bg-light p-1 rounded-3 lh-1 action-btn delete-row" id="delete-btn" title="Delete" data-id="{{ $operator->id }}">
                                                    <small><i class="fa-solid fa-trash"></i></small>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- <div class="text-end">
                        <a href="#" class="fw-semibold"><small><u>View All</u></small></a>
                    </div> -->
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modalComponent')
    <!-- Delete row table Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-3">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to delete this row?</p>
                </div>
                <input type="hidden" id="delete_user_id" value="" >
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        Yes, Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('customJS')
<script>
    $(document).ready(function() {

        $(document).on("click", "#delete-btn", function(e) {
            e.preventDefault(); // stop form submit

            let operatorId = $(this).data("id");
            $("#delete_user_id").val(operatorId);

        });

        $("#confirmDeleteBtn").click(function(event) {
            let operatorId = $("#delete_user_id").val();
            $(`#delete-form-${operatorId}`).submit();
        });

    });
</script>
@endsection
