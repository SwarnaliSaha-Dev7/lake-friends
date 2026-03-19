@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="member-list-part position-relative">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2 flex-wrap">
                    <h2 class="fs-5 common-heading mb-md-0 fw-semibold">Club Member list</h2>
                    <div class="d-flex gap-2">
                        <div class="d-flex justify-content-end">
                            <select id="statusFilter"
                                class="form-select form-select-sm w-auto fs-6 rounded-2 ps-3 shadow-none">
                                <option value="" selected disabled hidden>Status filter</option>
                                <option value="">All</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Pending</option>
                                <option value="Blocked">Rejected</option>
                            </select>
                        </div>
                        <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addclubmember" id="addClubMemberBtn">+ Add club member</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table rounded-3 overflow-hidden clubmemberlist2" cellspacing="0"
                        width="100%">
                        <thead>
                            <tr>
                                <th class="text-white fw-medium align-middle text-nowrap">Sl No</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Name</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Phone</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Card Number
                                </th>
                                <th class="text-white fw-medium align-middle text-nowrap">Wallet</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Exp. Date</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Approve by
                                </th>
                                <th class="text-white fw-medium align-middle text-nowrap">Status</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($members as $member)
                            <tr>
                                <td class="text-nowrap">{{ $loop->iteration }}</td>
                                <td class="text-nowrap">{{$member->name}}</td>
                                <td class="text-nowrap">{{$member->phone}}</td>
                                <td class="text-nowrap">{{ $member->cardDetails?->card_no ?? '-' }}</td>
                                <td class="text-nowrap">₹ {{$member->walletDetails?->current_balance ?? 0}}</td>
                                <td class="text-nowrap">{{ isset($member->purchaseHistory[0]) ? \Carbon\Carbon::parse($member->purchaseHistory[0]->expiry_date)->format('d/m/Y') : 'N/A' }}</td>
                                <td class="text-nowrap">
                                    {{ ucwords($member->latestApproval?->checker?->name ?? '-') }}
                                </td>
                                @if ($member->status == 'active')
                                    <td class="text-success text-nowrap">Active</td>
                                @elseif ($member->status == 'pending')
                                    <td class="text-warning text-nowrap">Pending</td>
                                @elseif ($member->status == 'rejected')
                                    <td class="text-danger text-nowrap">Rejected</td>
                                @endif
                                <td class="text-nowrap">
                                    <button class="border-0 bg-light p-1 rounded-3 lh-1 action-btn viewProfileBtn" data-bs-toggle="modal" data-bs-target="#viewprofile"
                                        title="View Profile" data-id="{{$member->id}}">
                                        <small>
                                            <i class="fa-regular fa-eye"></i>
                                        </small>
                                    </button>
                                    <button class="border-0 bg-light p-1 rounded-3 lh-1 action-btn membershipPlanBtn" data-bs-toggle="modal" data-bs-target="#membershipplan"
                                        title="Membership Plan" data-id="{{$member->id}}">
                                        <small>
                                            <i class="fa-sharp fa-clock-rotate-left"></i>
                                        </small>
                                    </button>
                                    <button class="border-0 bg-light p-1 rounded-3 lh-1 action-btn walletRechargeBtn"
                                            title="Wallet Recharge" data-id="{{$member->id}}"><small><i
                                            class="fa-solid fa-wallet"></i></small></button>
                                    <button class="border-0 bg-light p-1 rounded-3 lh-1 action-btn lockerBtn" data-bs-toggle="modal" data-bs-target="#lockerModal"
                                        title="Locker Purchase" data-id="{{$member->id}}">
                                        {{-- title="Locker Purchase" data-id="{{$member->id}}" data-has-locker="{{ $member->has_locker ? 1 : 0 }}"> --}}
                                        <small>
                                            <i class="fa-solid fa-table-cells-row-lock"></i>
                                        </small>
                                    </button>
                                    <button class="border-0 bg-light p-1 rounded-3 lh-1 action-btn addOnBtn" data-bs-toggle="modal" data-bs-target="#addAddonModal"
                                        title="Add On" data-id="{{$member->id}}">
                                        <small>
                                            <i class="fa-solid fa-puzzle-piece"></i>
                                        </small>
                                    </button>
                                    <button class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"
                                        data-bs-toggle="modal" data-bs-target="#planrenewal"
                                        title="Plan Renewal" data-id="{{$member->id}}"><small><i
                                                class="fa-solid fa-rotate-right"></i></small></button>
                                    <button class="border-0 bg-light p-1 rounded-3 lh-1 action-btn memberEditBtn" title="Edit" data-id="{{$member->id}}">
                                        <small>
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </small>
                                    </button>
                                    <button class="border-0 bg-light p-1 rounded-3 lh-1 action-btn delete-row memberDeleteBtn"
                                        title="Delete" data-id="{{$member->id}}">
                                        <small>
                                            <i class="fa-solid fa-trash"></i>
                                        </small>
                                    </button>

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
    <!-- add Club Member Modal start -->
    <div class="modal fade" id="addclubmember" tabindex="-1" aria-labelledby="addclubmemberModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h2 class="modal-title fs-5 fw-semibold" id="addclubmemberModalLabel">Add New Member</h2>
                    <button type="button" class="btn-close bg-transparent fs-5 lh-1" data-bs-dismiss="modal"
                        aria-label="Close"><i class="fa-regular fa-circle-xmark"></i></button>
                </div>
                <div class="modal-body">
                    <form id="club-member-form" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-lg-6">
                                <label for="" class="form-label fw-semibold text-dark mb-3"><span
                                        class="text-info rounded-3 label-icon p-1 d-inline-flex align-items-center justify-content-center me-2"><i
                                            class="fa-regular fa-user"></i></span> Personal Details</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Name</small></label>
                                            <input type="text" class="form-control py-2 shadow-none text-only" id="" name="name"
                                                placeholder="Full Name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Email</small></label>
                                            <input type="email" class="form-control py-2 shadow-none" id="" name="email"
                                                placeholder="Email" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Phone</small></label>
                                            <input type="tel" class="form-control py-2 shadow-none phone-input" id="phone" name="phone"
                                                placeholder="Phone" required>
                                            <span class="error-div text-danger"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Blood Group</small></label>
                                            <select id="" class="form-select py-2 shadow-none" name="blood_grp" required>
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
                                            <textarea class="form-control py-2 shadow-none" id="address" name="address" rows="3"
                                                placeholder="Address" required></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Photo</small></label>
                                            <label class="file-upload-box position-relative text-center border rounded-3 w-100 p-2">
                                                <input type="file" class="file-input opacity-0 position-absolute start-0 w-100 profile-image" name="image" accept=".jpg,.jpeg,.png" required>
                                                <div class="upload-content">
                                                    <i class="upload-icon"><i
                                                            class="fa-solid fa-arrow-up-from-bracket"></i></i>
                                                    <p class="upload-text mb-0">
                                                        Passport size Image
                                                    </p>
                                                    <small class="text-muted">
                                                        JPG, JPEG & PNG, max file size 5MB
                                                    </small>
                                                </div>
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
                                            <input type="text" class="form-control py-2 shadow-none text-only" id="" name="spouse_name"
                                                placeholder="Spouse Full Name">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Email</small></label>
                                            <input type="email" class="form-control py-2 shadow-none" id="" name="spouse_email"
                                                placeholder="Email">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Phone</small></label>
                                            <input type="tel" class="form-control py-2 shadow-none phone-input" id="spouse_phone" name="spouse_phone"
                                                placeholder="Phone">
                                            <span class="error-div text-danger"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Blood Group</small></label>
                                            <select id="" class="form-select py-2 shadow-none" name="spouse_blood_grp">
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
                                            <textarea class="form-control py-2 shadow-none" id="spouse-address" name="spouse_address" rows="3"
                                                placeholder="Address"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Photo</small></label>
                                            <label class="file-upload-box position-relative text-center border rounded-3 w-100 p-2">
                                                <input type="file" class="file-input opacity-0 position-absolute start-0 w-100 profile-image" name="spouse_image" accept=".jpg,.jpeg,.png">
                                                <div class="upload-content">
                                                    <i class="upload-icon"><i
                                                            class="fa-solid fa-arrow-up-from-bracket"></i></i>
                                                    <p class="upload-text mb-0">
                                                        Passport size Image
                                                    </p>
                                                    <small class="text-muted">
                                                        JPG, JPEG & PNG, max file size 5MB
                                                    </small>
                                                </div>
                                            </label>
                                            <span class="error-div text-danger"></span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="col-lg-12">
                                <label for="" class="form-label fw-semibold text-dark my-3"><span
                                        class="text-info rounded-3 label-icon p-1 d-inline-flex align-items-center justify-content-center me-2"><i
                                            class="fa-regular fa-regular fa-credit-card"></i></span> Card
                                    Details</label>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100"><small>Plan type</small></label>

                                            @foreach ($clubMembershipPlanTypeList as $type)
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input plan-type" type="radio" name="membership_plan_type_id"
                                                    id="membership_plan_type_{{ $type->id }}" value="{{ $type->id }}"
                                                    {{ $loop->first ? 'required' : '' }}>
                                                <label class="form-check-label"
                                                    for="membership_plan_type_{{ $type->id }}"><small>{{ $type->name }}</small></label>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-4">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Card No.</small></label>
                                            <select name="card_id" id="" class="form-select py-2 shadow-none" required>
                                                <option  value="" selected="" hidden="" disabled="">Card No.</option>
                                                @foreach ($cards as $card)
                                                    <option value="{{$card->id}}">{{$card->card_no}}</option>
                                                @endforeach

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-4">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Payment Mode</small></label>
                                            <input type="text" class="form-control py-2 shadow-none" id="" name="payment_mode"
                                                placeholder="Payment Mode" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-4">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100"><small>A/C Head</small></label>
                                            <input type="text" class="form-control py-2 shadow-none" id="" name="ac_head"
                                                placeholder="A/C Head" required>
                                        </div>
                                    </div>
                                    <!-- <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100"><small>Card No.</small></label>
                                            <input type="text" class="form-control py-2 shadow-none" id="" name="card_id"
                                                placeholder="Card No." required>
                                        </div>
                                    </div> -->
                                    <div class="col-xl-3 col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100"><small>Taxable Amt.</small></label>
                                            <input type="number" class="form-control py-2 shadow-none" id="taxable_amount" name="taxable_amount"
                                                placeholder="Taxable Amt.">
                                            <span class="error-div text-danger"></span>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100"><small>GST%</small></label>
                                            <input type="number" class="form-control py-2 shadow-none" id="gstPercentage" name="gstPercentage"
                                                placeholder="GST%" value="{{ $gstPercentage }}">
                                            <span class="error-div text-danger"></span>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100"><small>GST Amt</small></label>
                                            <input type="text" class="form-control py-2 shadow-none" id="gstAmt" name="gst_amt"
                                                placeholder="GST Amt" readonly>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100"><small>Receipt Amt</small></label>
                                            <input type="text" class="form-control py-2 shadow-none" id="receiptAmt" name="receipt_amt"
                                                placeholder="Receipt Amt" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Bank Name</small></label>
                                            <select name="bank_id" id="" class="form-select py-2 shadow-none" required>
                                                <option value="">Bank Name</option>
                                                @foreach ($bankList as $bank)
                                                    <option value="{{$bank->id}}">{{$bank->name}}</option>
                                                @endforeach

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100"><small>Remarks</small></label>
                                            <input type="text" class="form-control py-2 shadow-none" id="" name="remarks"
                                                placeholder="Remarks" required>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="text-end mod-footer mt-3">
                            <button type="button" class="btn btn-info fw-semibold"
                                data-bs-dismiss="modal">Cancel</button>
                            <input type="submit" class="btn btn-primary fw-semibold" value="Add member" id="submit">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- add Club Member Modal end  -->

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
                        @csrf
                        <input type="hidden" name="member_id" value="" id="club_member_id">
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
                                                placeholder="Full Name" required>
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
                                                placeholder="Phone" required>
                                            <span class="error-div text-danger"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Blood Group</small></label>
                                            <select id="club_member_blood_grp" class="form-select py-2 shadow-none" name="blood_grp" required>
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
                                                placeholder="Address" required></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Photo</small></label>
                                            <label class="file-upload-box position-relative text-center border rounded-3 w-100 p-2">
                                                <input type="file" class="file-input opacity-0 position-absolute start-0 w-100 profile-image" name="image" accept=".jpg,.jpeg,.png" id="club_member_image">
                                                <div class="upload-content">
                                                    <i class="upload-icon"><i
                                                            class="fa-solid fa-arrow-up-from-bracket"></i></i>
                                                    <p class="upload-text mb-0">
                                                        Passport size Image
                                                    </p>
                                                    <small class="text-muted">
                                                        JPG, JPEG & PNG, max file size 5MB
                                                    </small>
                                                </div>
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
                                                placeholder="Spouse Full Name">
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
                                                placeholder="Phone">
                                            <span class="error-div text-danger"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Blood Group</small></label>
                                            <select id="spouse_blood_grp" class="form-select py-2 shadow-none" name="spouse_blood_grp">
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
                                                placeholder="Address"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Photo</small></label>
                                            <label class="file-upload-box position-relative text-center border rounded-3 w-100 p-2">
                                                <input type="file" class="file-input opacity-0 position-absolute start-0 w-100 profile-image" name="spouse_image" id="spouse_image" accept=".jpg,.jpeg,.png">
                                                <div class="upload-content">
                                                    <i class="upload-icon"><i
                                                            class="fa-solid fa-arrow-up-from-bracket"></i></i>
                                                    <p class="upload-text mb-0">
                                                        Passport size Image
                                                    </p>
                                                    <small class="text-muted">
                                                        JPG, JPEG & PNG, max file size 5MB
                                                    </small>
                                                </div>
                                            </label>
                                            <span class="error-div text-danger"></span>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Status</small></label>
                                            <select name="club_status" id="club_status" class="form-select py-2 shadow-none">
                                                <option value="" hidden disabled selected>Status</option>
                                                <option value="active">Active</option>
                                                <option value="pending">Pending</option>
                                                <option value="rejected">Rejected</option>
                                            </select>
                                        </div>
                            </div>

                            <div class="col-lg-12">
                                <label for="" class="form-label fw-semibold text-dark my-3"><span
                                        class="text-info rounded-3 label-icon p-1 d-inline-flex align-items-center justify-content-center me-2"><i
                                            class="fa-regular fa-regular fa-credit-card"></i></span> Card
                                    Details</label>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100"><small>Plan type</small></label>

                                            <p id="current_membership"></p>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-4">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Update Card No.</small></label>
                                            <select name="card_id" id="card_no" class="form-select py-2 shadow-none" >
                                                <option value="" selected="" hidden="" disabled="">Card No.</option>
                                                @foreach ($cards as $card)
                                                    <option value="{{$card->id}}">{{$card->card_no}}</option>
                                                @endforeach

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Current Card No.</small></label>
                                            <p id="current_card_no"></p>
                                        </div>
                                    </div>
                                    <!-- <div class="col-md-6 col-xl-4">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Payment Mode</small></label>
                                            <input type="text" class="form-control py-2 shadow-none" id="" name="payment_mode"
                                                placeholder="Payment Mode" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-4">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100"><small>A/C Head</small></label>
                                            <input type="text" class="form-control py-2 shadow-none" id="" name="ac_head"
                                                placeholder="A/C Head" required>
                                        </div>
                                    </div> -->
                                    <!-- <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100"><small>Card No.</small></label>
                                            <input type="text" class="form-control py-2 shadow-none" id="" name="card_id"
                                                placeholder="Card No." required>
                                        </div>
                                    </div> -->
                                    <!-- <div class="col-xl-3 col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100"><small>Taxable Amt.</small></label>
                                            <input type="text" class="form-control py-2 shadow-none" id="taxable_amount" name="taxable_amount"
                                                placeholder="Taxable Amt." readonly>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100"><small>GST%</small></label>
                                            <input type="text" class="form-control py-2 shadow-none" id="gstPercentage" name="gstPercentage"
                                                placeholder="GST%" value="{{ $gstPercentage }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100"><small>GST Amt</small></label>
                                            <input type="text" class="form-control py-2 shadow-none" id="gstAmt" name="gst_amt"
                                                placeholder="GST Amt" readonly>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100"><small>Receipt Amt</small></label>
                                            <input type="text" class="form-control py-2 shadow-none" id="receiptAmt" name="receipt_amt"
                                                placeholder="Receipt Amt" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Bank Name</small></label>
                                            <select name="bank_id" id="" class="form-select py-2 shadow-none" required>
                                                <option value="">Bank Name</option>
                                                @foreach ($bankList as $bank)
                                                    <option value="{{$bank->id}}">{{$bank->name}}</option>
                                                @endforeach

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100"><small>Remarks</small></label>
                                            <input type="text" class="form-control py-2 shadow-none" id="" name="remarks"
                                                placeholder="Remarks" required>
                                        </div>
                                    </div> -->
                                </div>

                            </div>
                        </div>
                        <div class="text-end mod-footer mt-3">
                            <button type="button" class="btn btn-info fw-semibold"
                                data-bs-dismiss="modal">Cancel</button>
                            <input type="submit" class="btn btn-primary fw-semibold" value="Update club member" id="club_edit_submit">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- edit Club Member Modal end  -->

    <!-- View Profile Modal -->
    <div class="modal fade" id="viewprofile" tabindex="-1" aria-labelledby="viewprofileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fs-5 fw-semibold" id="viewprofileModalLabel">Member Plan Details</h5>
                    <button type="button" class="btn-close bg-transparent fs-5 lh-1" data-bs-dismiss="modal"
                        aria-label="Close"><i class="fa-regular fa-circle-xmark"></i></button>
                </div>
                <div class="modal-body">
                    <table class="table border-0 membership-plan-table" cellspacing="1" cellpadding="1">
                        <tbody>
                            <tr>
                                <td class="text-secondary ps-3">
                                    <small>Member’s Name:</small>
                                </td>
                                <td class="pe-3"><small id="memberName">Soumen Das</small></td>
                            </tr>
                            <tr>
                                <td class="text-secondary ps-3">
                                    <small>Club Name:</small>
                                </td>
                                <td class="pe-3"><small id="memberClubName">Soumen Das</small></td>
                            </tr>
                            <!-- <tr>
                                <td class="text-secondary ps-3">
                                    <small>Member’s Id:</small>
                                </td>
                                <td class="pe-3"><small id="memberCode">GH231</small></td>
                            </tr> -->
                            <tr>
                                <td class="text-secondary ps-3">
                                    <small>Card No:</small>
                                </td>
                                <td class="pe-3"><small id="memberCardNo">12345abcd</small></td>
                            </tr>
                            <!-- <tr>
                                <td class="text-secondary ps-3">
                                    <small>Card No:</small>
                                </td>
                                <td class="pe-3"><small>12345abcd</small></td>
                            </tr> -->
                            <tr>
                                <td class="text-secondary ps-3">
                                    <small>Current Active Plan:</small>
                                </td>
                                <td class="pe-3"><small id="memberPlan">Gold</small></td>
                            </tr>
                            <tr>
                                <td class="text-secondary ps-3">
                                    <small>Plan Expiry Date:</small>
                                </td>
                                <td class="pe-3"><small id="memberPlanExpiry">12-31-2006</small></td>
                            </tr>
                            <tr>
                                <td class="text-secondary ps-3">
                                    <small>Current Wallet Balance:</small>
                                </td>
                                <td class="pe-3"><small id="memberWallet">₹2,450.00</small></td>
                            </tr>
                            <tr>
                                <td class="text-secondary ps-3">
                                    <small>Approved By:</small>
                                </td>
                                <td class="pe-3"><small id="memberApprovedBy">₹2,450.00</small></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Plan Renewal Modal -->
    <div class="modal fade" id="planrenewal" tabindex="-1" aria-labelledby="planrenewalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fs-5 fw-semibold" id="planrenewalModalLabel">Renewal Plan</h5>
                    <button type="button" class="btn-close bg-transparent fs-5 lh-1" data-bs-dismiss="modal"
                        aria-label="Close"><i class="fa-regular fa-circle-xmark"></i></button>
                </div>
                <div class="modal-body">
                    <form action="">
                        <div class="row">
                            <div class="col-12">
                                <label for="" class="form-label fw-semibold text-dark mb-3"><span
                                        class="text-info rounded-3 label-icon p-1 d-inline-flex align-items-center justify-content-center me-2"><i
                                            class="fa-regular fa-user"></i></span>Personal Details</label>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100"><small>Renewal type</small></label>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="renewinlineRadioOptions"
                                                    id="renewinlineRadio1" value="option1">
                                                <label class="form-check-label"
                                                    for="renewinlineRadio1"><small>Annual</small></label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="renewinlineRadioOptions"
                                                    id="renewinlineRadio2" value="option2">
                                                <label class="form-check-label"
                                                    for="renewinlineRadio2"><small>Lifetime</small></label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="renewinlineRadioOptions"
                                                    id="renewinlineRadio3" value="option3">
                                                <label class="form-check-label"
                                                    for="renewinlineRadio3"><small>Silver</small></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-part mb-3">
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="Mode">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-part mb-3">
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="A/C Head">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-part mb-3">
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="Fine">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-part mb-3">
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="Taxable Amt (Min 500)">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-part mb-3">
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="GST%">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-part mb-3">
                                            <input type="text" class="form-control py-2 shadow-none" id="" placeholder="GST Amt">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-part mb-3">
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="Receipt Amt">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-part mb-3">
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="Bank Name">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-part mb-3">
                                            <textarea class="form-control py-2 shadow-none" id="" rows="3"
                                                placeholder="Remarks"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-end mod-footer mt-3">
                            <button type="button" class="btn btn-info fw-semibold"
                                data-bs-dismiss="modal">Cancel</button>
                            <input type="submit" class="btn btn-primary fw-semibold" value="Submit">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- wallet Modal -->
    <div class="modal fade" id="walletrecharge" tabindex="-1" aria-labelledby="walletrechargeModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fs-5 fw-semibold" id="walletrechargeModalLabel">Wallet Recharge</h5>
                    <button type="button" class="btn-close bg-transparent fs-5 lh-1" data-bs-dismiss="modal"
                        aria-label="Close"><i class="fa-regular fa-circle-xmark"></i></button>
                </div>
                <div class="modal-body">
                    <div class="card current-balance border-0 p-1 mb-4">
                        <div class="card-body">
                            <p class="card-text fw-semibold mb-2 text-white">Current Balance</p>
                            <h5 class="card-title fs-4 fw-semibold text-white mb-0" id="walletBalance"></h5>
                        </div>
                    </div>
                    <form action="" id="walletRechargeForm" method="post">
                        @csrf
                        <input type="hidden" name="wallet_member_id" id="walletMemberId" value="">
                        <div class="row">
                            <div class="col-12">
                                <div class="input-group mb-3">
                                    <label for="" class="form-label w-100 mb-1 w-100"><small>Balance</small></label>
                                    <span
                                        class="input-group-text bg-transparent border-end-0 bg-white"><small>₹</small></span>
                                    <input id="amountInput" type="number" class="form-control border-start-0 shadow-none"
                                        aria-label="Amount" name="wallet_recharge_amount" placeholder="0" min="500" step="1" required>

                                </div>
                                <span class="error-div text-danger" id="amountErrorDiv"></span>
                            </div>
                            <div class="col-12">
                                <label for="" class="form-label fw-semibold text-dark mb-3">Quick Select</label>
                                <div class="row">
                                    <div class="col-xl-3 col-md-6">
                                        <div class="form-check position-relative p-0 custom-radio mb-3">
                                            <input
                                                class="form-check-input m-0 w-100 h-100 position-absolute top-0 start-0 rounded-0 opacity-0"
                                                type="radio" name="inlineRadioOptions" id="inlineRadio1"
                                                value="option1">
                                            <label class="form-check-label w-100 border p-2 rounded-3"
                                                for="inlineRadio1"><small>₹ 500</small></label>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <div class="form-check position-relative p-0 custom-radio mb-3">
                                            <input
                                                class="form-check-input m-0 w-100 h-100 position-absolute top-0 start-0 rounded-0 opacity-0"
                                                type="radio" name="inlineRadioOptions" id="inlineRadio2"
                                                value="option1">
                                            <label class="form-check-label w-100 border p-2 rounded-3"
                                                for="inlineRadio2"><small>₹ 1000</small></label>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <div class="form-check position-relative p-0 custom-radio mb-3">
                                            <input
                                                class="form-check-input m-0 w-100 h-100 position-absolute top-0 start-0 rounded-0 opacity-0"
                                                type="radio" name="inlineRadioOptions" id="inlineRadio3"
                                                value="option1">
                                            <label class="form-check-label w-100 border p-2 rounded-3"
                                                for="inlineRadio3"><small>₹ 2000</small></label>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <div class="form-check position-relative p-0 custom-radio mb-3">
                                            <input
                                                class="form-check-input m-0 w-100 h-100 position-absolute top-0 start-0 rounded-0 opacity-0"
                                                type="radio" name="inlineRadioOptions" id="inlineRadio4"
                                                value="option1">
                                            <label class="form-check-label w-100 border p-2 rounded-3"
                                                for="inlineRadio4"><small>₹ 5000</small></label>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="col-md-6 col-xl-6">
                                <div class="form-part mb-3">
                                    <label for="" class="form-label w-100 mb-1 w-100"><small>Payment Mode</small></label>
                                    <input type="text" class="form-control py-2 shadow-none" id="" name="wallet_payment_mode"
                                        placeholder="Payment Mode" required>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-6">
                                <div class="form-part mb-3">
                                    <label for="" class="form-label w-100 mb-1 w-100"><small>A/C Head</small></label>
                                    <input type="text" class="form-control py-2 shadow-none" id="" name="wallet_ac_head"
                                        placeholder="A/C Head" required>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-6">
                                <div class="form-part mb-3">
                                    <label for="" class="form-label w-100 mb-1 w-100"><small>Bank Name</small></label>
                                    <select name="wallet_bank_id" id="" class="form-select py-2 shadow-none" required>
                                        <option value="">Bank Name</option>
                                        @foreach ($bankList as $bank)
                                            <option value="{{$bank->id}}">{{$bank->name}}</option>
                                        @endforeach

                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-6">
                                <div class="form-part mb-3">
                                    <label for="" class="form-label w-100 mb-1 w-100"><small>Remarks</small></label>
                                    <input type="text" class="form-control py-2 shadow-none" id="" name="wallet_remarks"
                                        placeholder="Remarks" required>
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                            <button class="btn btn-primary fw-semibold" id="rechargeSubmitBtn">Recharge Wallet</button>
                        </div>
                    </form>
                    <div class="d-flex justify-content-between align-items-center gap-3 my-4">
                        <div class="form-label fw-semibold text-dark mb-3"><span
                                class="text-info rounded-3 label-icon p-1 d-inline-flex align-items-center justify-content-center me-2"><i
                                    class="fa-solid fa-wallet"></i></span> Recent Transactions</div>
                        <span><a class="text-dark"><i class="fa-regular fa-calendar fs-4"></i></a></span>
                    </div>
                    <div class="bg-light p-2">
                        {{-- <table class="table border-0 m-0 wallet-table" id="walletTransactionTbody"> --}}
                        <table class="table border-0 m-0 wallet-table">
                            <tbody id="walletTransactionTbody">
                                {{-- <tr>
                                    <td class="border-secondary bg-transparent align-middle lh-sm">
                                        <small class="fw-semibold">Added</small> <br>
                                        <small class="text-black-50">Today, 2:30 PM</small>
                                    </td>
                                    <td class="text-success text-end border-secondary bg-transparent align-middle">
                                        +₹4000</td>
                                </tr>
                                <tr>
                                    <td class="border-secondary bg-transparent align-middle lh-sm">
                                        <small class="fw-semibold">Added</small> <br>
                                        <small class="text-black-50">Today, 2:30 PM</small>
                                    </td>
                                    <td class="text-info text-end border-secondary bg-transparent align-middle">
                                        +₹4000</td>
                                </tr>
                                <tr>
                                    <td class="border-secondary bg-transparent align-items-center align-middle lh-sm">
                                        <small class="fw-semibold">Used</small> <br>
                                        <small class="text-black-50">Today, 2:30 PM</small>
                                    </td>
                                    <td class="text-danger text-end border-secondary bg-transparent align-middle">+₹4000
                                    </td>
                                </tr> --}}
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Membership plan Modal -->
    <div class="modal fade" id="membershipplan" tabindex="-1" aria-labelledby="membershipplanModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fs-5 fw-semibold" id="membershipplanModalLabel">Membership Plan History</h5>
                    <button type="button" class="btn-close bg-transparent fs-5 lh-1" data-bs-dismiss="modal"
                        aria-label="Close"><i class="fa-regular fa-circle-xmark"></i></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table border-0 membership-plan-table">
                            <tbody id="membershipPlanTbody">
                                <!-- <tr class="active-member">
                                    <td class="bg-info align-middle p-3 text-nowrap">
                                        <small class="fw-semibold">From Date</small> <br>
                                        <small class="text-black-50">01.01.2026</small>
                                    </td>
                                    <td class="bg-info align-middle p-3 text-nowrap">
                                        <small class="fw-semibold">To Date</small> <br>
                                        <small class="text-black-50">01.01.2026</small>
                                    </td>
                                    <td class="bg-info align-middle p-3 text-nowrap">
                                        <small class="fw-semibold">Plan Type</small> <br>
                                        <small class="text-black-50">Annual</small>
                                    </td>
                                    <td class="bg-info align-middle p-3 text-nowrap">
                                        <small class="fw-semibold">Fine</small> <br>
                                        <small class="text-black-50">Rs 00.00</small>
                                    </td>
                                    <td class="bg-info align-middle p-3 text-nowrap">
                                        <small class="fw-semibold">Price</small> <br>
                                        <small class="text-black-50">Rs 00.00</small>
                                    </td>
                                    <td class="bg-info align-middle text-end p-3">
                                        <img src="{{ asset('assets/images/active-tag.svg') }}" alt="" width="76" height="67"
                                            style="min-width: 76px;">
                                    </td>
                                </tr>
                                <tr class="expire-member">
                                    <td class="bg-info align-middle p-3 text-nowrap">
                                        <small class="fw-semibold">From Date</small> <br>
                                        <small class="text-black-50">01.01.2026</small>
                                    </td>
                                    <td class="bg-info align-middle p-3 text-nowrap">
                                        <small class="fw-semibold">To Date</small> <br>
                                        <small class="text-black-50">01.01.2026</small>
                                    </td>
                                    <td class="bg-info align-middle p-3 text-nowrap">
                                        <small class="fw-semibold">Plan Type</small> <br>
                                        <small class="text-black-50">Annual</small>
                                    </td>
                                    <td class="bg-info align-middle p-3 text-nowrap">
                                        <small class="fw-semibold">Fine</small> <br>
                                        <small class="text-black-50">Rs 00.00</small>
                                    </td>
                                    <td class="bg-info align-middle p-3 text-nowrap">
                                        <small class="fw-semibold">Price</small> <br>
                                        <small class="text-black-50">Rs 00.00</small>
                                    </td>
                                    <td class="bg-info align-middle p-3 text-nowrap">
                                        <img src="{{ asset('assets/images/expire-tag.svg') }}" alt="" class="position-absolute end-0"
                                            style="top: 10px;" width="73" height="24" style="min-width: 73px;">
                                    </td>
                                </tr> -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn" data-id="">
                        Yes, Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Wallet Recharge Confirmation Modal -->
    <div class="modal fade" id="confirmRechargeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-3">

                <div class="modal-header border-0">
                    <h5 class="modal-title">Confirm Wallet Recharge</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    Are you sure you want to recharge this wallet?
                </div>

                <div class="modal-footer border-0">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button class="btn btn-success" id="confirmRechargeBtn">
                        Yes, Recharge
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- Add Add-On Modal -->
    <div class="modal fade" id="addAddonModal" tabindex="-1"
        aria-labelledby="addAddonModalLabel" aria-hidden="true">

        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <!-- Header -->
                <div class="modal-header border-0">
                    <h5 class="modal-title fs-5 fw-semibold" id="addAddonModalLabel">
                        Add Add-On
                    </h5>

                    <button type="button"
                        class="btn-close bg-transparent fs-5 lh-1"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                        <i class="fa-regular fa-circle-xmark"></i>
                    </button>
                </div>

                <!-- Body -->
                <div class="modal-body">
                    <form>
                        <input type="hidden" id="addOnMemberId" value="">
                        <input type="hidden" id="existingAddonIds" value="">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-part mb-3">
                                    <label class="form-label w-100"><small>Please Select Add-Ons</small></label>

                                    @foreach ($addonList as $addon)
                                        <div class="form-check mb-2">
                                            <input class="form-check-input addon-checkbox" type="checkbox" value="{{ $addon->id }}" data-price="{{ $addon->price }}" id="addon{{ $addon->id }}">

                                            <label class="form-check-label" for="addon{{ $addon->id }}">
                                                <small>{{ $addon->name }} (₹{{ $addon->price }})</small>
                                                <div class="addon-date small text-muted d-inline ms-1 d-none"
                                                    id="addonDate{{ $addon->id }}">
                                                    | Start: <span class="start-date"></span>,
                                                    End: <span class="end-date"></span>
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach

                                </div>
                            </div>

                        </div>
                        <!-- Bottom Payment Bar -->
                        <div class="border-top pt-3 mt-3 d-flex justify-content-between align-items-center">

                            <div class="text-end mt-3 d-none" id="addonTotalWrapper">
                                <small>Total Amount to Pay</small>
                                <h5 class="fw-semibold mb-0">
                                    ₹ <span id="addonTotalAmount">0</span>
                                </h5>
                            </div>

                            <button class="btn btn-primary fw-semibold px-4"
                                id="purchaseAddonBtn">
                                Purchase
                            </button>

                        </div>
                        {{-- <div class="text-end">
                            <button class="btn btn-primary fw-semibold" id="purchaseAddonBtn">Purchase</button>
                        </div> --}}
                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- Add Locker Purchase Modal -->
    <div class="modal fade" id="lockerModal" tabindex="-1">
        <div class="modal-dialog modal-md">
            <div class="modal-content">

                <!-- Header -->
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-semibold">Locker Purchase</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                        <i class="fa-regular fa-circle-xmark"></i>
                    </button>
                </div>

                <!-- Body -->
                <div class="modal-body">
                    <form id="lockerForm">
                        <input type="hidden" id="lockerMemberId">

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-part mb-3">
                                    <label class="form-label">
                                        <small>Select Locker</small>
                                    </label>

                                    <select class="form-select shadow-none" id="lockerSelect" required>
                                        <option value="">Select Locker</option>
                                        @foreach($lockers as $locker)
                                            <option value="{{ $locker->id }}" data-price="{{ $lockerPrice->price ?? 0 }}">
                                                Locker {{ $locker->locker_number }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="d-none" id="lockerAllocationInfo">
                                    <small class="text-muted">Allocated Duration</small>
                                    <div class="fw-semibold">
                                        <span id="lockerAllocationDates">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PRICE SECTION HERE -->
                        <div class="text-end mt-3" id="lockerPriceWrapper">
                            <small>Amount to Pay</small>
                            <h5 class="fw-semibold mb-0">
                                ₹ <span id="lockerPrice">0</span>
                            </h5>
                        </div>

                        <!-- Bottom -->
                        <div class="border-top pt-3 mt-3 d-flex justify-content-end">
                            <button class="btn btn-primary fw-semibold px-4" id="purchaseLockerBtn">
                                Purchase
                            </button>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('customJS')
<script>
    $(document).ready(function() {

        const params = new URLSearchParams(window.location.search);
        const type = params.get('type');

        // Check if 'type' parameter is 'addMember'
        if (type === 'addMember') {
            $('#addClubMemberBtn').trigger('click');
        }

        $(document).on('click', '.lockerBtn', function() {
            let memberId = $(this).data('id');

            $('#lockerMemberId').val(memberId);
            $('#lockerSelect option[data-assigned="1"]').remove();
            $('#lockerSelect').val('').prop('disabled', false);
            $('#lockerPrice').text(0);
            $('#lockerAllocationInfo').addClass('d-none');
            $('#lockerAllocationDates').text('-');

            // $('#lockerModal').data('has-locker', false);

            $('#lockerPriceWrapper').addClass('d-none');
            $('#purchaseLockerBtn').addClass('d-none');

            $.ajax({
                url: '{{ route("club-member.locker-allocation", ":memberId") }}'.replace(':memberId', memberId),
                type: 'GET',
                success: function(response){
                    if (response.statusCode == 200 && response.data) {
                        let allocation = response.data;
                        let lockerId = allocation.locker_id;
                        let lockerNumber = allocation.locker?.locker_number ?? '';
                        let isExpired = allocation.is_expired == 1;

                        let $select = $('#lockerSelect');
                        if ($select.find(`option[value="${lockerId}"]`).length === 0) {
                            $select.append(`<option value="${lockerId}" data-assigned="1" data-price="600.00">Locker ${lockerNumber}</option>`);
                        }

                        $select.val(lockerId).prop('disabled', !isExpired);

                        let startDate = allocation.start_date
                            ? new Date(allocation.start_date).toLocaleDateString('en-IN', { timeZone: 'Asia/Kolkata' })
                            : '-';
                        let endDate = allocation.end_date
                            ? new Date(allocation.end_date).toLocaleDateString('en-IN', { timeZone: 'Asia/Kolkata' })
                            : 'No Expiry';

                        if (isExpired) {
                            $('#lockerAllocationDates').html(`${startDate} - ${endDate} <span class="text-danger">(Expired)</span>`);
                        } else {
                            $('#lockerAllocationDates').text(`${startDate} - ${endDate}`);
                        }
                        $('#lockerAllocationInfo').removeClass('d-none');

                        if (isExpired) {
                            $('#lockerModal').data('has-locker', false);
                            let lockerPrice = $select.find(':selected').data('price') || 0;
                            $('#lockerPrice').text(lockerPrice);
                            $('#lockerPriceWrapper').removeClass('d-none');
                            $('#purchaseLockerBtn').removeClass('d-none');
                        } else {
                            $('#lockerPriceWrapper').addClass('d-none');
                            $('#purchaseLockerBtn').addClass('d-none');
                            $('#lockerModal').data('has-locker', true);
                        }
                    } else {
                        $('#lockerModal').data('has-locker', false);
                    }
                },
                error: function(){
                    toastr.error('Something Went Wrong.');
                }
            });
        });

        $(document).on('change', '#lockerSelect', function() {
            let hasLocker = $('#lockerModal').data('has-locker') == 1;
            let lockerPrice = $(this).find(':selected').data('price') || 0;
            let hasSelection = $(this).val() !== '';

            if (!hasLocker && hasSelection) {
                $('#lockerPrice').text(lockerPrice);
                $('#lockerPriceWrapper').removeClass('d-none');
                $('#purchaseLockerBtn').removeClass('d-none');
            } else {
                $('#lockerPrice').text(0);
                $('#lockerPriceWrapper').addClass('d-none');
                $('#purchaseLockerBtn').addClass('d-none');
            }
        });

        $(document).on('click', '#purchaseLockerBtn', function(e) {
            e.preventDefault();

            let memberId = $('#lockerMemberId').val();
            let lockerId = $('#lockerSelect').val();

            if (!lockerId) {
                toastr.error('Please select a locker');
                return;
            }

            let btn = $(this);
            btn.prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm me-2"></span> Processing...');

            $.ajax({
                url: "{{ route('club-member.locker.purchase') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    member_id: memberId,
                    locker_id: lockerId
                },
                success: function(response) {
                    // console.log(response)
                    if (response.statusCode == 200) {
                        toastr.success(response.message);
                        $('#lockerModal').modal('hide');

                        setTimeout(() => {
                            window.location.href = "{{ route('club-member.list') }}";
                        }, 1200);
                    } else {
                        toastr.error(response.message ?? "Something went wrong");
                    }
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON?.message || xhr.responseJSON?.error || "Something went wrong";
                    toastr.error(msg);
                },
                complete: function() {
                    btn.prop('disabled', false).html('Purchase');
                }
            });
        });



        //name validation
        $('.text-only').on('input', function () {
            this.value = this.value.replace(/[^A-Za-z\s]/g, '');
        });

        //phone no validation
        $('.phone-input').on('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 10);
        });

        function calculateGST() {
            // Get values from inputs
            let taxable = parseFloat($('#taxable_amount').val()) || 0;
            let gstPercent = parseFloat($('#gstPercentage').val()) || 0;

            // Calculate GST Amount
            let gstAmount = (taxable * gstPercent) / 100;

            // Calculate Receipt Amount (Taxable + GST)
            let receiptAmount = taxable + gstAmount;

            // Update the readonly inputs
            $('#gstAmt').val(gstAmount.toFixed(2));
            $('#receiptAmt').val(receiptAmount.toFixed(2));
        }

        // Bind calculation on keyup/change for Taxable Amt and GST%
        $('#taxable_amount, #gstPercentage').on('keyup change', calculateGST);

        $('.plan-type').on('change', function () {

            let planTypeId = $(this).val();
            let gstPercentage = $('#gstPercentage').val();

            $.ajax({
                url: "{{ route('club-member-plan-price') }}",
                type: "GET",
                data: {
                    planTypeId: planTypeId
                },
                success: function (response) {
                    if (response.statusCode == 200) {
                        $('#taxable_amount').val(response.data.plan.price);
                        let price       = parseFloat(response.data.plan.price);
                        let gstAmt      = ((price * gstPercentage)/100).toFixed(2);
                        let receiptAmt  = (price + parseFloat(gstAmt)).toFixed(2);

                        $('#gstAmt').val(gstAmt);
                        $('#receiptAmt').val(receiptAmt);

                    } else {
                        if(response.message){
                            toastr.error(response.message);
                        }
                        else{
                            toastr.error("Something went wrong, Please try again.");
                        }
                    }
                },
                error: function(xhr, status, error) {
                    // Handle errors
                    toastr.error("Something went wrong, Please try again.");
                    console.error(xhr.responseText);
                }
            });

        });

        $('#club-member-form').on('submit', function (e) {
            e.preventDefault();
            const $btn = $('#submit');
            const originalText = $btn.val();

            let isValid = true;
            $('.phone-input').each(function () {

                let phone = $(this).val();
                let errorDiv = $(this).next('.error-div');

                if (phone !== '' && !/^\d{10}$/.test(phone)) {
                    errorDiv.text('Phone number must be 10 digits.');
                    $(this).addClass('is-invalid');
                    isValid = false;

                } else {
                    $(this).removeClass('is-invalid');
                    errorDiv.text('');
                }
            });

            $('.profile-image').each(function () {

                let fileInput = this;
                let errorDiv = $(this).closest('.form-part').find('.error-div');

                if (fileInput.files.length > 0) {

                    let file = fileInput.files[0];
                    let allowedTypes = ['image/jpeg', 'image/png'];
                    let maxSize = 5 * 1024 * 1024; // 5MB

                    let errors = [];

                    if (!allowedTypes.includes(file.type)) {
                        errors.push('Only JPG, JPEG and PNG images are allowed.');
                    }

                    if (file.size > maxSize) {
                        errors.push('Image must be less than 5MB.');
                    }

                    if (errors.length > 0) {
                        isValid = false;
                        errorDiv.html(errors.join('<br>'));
                        $(this).addClass('is-invalid');
                    } else {
                        errorDiv.text('');
                        $(this).removeClass('is-invalid');
                    }
                } else {
                    // If optional field → clear error
                    errorDiv.text('');
                    $(this).removeClass('is-invalid');
                }

            });

            let taxableAmt = $('#taxable_amount').val();
            let errorDiv = $(this).next('.error-div');

            if (taxableAmt === '' || isNaN(taxableAmt) || parseFloat(taxableAmt) <= 0) {
                errorDiv.text('Please enter a valid taxable amount.');
                $('#taxable_amount').addClass('is-invalid');
                isValid = false;
            } else {
                errorDiv.text('');
                $('#taxable_amount').removeClass('is-invalid');
            }

            let gstPercentage = $('#gstPercentage').val();

            if (gstPercentage === '' || isNaN(gstPercentage) || parseFloat(gstPercentage) <= 0) {
                errorDiv.text('Please enter a valid gst percentage.');
                $('#gstPercentage').addClass('is-invalid');
                isValid = false;
            } else {
                errorDiv.text('');
                $('#gstPercentage').removeClass('is-invalid');
            }



            if (!isValid) {
                return isValid;
            }

            $btn.prop('disabled', true);
            $btn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...');

            let clubMemberformData = new FormData($("#club-member-form")[0]);
            $.ajax({
                url: "{{ route('club-member.store') }}",
                type: "POST",
                // "_token": "{{ csrf_token() }}",
                data: clubMemberformData,
                processData: false,
                contentType: false,
                success: function(response) {
                    // alert(response)

                    if (response.statusCode == 200) {
                        $btn.html(originalText);
                        toastr.success(response.message ?? "Member created successfully.");

                        // Reset form
                        $('#club-member-form')[0].reset();

                        // Optional: reload after 1.5s
                        setTimeout(function() {
                            // location.reload();
                            window.location.href = "{{ route('club-member.list') }}";
                        }, 1500);

                    } else {
                        $btn.html(originalText);
                        $btn.prop('disabled', false);
                        if (response.message) {
                            toastr.error(response.message);
                        } else {
                            toastr.error("Something went wrong, Please try again.");
                            // console.log(response)
                        }
                    }

                },
                error: function(xhr, status, error) {
                    // if (xhr.responseJSON && xhr.responseJSON.message) {
                    //     toastr.error(xhr.responseJSON.message);
                    // } else {
                    $btn.html(originalText);
                    $btn.prop('disabled', false);
                    toastr.error("Something went wrong, Please try again.");
                    // }
                    console.error(xhr.responseText);
                }
            });

        });

        $(document).on('click', '.viewProfileBtn', function() {
            let memberId = $(this).data('id');
            let originalBtn = $(this).prop('outerHTML'); // save original button
            $(this).replaceWith('<span class="spinner-border spinner-border-sm text-primary"></span>');

            $.ajax({
                url: '{{route("club-member.view", ":memberId")}}'.replace(':memberId', memberId),
                type: 'GET',
                success: function(response){
                    if (response.statusCode == 200) {
                        // console.log(response);
                        $('#memberName').text(response.data.name);
                        $('#memberClubName').text(response.data.club_details.name)
                        // $('#memberCode').text(response.data.member_code)
                        $('#memberCardNo').text(response.data.card_details?.card_no || '-')
                        const purchase = response.data.purchase_history?.[0];

                        $('#memberApprovedBy').text(response.data.latest_approval?.checker?.name ?? '-');

                        $('#memberPlan').text(
                            purchase?.status === 'active'
                                ? purchase?.membership_plan_type?.name ?? 'No Active Plan'
                                : 'No Active Plan'
                        );
                        // $('#memberPlan').text(response.data.purchase_history[0].membership_plan_type.name)
                        let formatted = 'NA';
                        if (purchase?.status === 'active' && purchase?.expiry_date) {
                            formatted = new Date(purchase.expiry_date).toLocaleDateString('en-IN');
                        }
                        $('#memberPlanExpiry').text(formatted);
                        $('#memberWallet').text('₹ ' + (response.data.wallet_details?.current_balance??0));

                        $('.spinner-border').replaceWith(originalBtn);
                        $('#viewprofile').modal('show');
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

        $(document).on('click', '.membershipPlanBtn', function() {
            let memberId = $(this).data('id');
            let originalBtn = $(this).prop('outerHTML'); // save original button
            $(this).replaceWith('<span class="spinner-border spinner-border-sm text-primary"></span>');

            $.ajax({
                url: '{{route("club-member.membership-plan", ":memberId")}}'.replace(':memberId', memberId),
                type: 'GET',
                success: function(response){
                    if (response.statusCode == 200) {
                        let tbody = $('#membershipPlanTbody');
                        tbody.empty();

                        response.data.forEach(function(plan) {
                            let fromDate = new Date(plan.start_date).toLocaleDateString('en-IN', { timeZone: 'Asia/Kolkata' });
                            let toDate = new Date(plan.expiry_date).toLocaleDateString('en-IN', { timeZone: 'Asia/Kolkata' });
                            let today = new Date();
                            let expiryDate = new Date(plan.expiry_date);
                            let isActive = expiryDate >= today;
                            if (plan.status == 'cancelled') {
                                isActive = 0;
                            }

                            let row = `
                                <tr>
                                    <td class="bg-info align-middle p-3 text-nowrap">
                                        <small class="fw-semibold">From Date</small> <br>
                                        <small class="text-black-50">${fromDate}</small>
                                    </td>
                                    <td class="bg-info align-middle p-3 text-nowrap">
                                        <small class="fw-semibold">To Date</small> <br>
                                        <small class="text-black-50">${toDate}</small>
                                    </td>
                                    <td class="bg-info align-middle p-3 text-nowrap">
                                        <small class="fw-semibold">Plan Type</small> <br>
                                        <small class="text-black-50">${plan.membership_plan_type.name}</small>
                                    </td>
                                    <td class="bg-info align-middle p-3 text-nowrap">
                                        <small class="fw-semibold">Fine</small> <br>
                                        <small class="text-black-50">Rs ${plan.fine_amount}</small>
                                    </td>
                                    <td class="bg-info align-middle p-3 text-nowrap">
                                        <small class="fw-semibold">Price</small> <br>
                                        <small class="text-black-50">Rs ${plan.net_amount}</small>
                                    </td>
                                    <td class="bg-info align-middle text-end p-3">
                                        <img src="{{ asset('assets/images') }}${isActive ? '/active-tag.svg' : '/expire-tag.svg'}" alt="" width="${isActive ? 76 : 73}" height="${isActive ? 67 : 24}">
                                    </td>
                                </tr>`;

                            tbody.append(row);
                        });

                        $('.spinner-border').replaceWith(originalBtn);
                        $('#membershipplan').modal('show')
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

        $(document).on('click', '.walletRechargeBtn', function() {
            let memberId = $(this).data('id');
            let originalBtn = $(this).prop('outerHTML'); // save original button
            $(this).replaceWith('<span class="spinner-border spinner-border-sm text-primary"></span>');

            $.ajax({
                url: '{{route("club-member.fetch-wallet-balance", ":memberId")}}'.replace(':memberId', memberId),
                type: 'GET',
                success: function(response){
                    if (response.statusCode == 200) {
                        // console.log(response);
                        $('#walletMemberId').val(memberId);
                        $('#walletBalance').text('₹' + (response.data.walletBalance ?? 0.00));

                        let tbody = $('#walletTransactionTbody');
                        tbody.empty();

                        if(response.data.walletTransactionHistory.length > 0){
                            response.data.walletTransactionHistory.forEach(function(transaction) {
                                let amount = transaction.amount;
                                let direction = transaction.direction;
                                let date = new Date(transaction.created_at).toLocaleDateString('en-IN', { timeZone: 'Asia/Kolkata' });
                                let amountClass = direction == 'credit' ? 'text-success' : 'text-danger';
                                let sign = direction == 'credit' ? '+' : '-';
                                let label = direction == 'credit' ? 'Added By' : 'Used';
                                let maker = transaction.creator?.name ?? '-';
                                let remarks = transaction.payment?.remarks ?? '';

                                let row = `
                                    <tr>
                                        <td class="border-secondary bg-transparent align-middle lh-sm">
                                            <small class="fw-semibold">${label}:</small>
                                            <small class="text-black-50">${maker}</small>

                                            ${remarks ? `<br>
                                            <small class="fw-semibold">Remarks:</small>
                                            <small class="text-black-50">${remarks}</small>` : ''}

                                        </td>


                                        <td class="${amountClass} text-end border-secondary bg-transparent align-middle">
                                            ${sign}₹${amount}
                                        </td>
                                    </tr>`;

                                tbody.append(row);
                            });
                        } else {
                            tbody.append('<tr><td colspan="2" class="text-center">No transactions found</td></tr>');
                        }


                        $('.spinner-border').replaceWith(originalBtn);
                        $('#walletrecharge').modal('show');
                    }
                    else{
                        toastr.error('Something Went Wrong').
                        $('.spinner-border').replaceWith(originalBtn);
                        // console.log(response);
                    }

                },
                error: function(){
                    toastr.error('Something Went Wrong.');
                }
            });

        });

        $('#walletRechargeForm').on('submit', function (e) {
            e.preventDefault();

            $('#confirmRechargeModal').modal('show');
        });

        $(document).on('click', '#confirmRechargeBtn', function () {

            $('#confirmRechargeModal').modal('hide');

            $('#rechargeSubmitBtn')
                .prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm me-2"></span> Processing...');

            let walletRechargeMemberformData = new FormData($("#walletRechargeForm")[0]);

            $.ajax({
                url: "{{ route('club-member.recharge-wallet-balance') }}",
                type: "POST",
                data: walletRechargeMemberformData,
                processData: false,
                contentType: false,

                success: function(response) {

                    if (response.statusCode == 200) {

                        toastr.success(response.message);

                        $('#rechargeSubmitBtn').hide();

                        setTimeout(() => {
                            window.location.href = "{{ route('club-member.list') }}";
                        }, 1500);

                    } else {

                        toastr.error(response.message ?? "Something went wrong");

                        $('#rechargeSubmitBtn')
                            .prop('disabled', false)
                            .html('Recharge Wallet');
                    }
                },

                error: function() {

                    toastr.error("Something went wrong, Please try again.");

                    $('#rechargeSubmitBtn')
                        .prop('disabled', false)
                        .html('Recharge Wallet');
                }
            });

        });

        $(document).on('click', '.memberDeleteBtn', function(){
            // alert($(this).data('id'));
            $('#confirmDeleteBtn').data('id', $(this).data('id'));
        });

        $(document).on('click', '#confirmDeleteBtn', function(){
            // $('#confirmDeleteBtn').on('click', function(){
            let memberId = $(this).data('id');
            // let originalBtn = $(this).prop('outerHTML'); // save original button
            // $(this).replaceWith('<span class="spinner-border spinner-border-sm text-danger"></span>');

            $.ajax({
                url: '{{route("club-member.delete", ":memberId")}}'.replace(':memberId', memberId),
                type: 'DELETE',
                data:{
                    _token: "{{ csrf_token() }}"
                },
                        success: function(response){
                    if (response.statusCode == 200) {
                        toastr.success(response.message);

                        // $('.spinner-border').replaceWith(originalBtn);
                    }
                    else {
                        // $btn.html(originalText);
                        // $btn.prop('disabled', false);
                        if(response.message){
                            console.log(response.message);
                            toastr.error(response.message);
                        }
                        else{
                            toastr.error("Something went wrong, Please try again.");
                            // console.log(response)
                        }
                    }
                },
                error: function(){
                    toastr.error('Something Went Wrong.');
                }
            });
        });

        $(document).on('click', '.memberEditBtn', function() {

            let memberId = $(this).data('id');
            let originalBtn = $(this).prop('outerHTML');

            $(this).replaceWith('<span class="spinner-border spinner-border-sm text-primary"></span>');

            $.ajax({
                url: '{{route("club-member.view", ":memberId")}}'.replace(':memberId', memberId),
                type: 'GET',

            success: function(response){

                if(response.statusCode == 200){

                    let data = response.data;
                    let details = data.member_details.details;
                    //let details = data.member_details?.details ?? {};

                    $('#club_member_id').val(data.id);

                    $('#club_member_name').val(data.name);
                    $('#club_member_email').val(data.email);
                    $('#club_member_phone').val(data.phone);
                    $('#club_member_address').val(data.address);

                    $('#club_status').val(data.status);

                    $('#club_member_blood_grp').val(details.blood_grp);

                    $('#spouse_name').val(details.spouse_name);
                    $('#spouse_email').val(details.spouse_email);
                    $('#edit_spouse_phone').val(details.spouse_phone);
                    $('#spouse_blood_grp').val(details.spouse_blood_grp);
                    $('#spouse_address').val(details.spouse_address);

                    // $('#member_image_preview').attr('src', data.image);
                    // $('#spouse_image_preview').attr('src', details.spouse_image);

                    // $('#card_no').val(data.card_details.id);

                    $('#current_card_no').text(data.card_details?.card_no || '-');

                    $('#current_membership').text(data.purchase_history[0].membership_plan_type.name);

                    $('.spinner-border').replaceWith(originalBtn);

                    $('#editclubmember').modal('show');
                }
            },

                error: function(){

                    toastr.error('Something Went Wrong.');
                    $('.spinner-border').replaceWith(originalBtn);

                }

            });

        });

        $('#clubMemberEditForm').on('submit', function (e) {
            e.preventDefault();

            const $btn = $('#club_edit_submit');
            const originalText = $btn.val();

            let isValid = true;

            $('.phone-input').each(function(){

                let phone = $(this).val();
                let errorDiv = $(this).next('.error-div');

                if(phone !== '' && !/^\d{10}$/.test(phone)){

                    errorDiv.text('Phone number must be 10 digits.');
                    $(this).addClass('is-invalid');
                    isValid = false;

                }else{

                    $(this).removeClass('is-invalid');
                    errorDiv.text('');

                }

            });


            $('.profile-image').each(function(){

                let fileInput = this;
                let errorDiv = $(this).closest('.form-part').find('.error-div');

                if(fileInput.files.length > 0){

                    let file = fileInput.files[0];

                    let allowedTypes = ['image/jpeg','image/png'];
                    let maxSize = 5 * 1024 * 1024; // 5MB

                    let errors = [];

                    if(!allowedTypes.includes(file.type)){
                        errors.push('Only JPG, JPEG and PNG images are allowed.');
                    }

                    if(file.size > maxSize){
                        errors.push('Image must be less than 5MB.');
                    }

                    if(errors.length > 0){

                        isValid = false;
                        errorDiv.html(errors.join('<br>'));
                        $(this).addClass('is-invalid');

                    }else{

                        errorDiv.text('');
                        $(this).removeClass('is-invalid');

                    }

                }else{

                    errorDiv.text('');
                    $(this).removeClass('is-invalid');

                }

            });


            if(!isValid){
                return false;
            }

            $btn.prop('disabled', true);
            $btn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...');

            let formData = new FormData($("#clubMemberEditForm")[0]);

            $.ajax({

                url: "{{ route('club-member.update') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,

                success: function(response){

                    if(response.statusCode == 200){
                        $btn.html(originalText);

                        toastr.success(response.message);
                        // setTimeout(()=>location.reload(),1500);
                        setTimeout(() => {
                            window.location.href = "{{ route('club-member.list') }}";
                        }, 1500);

                    }else{
                        $btn.html(originalText);
                        $btn.prop('disabled', false);
                        // console.log(response)
                        toastr.error(response.message ?? "Something went wrong");

                    }

                },

                error: function(xhr){
                    $btn.html(originalText);
                    $btn.prop('disabled', false);

                    toastr.error("Something went wrong, Please try again.");

                    let responseError = xhr.responseJSON?.error ?? "Something went wrong.";
                    console.error(responseError);

                }

            });

        });

        $(document).on('click', '.addOnBtn', function() {
            let btn = $(this);
            let memberId = $(this).data('id');
            let originalBtn = $(this).prop('outerHTML'); // save original button
            $(this).replaceWith('<span class="spinner-border spinner-border-sm text-primary addon-loader"></span>');

            $('#addOnMemberId').val(memberId);
            $('#addonTotalAmount').text(0);
            $('#addonTotalWrapper').addClass('d-none');
            // reset old selections
            $('.addon-checkbox').prop('checked', false).prop('disabled', false);
            $('.addon-date').addClass('d-none');

            $.ajax({
                url: "{{ route('club-member.member-addon.list') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    member_id: memberId
                },

                success: function (response) {

                    if (response.statusCode == 200) {

                        let total = 0;
                        let addonIds = [];

                        // mark already purchased addons
                        response.data.forEach(function(addon){
                            // collect addon ids
                            addonIds.push(addon.add_on_id);

                            let checkbox = $('#addon' + addon.add_on_id);
                            checkbox.prop('checked', true).prop('disabled', true); // prevent repurchase

                            // SHOW START & END DATE
                            let dateBox = $('#addonDate' + addon.add_on_id);

                            dateBox.removeClass('d-none');
                            dateBox.find('.start-date').text(addon.start_date);
                            dateBox.find('.end-date').text(addon.end_date);

                            // total += parseFloat(addon.price);
                        });

                        // store in hidden field
                        $('#existingAddonIds').val(addonIds.join(','));

                        // $('#addonTotalAmount').text(total);

                        // open modal AFTER data load
                        $('#addAddonModal').modal('show');

                    } else {
                        toastr.error(response.message ?? "Failed to load add-ons");
                    }
                },

                error: function () {
                    toastr.error("Something went wrong");
                },

                complete: function () {
                    // restore button
                    $('.addon-loader').replaceWith(originalBtn);
                }
            });

        });

        $(document).on('click', '#purchaseAddonBtn', function (e) {
            e.preventDefault();
            let memberId = $('#addOnMemberId').val();
            let total = 0;
            let addons = [];
            // already taken addons
            let existingIds = $('#existingAddonIds').val()
                ? $('#existingAddonIds').val().split(',').map(Number)
                : [];

            $('.addon-checkbox:checked').each(function () {
                // addons.push($(this).val());
                let addonId = parseInt($(this).val());
                let price = parseFloat($(this).data('price'));

                // push ONLY NEW addons
                if (!existingIds.includes(addonId)) {
                    total += price;
                    addons.push(addonId);
                }
            });

            if ((addons.length === 0) || (total <= 0)) {
                toastr.error('No new add-on selected');
                return;
            }

            let btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Processing...');

            $.ajax({
                url: "{{ route('club-member.member-addon.purchase') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    member_id: memberId,
                    addons: addons,
                    amount: total
                },
                success: function (response) {

                    if (response.statusCode == 200) {
                        toastr.success(response.message);
                        $('#addAddonModal').modal('hide');

                    } else {
                        toastr.error(response.message ?? "Something went wrong");
                    }
                },
                error: function () {
                    toastr.error('Something went wrong');
                },
                complete: function () {
                    btn.prop('disabled', false).html('Purchase Add-On');
                }
            });

        });

        $(document).on('change', '.addon-checkbox', function () {
            let total = 0;
            let existingIds = $('#existingAddonIds').val()
                            ? $('#existingAddonIds').val().split(',').map(Number)
                            : [];

            let newAddonSelected = false;

            $('.addon-checkbox:checked').each(function () {
                // total += parseFloat($(this).data('price'));

                let addonId = parseInt($(this).val());
                let price = parseFloat($(this).data('price'));

                // check mismatch (NEW addon)
                if (!existingIds.includes(addonId)) {
                    total += price;
                    newAddonSelected = true;
                }
            });

            $('#addonTotalAmount').text(total.toFixed(2));
            // $('#addonTotalAmount').text(total);

            // show only if NEW addon selected
            if (newAddonSelected && total > 0) {
                $('#addonTotalWrapper').removeClass('d-none');
            } else {
                $('#addonTotalWrapper').addClass('d-none');
            }
        });

    });
</script>
@endsection
