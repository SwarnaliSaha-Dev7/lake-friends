@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="member-list-part position-relative">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2 flex-wrap">
                    <h2 class="fs-5 common-heading mb-md-0 fw-semibold">Club Members Approval list</h2>
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
                                <th class="text-white fw-medium align-middle text-nowrap">Type</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Date</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Maker
                                </th>
                                <th class="text-white fw-medium align-middle text-nowrap">Status</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($clubMembershipData as $data)
                                    {{-- @php
                                        $payloadJson = $data->request_payload;
                                        $payload = json_decode($payloadJson);
                                        // echo "<pre>"; print_r($payload); echo "</pre>";
                                        // $detail = "";

                                        $detail = '';

                                        if ($data->module == 'member_delete') {
                                            $member = \App\Models\Member::find($data->entity_id);
                                            $detail = $member->name ?? '';
                                        }

                                        elseif (isset($payload->swim_name)) {
                                            $detail = $payload?->swim_name ?? '';
                                        }
                                        elseif (isset($payload->swim_member_name)) {
                                            $detail = $payload?->swim_member_name ?? '';
                                        }
                                        elseif (isset($payload->name)) {
                                            $detail = $payload?->name ?? '';
                                        }

                                    @endphp --}}
                                <tr>
                                    <td class="text-nowrap">{{ $loop->iteration }}</td>

                                    @if($data->module == 'locker_purchase')
                                        <td class="text-nowrap"><a href="javascript:void(0)" class="lockerDtls" data-id="{{$data->id}}">{{ $data->entity->name }}</a></td>
                                    @elseif($data->module == 'plan_renewal')
                                        @php
                                            $renewalMember = $data->entity->member ?? null;
                                            $renewalPlan   = $data->entity->membershipPlanType ?? null;
                                        @endphp
                                        <td class="text-nowrap">
                                            {{ $renewalMember->name ?? '—' }}
                                            @if($renewalPlan)
                                                <span class="text-muted small">— {{ $renewalPlan->name }}</span>
                                            @endif
                                        </td>
                                    @elseif($data->module =="add_on_purchase")
                                        <td class="text-nowrap"><a href="javascript:void(0)" class="addOnPurchaseDtls" data-id="{{$data->id}}">{{ $data->entity->name }}</a></td>
                                    @else
                                        <td class="text-nowrap"><a href="javascript:void(0)" class="clubMemberDetail" data-id="{{$data->id}}">{{ $data->entity->name }}</a></td>
                                    @endif
                                    <td class="text-nowrap">{{ \Illuminate\Support\Str::title(str_replace('_', ' ', $data->module)) }}</td>
                                    <td class="text-nowrap">{{ \Carbon\Carbon::parse($data->created_at)->format('d/m/Y') }}</td>
                                    <td class="text-nowrap">{{$data->operatorDetails->name}}</td>
                                    <td class="text-nowrap">{{ \Illuminate\Support\Str::title(str_replace('_', ' ', $data->status ?? 'Pending')) }}</td>

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

    <div class="row">
        <div class="col-12">
            <div class="member-list-part position-relative">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2 flex-wrap">
                    <h2 class="fs-5 common-heading mb-md-0 fw-semibold">Swimming Members Approval list</h2>
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
                                <th class="text-white fw-medium align-middle text-nowrap">Type</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Date</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Maker
                                </th>
                                <th class="text-white fw-medium align-middle text-nowrap">Status</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($swimmingMembershipData as $data)
                                    {{-- @php
                                        $payloadJson = $data->request_payload;
                                        $payload = json_decode($payloadJson);
                                        // echo "<pre>"; print_r($payload); echo "</pre>";
                                        // $detail = "";

                                        $detail = '';

                                        if ($data->module == 'member_delete') {
                                            $member = \App\Models\Member::find($data->entity_id);
                                            $detail = $member->name ?? '';
                                        }
                                        elseif (isset($payload->swim_name)) {
                                            $detail = $payload?->swim_name ?? '';
                                        }
                                        elseif (isset($payload->swim_member_name)) {
                                            $detail = $payload?->swim_member_name ?? '';
                                        }
                                        elseif (isset($payload->name)) {
                                            $detail = $payload?->name ?? '';
                                        }
                                    @endphp --}}
                                <tr>
                                    <td class="text-nowrap">{{ $loop->iteration }}</td>
                                    <td class="text-nowrap"><a href="javascript:void(0)" class="swimMemberDetail" data-id="{{$data->id}}">{{ $data->entity->name }}</a></td>
                                    <td class="text-nowrap">{{ \Illuminate\Support\Str::title(str_replace('_', ' ', $data->module)) }}</td>
                                    <td class="text-nowrap">{{ \Carbon\Carbon::parse($data->created_at)->format('d/m/Y') }}</td>
                                    <td class="text-nowrap">{{$data->operatorDetails->name}}</td>
                                    <td class="text-nowrap">{{ \Illuminate\Support\Str::title(str_replace('_', ' ', $data->status ?? 'Pending')) }}</td>

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

    <!-- edit Swim Member Modal start -->
    <div class="modal fade" id="editswimmingmember" tabindex="-1" aria-labelledby="editswimmingmemberModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h2 class="modal-title fs-5 fw-semibold" id="addswimmingmemberModalLabel">Approval details
                    </h2>
                    <button type="button" class="btn-close bg-transparent fs-5 lh-1" data-bs-dismiss="modal"
                        aria-label="Close"><i class="fa-regular fa-circle-xmark"></i></button>
                </div>
                <div class="modal-body">
                    <form action="" id="swimmingMemberEditForm">

                        <div class="row">
                            <div class="col-lg-12">
                                <label for="" class="form-label fw-semibold text-dark mb-3"><span
                                        class="text-info rounded-3 label-icon p-1 d-inline-flex align-items-center justify-content-center me-2"><i
                                            class="fa-regular fa-user"></i></span> Personal Details</label>
                                <div class="row">
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Full Name</small></label>
                                            <input type="text" class="form-control py-2 shadow-none" name="swim_name" id="swim_member_name"
                                                placeholder="Full Name" required readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Email</small></label>
                                            <input type="email" class="form-control py-2 shadow-none" name="swim_email" id="swim_member_email"
                                                placeholder="Email" required readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Phone</small></label>
                                            <input type="tel" class="form-control py-2 shadow-none phone-input" name="swim_phone" id="swim_member_phone"
                                                placeholder="Phone" required readonly>
                                            <span class="error-div text-danger"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Address</small></label>

                                            <textarea class="form-control py-2 shadow-none" id="swim_member_address" name="swim_address" rows="3"
                                                placeholder="Address" required readonly></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Police Station</small></label>
                                            <input type="text" class="form-control py-2 shadow-none" name="swim_member_police_station" id="swim_member_police_station"
                                                placeholder="Police Station" required readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Age</small></label>
                                            <input type="number" class="form-control py-2 shadow-none" name="swim_age" id="swim_member_age"
                                                placeholder="Age" required readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Sex</small></label>
                                            <select name="swim_sex" id="swim_member_sex" class="form-select py-2 shadow-none" required readonly>
                                                <option value="" hidden disabled selected>Sex</option>
                                                <option value="male">Male</option>
                                                <option value="female">Female</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Height</small></label>
                                            <input type="number" class="form-control py-2 shadow-none" name="swim_height" id="swim_member_height"
                                                placeholder="Height" required readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Weight</small></label>
                                            <input type="number" class="form-control py-2 shadow-none" name="swim_weight" id="swim_member_weight"
                                                placeholder="Weight" required readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Pulse Rate</small></label>
                                            <input type="number" class="form-control py-2 shadow-none" name="swim_pulse_rate" id="swim_member_pulse_rate"
                                                placeholder="Pulse Rate" required readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Batch</small></label>
                                            <input type="text" class="form-control py-2 shadow-none" name="swim_batch" id="swim_member_batch"
                                                placeholder="Batch" required readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Vaccination</small></label>
                                            <input type="text" class="form-control py-2 shadow-none" name="swim_vaccination" id="swim_member_vaccination"
                                                placeholder="Vaccination" required readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" value="1" name="swim_i_agree" id="swim_member_i_agree" required disabled>
                                            <label class="form-check-label" for="swim_member_i_agree">
                                                <small>I have gone through the rules & Regulations overleaf and undertake to abide by the same at my risk and cost.</small>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-lg-8">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100"><small>I am not suffering
                                                    from</small></label>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" id="inlineCheckbox1"
                                                    value="occational_faint" name="swim_disease[]" disabled>
                                                <label class="form-check-label" for="inlineCheckbox1"><small>Any sudden
                                                        or
                                                        occasional faint</small></label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" id="inlineCheckbox2"
                                                    value="lung_heart_trouble" name="swim_disease[]" disabled>
                                                <label class="form-check-label" for="inlineCheckbox2"><small>Lung/Heart
                                                        trouble</small></label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" id="inlineCheckbox3"
                                                    value="skin_disease" name="swim_disease[]" disabled>
                                                <label class="form-check-label" for="inlineCheckbox3"><small>Skin
                                                        disease</small></label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" id="inlineCheckbox4"
                                                    value="other_disease" name="swim_disease[]" disabled>
                                                <label class="form-check-label" for="inlineCheckbox4"><small>Any other
                                                        disease</small></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Photo</small></label>
                                            <label class="file-upload-box position-relative text-center border rounded-3 w-100 p-2">
                                                <img src="" alt="" id="swim_image">
                                                {{-- <input type="file" class="file-input opacity-0 position-absolute start-0 w-100 profile-image" name="swim_image" accept=".jpg,.jpeg,.png">
                                                <div class="upload-content">
                                                    <i class="upload-icon"><i
                                                            class="fa-solid fa-arrow-up-from-bracket"></i></i>
                                                    <p class="upload-text mb-0">
                                                        Upload Passport size Image & Signature
                                                    </p>
                                                    <small class="text-muted">
                                                        Image format, PNG & JPEG, max file size 5MB
                                                    </small>
                                                </div> --}}
                                            </label>
                                            <span class="error-div text-danger"></span>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Status</small></label>
                                            <select name="swim_status" id="swim_status" class="form-select py-2 shadow-none" disabled>
                                                <option value="" hidden disabled selected>Status</option>
                                                <option value="active">Active</option>
                                                <option value="pending">Pending</option>
                                                <option value="rejected">Rejected</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="col-lg-12">
                                <label for="" class="form-label fw-semibold text-dark my-3"><span
                                        class="text-info rounded-3 label-icon p-1 d-inline-flex align-items-center justify-content-center me-2"><i
                                            class="fa-regular fa-user"></i></span> Family Details</label>
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Father/Guardian’s Full Name</small></label>
                                            <input type="text" class="form-control py-2 shadow-none" id="swim_guardian_name"
                                                placeholder="Father/Guardian’s Full Name" name="swim_guardian_name" required readonly>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Father/Guardian’s Occupation</small></label>
                                            <input type="text" class="form-control py-2 shadow-none" id="swim_guardian_occupation"
                                                placeholder="Father/Guardian’s Occupation" name="swim_guardian_occupation" required readonly>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Father/Guardian’s Photo</small></label>
                                            <label class="file-upload-box position-relative text-center border rounded-3 w-100 p-2">
                                                <img src="" alt="" id="swim_guardian_image">
                                                {{-- <input type="file" class="file-input opacity-0 position-absolute start-0 w-100 profile-image" name="swim_guardian_image" accept=".jpg,.jpeg,.png">
                                                <div class="upload-content">
                                                    <i class="upload-icon"><i
                                                            class="fa-solid fa-arrow-up-from-bracket"></i></i>
                                                    <p class="upload-text mb-0">
                                                        Upload Passport size Image & Signature
                                                    </p>
                                                    <small class="text-muted">
                                                        Image format, PNG & JPEG, max file size 5MB
                                                    </small>
                                                </div> --}}
                                            </label>
                                            <span class="error-div text-danger"></span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="col-lg-12" id="swimCardDetailCol">
                                <label for="" class="form-label fw-semibold text-dark my-3"><span
                                        class="text-info rounded-3 label-icon p-1 d-inline-flex align-items-center justify-content-center me-2"><i
                                            class="fa-regular fa-regular fa-credit-card"></i></span> Card
                                    Details</label>
                                <div class="row">
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Card No.</small></label>
                                            <select name="swim_card_id" id="swim_card_no" class="form-select py-2 shadow-none" disabled readonly>
                                                <option value="" hidden disabled selected>Card No.</option>
                                                @foreach ($cards as $card)
                                                    <option value="{{$card->id}}">{{$card->card_no}}</option>
                                                @endforeach

                                            </select>
                                        </div>
                                    </div>
                                    {{--
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Current Card No.</small></label>
                                            <p id="current_card_no"></p>
                                        </div>
                                    </div> --}}
                                </div>

                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- add Swim Member Modal end  -->

    <!-- edit Club Member Modal start -->
    <div class="modal fade" id="editclubmember" tabindex="-1" aria-labelledby="editclubmemberModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h2 class="modal-title fs-5 fw-semibold" id="addclubmemberModalLabel">Edit Member</h2>
                    <button type="button" class="btn-close bg-transparent fs-5 lh-1" data-bs-dismiss="modal"
                        aria-label="Close"><i class="fa-regular fa-circle-xmark"></i></button>
                </div>
                <div class="modal-body">
                    <form id="clubMemberEditForm" enctype="multipart/form-data">
                        {{-- @csrf
                        <input type="hidden" name="member_id" value="" id="club_member_id"> --}}
                        <div class="row">
                            <div class="col-lg-6">
                                <label for="" class="form-label fw-semibold text-dark mb-3"><span
                                        class="text-info rounded-3 label-icon p-1 d-inline-flex align-items-center justify-content-center me-2"><i
                                            class="fa-regular fa-user"></i></span> Personal Details</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Name</small></label>
                                            <input type="text" class="form-control py-2 shadow-none text-only" id="club_member_name" name="name"
                                                placeholder="Full Name" required readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Email</small></label>
                                            <input type="email" class="form-control py-2 shadow-none" id="club_member_email" name="email"
                                                placeholder="Email" required readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Phone</small></label>
                                            <input type="tel" class="form-control py-2 shadow-none phone-input" id="club_member_phone" name="phone"
                                                placeholder="Phone" required readonly>
                                            <span class="error-div text-danger"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Blood Group</small></label>
                                            <select id="club_member_blood_grp" class="form-select py-2 shadow-none" name="blood_grp" required disabled>
                                                <option value="">Blood Group</option>
                                                <option value="A+">A+</option>
                                                <option value="A-">A-</option>
                                                <option value="B+">B+</option>
                                                <option value="B-">B-</option>
                                                <option value="AB+">AB+</option>
                                                <option value="AB-">AB-</option>
                                                <option value="O+">O+</option>
                                                <option value="O-">O-</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="address" class="form-label w-100 mb-1 w-100"><small>Address</small></label>
                                            <textarea class="form-control py-2 shadow-none" id="club_member_address" name="address" rows="3"
                                                placeholder="Address" required readonly></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Photo</small></label>
                                            <label class="file-upload-box position-relative text-center border rounded-3 w-100 p-2">
                                                <img src="" alt="" id="club_image">
                                                {{-- <input type="file" class="file-input opacity-0 position-absolute start-0 w-100 profile-image" name="image" accept=".jpg,.jpeg,.png" id="club_member_image">
                                                <div class="upload-content">
                                                    <i class="upload-icon"><i
                                                            class="fa-solid fa-arrow-up-from-bracket"></i></i>
                                                    <p class="upload-text mb-0">
                                                        Passport size Image
                                                    </p>
                                                    <small class="text-muted">
                                                        JPG, JPEG & PNG, max file size 5MB
                                                    </small>
                                                </div> --}}
                                            </label>
                                            <span class="error-div text-danger"></span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="col-lg-6">
                                <label for="" class="form-label fw-semibold text-dark mb-3"><span
                                        class="text-info rounded-3 label-icon p-1 d-inline-flex align-items-center justify-content-center me-2"><i
                                            class="fa-regular fa-user"></i></span> Spouse Details</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Name</small></label>
                                            <input type="text" class="form-control py-2 shadow-none text-only" id="spouse_name" name="spouse_name"
                                                placeholder="Spouse Full Name" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Email</small></label>
                                            <input type="email" class="form-control py-2 shadow-none" id="spouse_email" name="spouse_email"
                                                placeholder="Email" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Phone</small></label>
                                            <input type="tel" class="form-control py-2 shadow-none phone-input" id="edit_spouse_phone" name="spouse_phone"
                                                placeholder="Phone" readonly>
                                            <span class="error-div text-danger"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Blood Group</small></label>
                                            <select id="spouse_blood_grp" class="form-select py-2 shadow-none" name="spouse_blood_grp" disabled>
                                                <option value="">Blood Group</option>
                                                <option value="A+">A+</option>
                                                <option value="A-">A-</option>
                                                <option value="B+">B+</option>
                                                <option value="B-">B-</option>
                                                <option value="AB+">AB+</option>
                                                <option value="AB-">AB-</option>
                                                <option value="O+">O+</option>
                                                <option value="O-">O-</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="spouse-address" class="form-label w-100 mb-1 w-100"><small>Address</small></label>
                                            <textarea class="form-control py-2 shadow-none" id="spouse_address" name="spouse_address" rows="3"
                                                placeholder="Address" readonly></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6" id="spouse_image_div">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Photo</small></label>
                                            <label class="file-upload-box position-relative text-center border rounded-3 w-100 p-2">
                                                {{-- <input type="file" class="file-input opacity-0 position-absolute start-0 w-100 profile-image" name="spouse_image" id="spouse_image" accept=".jpg,.jpeg,.png">
                                                <div class="upload-content">
                                                    <i class="upload-icon"><i
                                                            class="fa-solid fa-arrow-up-from-bracket"></i></i>
                                                    <p class="upload-text mb-0">
                                                        Passport size Image
                                                    </p>
                                                    <small class="text-muted">
                                                        JPG, JPEG & PNG, max file size 5MB
                                                    </small>
                                                </div> --}}
                                                <img src="" alt="" id="club_spouse_image">
                                            </label>
                                            <span class="error-div text-danger"></span>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="col-md-6 col-xl-3">
                                <div class="form-part mb-3">
                                    <label for="" class="form-label w-100 mb-1 w-100"><small>Status</small></label>
                                    <select name="club_status" id="club_status" class="form-select py-2 shadow-none" disabled>
                                        <option value="" hidden disabled selected>Status</option>
                                        <option value="active">Active</option>
                                        <option value="pending">Pending</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-12" id="clubCardDetailCol">
                                <label for="" class="form-label fw-semibold text-dark my-3"><span
                                        class="text-info rounded-3 label-icon p-1 d-inline-flex align-items-center justify-content-center me-2"><i
                                            class="fa-regular fa-regular fa-credit-card"></i></span> Card
                                    Details</label>
                                <div class="row">

                                    <div class="col-md-6 col-xl-4">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Card No.</small></label>
                                            <select name="card_id" id="club_card_no" class="form-select py-2 shadow-none" disabled>
                                                <option value="" selected="" hidden="" disabled="">Card No.</option>
                                                @foreach ($cards as $card)
                                                    <option value="{{$card->id}}">{{$card->card_no}}</option>
                                                @endforeach

                                            </select>
                                        </div>
                                    </div>

                                </div>

                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- edit Club Member Modal end  -->



