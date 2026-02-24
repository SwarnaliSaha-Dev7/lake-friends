@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')

    <div class="repeat-holder">
        <div class="row">
            <div class="col-12">
                <div class="member-list-part position-relative">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2 flex-wrap">
                        <h2 class="fs-5 common-heading mb-md-0 fw-semibold">GST Rate list</h2>
                        <div class="d-flex gap-2">
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
                            
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table rounded-3 overflow-hidden clubmemberlist2" cellspacing="0"
                            width="100%">
                            <thead>
                                <tr>
                                    <th class="text-white fw-medium align-middle text-nowrap">Sl No.</th>
                                    <th class="text-white fw-medium align-middle text-nowrap">GST Percentage</th>
                                    <th class="text-white fw-medium align-middle text-nowrap">GST Type</th>
                                    <th class="text-white fw-medium align-middle text-nowrap">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($gstList as $gst)
                                <tr>

                                    <td class="text-nowrap">{{ $loop->iteration }}</td>

                                    <td class="text-nowrap">{{ $gst->gst_percentage }}</td>

                                    <td class="text-nowrap">{{ ucwords(str_replace('_', ' ', $gst->gst_type)) }}</td>

                                    <!-- <td class="text-success text-nowrap">No</td> -->
                                    <td class="text-nowrap">

                                        <a href="{{ route('manage-gst-rates.edit', $gst->id) }}" class="border-0 bg-light p-1 rounded-3 lh-1 action-btn" title="Edit">
                                            <small>
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </small>
                                        </a>

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

@endsection

@section('customJS')

@endsection