@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')

    <div class="repeat-holder">
        <div class="row">
            <div class="col-12">
                <div class="member-list-part position-relative">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2 flex-wrap">
                        <h2 class="fs-5 common-heading mb-md-0 fw-semibold">Beverage Categories List</h2>
                        <div class="d-flex gap-2">
                            <a href="{{ route('manage-beverage-categories.create') }}" class="btn btn-info">+ Add</a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table rounded-3 overflow-hidden clubmemberlist2" cellspacing="0"
                            width="100%">
                            <thead>
                                <tr>
                                    <th class="text-white fw-medium align-middle text-nowrap">Sl No.</th>
                                    <th class="text-white fw-medium align-middle text-nowrap">Name</th>
                                    <th class="text-white fw-medium align-middle text-nowrap">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($beverageCatList as $beverages)
                                <tr>

                                    <td class="text-nowrap">{{ $loop->iteration }}</td>

                                    <td class="text-nowrap">{{ $beverages->name }}</td>
                                    <td class="text-nowrap d-flex gap-1 flex-wrap">

                                        <a href="{{ route('manage-beverage-categories.edit', $beverages->id) }}" class="border-0 bg-light p-1 rounded-3 lh-1 action-btn" title="Edit">
                                            <small>
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </small>
                                        </a>

                                        <form id="delete-form-{{ $beverages->id }}" action="{{ route('manage-beverage-categories.destroy', $beverages->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                            class="border-0 bg-light p-1 rounded-3 lh-1 action-btn  delete-row" id="delete-btn"
                                            data-id="{{ $beverages->id }}" title="Delete">
                                                <small>
                                                    <i class="fa-solid fa-trash"></i>
                                                </small>
                                            </button>

                                        </form>
                                    </td>
                                </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>
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

                <input type="hidden" id="delete_user_id" value="">

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
//Delete JQuery

$(document).ready(function(){

    $(document).on("click", "#delete-btn", function(e){
        e.preventDefault(); // stop form submit

        let beverageCatId = $(this).data("id");
        $('#delete_user_id').val(beverageCatId);
    });

    $('#confirmDeleteBtn').click(function(event){

        let beverageCatId = $("#delete_user_id").val();
        $(`#delete-form-${beverageCatId}`).submit();
    });
});
</script>

@endsection