@endsection

@section('customJS')
<script>
    $(document).ready(function() {


        $('.rejectBtn').on('click', function(){
            $('#confirmRejectBtn').data('id', $(this).data('id'));
            $('#rejectConfirmModal').modal('show');
        });

        $('#confirmRejectBtn').on('click', function(){
            // let id = $(this).data('id');
            // let originalBtn = $(this).prop('outerHTML'); // save original button
            // $(this).prop('disabled', true).replaceWith('<span class="spinner-border spinner-border-sm text-danger"></span>');

            let btn = $(this);
            let id = btn.data('id');

            btn.prop('disabled', true).css({
                'opacity':'1',
                'cursor':'not-allowed'
            });

            $.ajax({
                url: '{{route("memberActionApproval.reject", ":id")}}'.replace(':id', id),
                type: 'GET',
                success: function(response){
                    console.log(response)
                    if (response.statusCode == 200) {
                        toastr.success(response.message);

                        // $('.spinner-border').replaceWith(originalBtn);
                        // $('.spinner-border').hide();
                        setTimeout(() => location.reload(), 1500);
                    }
                    else{
                        toastr.error('Something Went Wrong').
                        console.log(response);

                        btn.prop('disabled', false).css({
                            'opacity':'',
                            'cursor':''
                        });

                    }

                },
                error: function(){
                    toastr.error('Something Went Wrong.');

                    btn.prop('disabled', false).css({
                    'opacity':'',
                    'cursor':''
                    });

                }
            });
        });

        $('.approveBtn').on('click', function(){
            $('#confirmApproveBtn').data('id', $(this).data('id'));
            $('#approveConfirmModal').modal('show');
        });

        $('#confirmApproveBtn').on('click', function(){
            // let id = $(this).data('id');
            // let originalBtn = $(this).prop('outerHTML'); // save original button
            // $(this).prop('disabled', true).replaceWith('<span class="spinner-border spinner-border-sm text-success"></span>');

            let btn = $(this);
            let id = btn.data('id');

            btn.prop('disabled', true).css({
                'opacity':'1',
                'cursor':'not-allowed'
            });

            $.ajax({
                url: '{{route("memberActionApproval.approve", ":id")}}'.replace(':id', id),
                type: 'GET',
                success: function(response){
                    console.log(response)
                    if (response.statusCode == 200) {
                        toastr.success(response.message);

                        // $('.spinner-border').replaceWith(originalBtn);
                        //$('.spinner-border').hide();
                        setTimeout(() => location.reload(), 1500);
                    }
                    else{
                        toastr.error('Something Went Wrong').
                        console.log(response);

                        btn.prop('disabled', false).css({
                            'opacity':'',
                            'cursor':''
                        });
                    }

                },
                error: function(){
                    toastr.error('Something Went Wrong.');

                    btn.prop('disabled', false).css({
                        'opacity':'',
                        'cursor':''
                    });
                }
            });
        });

        $(document).on('click', '.clubMemberDetail', function(e){
            e.preventDefault();
            let id = $(this).data('id');

            $.ajax({
                url: '{{route("memberActionApproval.view", ":id")}}'.replace(':id', id),
                type: 'GET',
                success: function(response){
                    console.log(response)
                    if (response.statusCode == 200) {
                        $('#club_member_name').val(response.data['name']);
                        $('#club_member_email').val(response.data['email']);
                        $('#club_member_phone').val(response.data['phone']);
                        $('#club_member_address').val(response.data['address']);

                        $('#club_status').val(response.data['club_status']);

                        $('#club_member_blood_grp').val(response.data['blood_grp']);

                        $('#spouse_name').val(response.data['spouse_name']);
                        $('#spouse_email').val(response.data['spouse_email']);
                        $('#edit_spouse_phone').val(response.data['spouse_phone']);
                        $('#spouse_blood_grp').val(response.data['spouse_blood_grp']);
                        $('#spouse_address').val(response.data['spouse_address']);

                        let card_no = response.data['card_id'];
                        if (card_no) {
                            $('#clubCardDetailCol').show();
                            $('#club_card_no').val(response.data['card_id']);
                        }
                        else{
                            $('#clubCardDetailCol').hide();
                        }

                        $("#club_image").attr("src", '/' + response.data['image']);

                        if (response.data['spouse_image']) {
                            $("#club_spouse_image").attr("src", '/' + response.data['spouse_image']);
                        }
                        else{
                            $("#spouse_image_div").hide();
                        }

                        // console.log(response.purchase_history);


                        // $('.spinner-border').replaceWith(originalBtn);
                        $('#editclubmember').modal('show');
                    }
                    else{
                        // toastr.error('Something Went Wrong').
                        // console.log(response);
                    }

                },
                error: function(){
                    toastr.error('Something Went Wrong.');
                }
            });
        });

        $(document).on('click', '.swimMemberDetail', function(e){
            e.preventDefault();
            let id = $(this).data('id');

            $.ajax({
                url: '{{route("memberActionApproval.view", ":id")}}'.replace(':id', id),
                type: 'GET',
                success: function(response){
                    if (response.statusCode == 200) {
                        // console.log(response);
                        // $('#swim_member_id').val(memberId)
                        $('#swim_member_name').val(response.data['swim_name']);
                        $('#swim_member_email').val(response.data['swim_email']);
                        $('#swim_member_phone').val(response.data['swim_phone']);
                        $('#swim_member_address').val(response.data['swim_address']);
                        $('#swim_member_police_station').val(response.data['swim_police_station']);
                        $('#swim_member_age').val(response.data['swim_age']);
                        $('#swim_member_sex').val(response.data['swim_sex']);
                        $('#swim_member_height').val(response.data['swim_height']);
                        $('#swim_member_weight').val(response.data['swim_weight']);
                        $('#swim_member_pulse_rate').val(response.data['swim_pulse_rate']);
                        $('#swim_member_batch').val(response.data['swim_batch']);
                        $('#swim_member_vaccination').val(response.data['swim_vaccination']);
                        $('#swim_member_i_agree').prop('checked', response.data['swim_i_agree'] == 1);
                        let diseases = response.data['swim_disease'];
                        $('input[name="swim_disease[]"]').prop('checked', false);
                        if(diseases && diseases.length > 0){
                            diseases.forEach(function(disease) {
                                $('input[name="swim_disease[]"][value="' + disease + '"]').prop('checked', true);
                            });
                        }
                        $('#swim_status').val(response.data['swim_status']);
                        $('#swim_guardian_name').val(response.data['swim_guardian_name']);
                        $('#swim_guardian_occupation').val(response.data['swim_guardian_occupation']);

                        let card_no = response.data['swim_card_id'];
                        if (card_no) {
                            $('#swimCardDetailCol').show();
                            $('#swim_card_no').val(response.data['swim_card_id']);
                        }
                        else{
                            $('#swimCardDetailCol').hide();
                        }

                        $("#swim_image").attr("src", '/' + response.data['swim_image']);

                        $("#swim_guardian_image").attr("src", '/' + response.data['swim_guardian_image']);

                        // console.log(response.purchase_history);


                        // $('.spinner-border').replaceWith(originalBtn);
                        $('#editswimmingmember').modal('show');
                    }
                    else{
                        // toastr.error('Something Went Wrong').
                        // console.log(response);
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
