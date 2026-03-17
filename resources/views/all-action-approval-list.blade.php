@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="member-list-part position-relative">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2 flex-wrap">
                    <h2 class="fs-5 common-heading mb-md-0 fw-semibold">All Action Approval list</h2>
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
                                <th class="text-white fw-medium align-middle text-nowrap">Sl No</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Details</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Action Type</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Plan Type</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Maker</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Made At</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Status</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Checker</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Checked At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($actionApprovalList as $data)
                                <tr>
                                    <td class="text-nowrap">{{ $loop->iteration }}</td>
                                    <td class="text-nowrap">
                                        @if($data->entity_model === 'Member')
                                            {{ $data->entity->email ?? '-' }}
                                        @elseif($data->entity_model === 'FoodItem')
                                            {{ $data->entity->name ?? '-' }}
                                        @elseif($data->module === 'godown_stock_management')
                                            {{ $data->entity?->foodItem?->name ?? '-' }}
                                        @endif
                                    </td>
                                    <td class="text-nowrap">{{ \Illuminate\Support\Str::title(str_replace('_', ' ', $data->module)) }}</td>
                                    <td class="text-nowrap">@if($data->entity_model === 'Member'){{ $data->membershipType?->name ?? '-' }}@else - @endif</td>
                                    <td class="text-nowrap">{{$data->operatorDetails->name}}</td>
                                    <td class="text-nowrap">{{ \Carbon\Carbon::parse($data->created_at)->format('d/m/Y') }}</td>
                                    <td class="text-nowrap">{{ \Illuminate\Support\Str::title(str_replace('_', ' ', $data->status ?? 'Pending')) }}</td>
                                    <td class="text-nowrap">{{ $data->checker?->name ?? '-' }}</td>
                                    <td class="text-nowrap">{{ $data->approved_or_rejected_at ? \Carbon\Carbon::parse($data->approved_or_rejected_at)->format('d/m/Y') : '-' }}</td>
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
@endsection

@section('customJS')
<script>
    $(document).ready(function() {
        //
    });
</script>
@endsection
