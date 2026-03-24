@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')
    <div class="repeat-holder">
        <div class="box-grid">
            <a href="javascript:void(0)"
                class="card text-white bg-info border-0 d-flex justify-content-between flex-row-reverse">
                <div class="card-header bg-transparent border-0"><i class="fa-solid fa-users"></i></div>
                <div class="card-body">
                    <p class="card-text mb-2 fw-medium">Total Members</p>
                    <h2 class="card-title fs-4">{{ $totalMembers }}</h2>
                </div>
            </a>
            <a href="javascript:void(0)"
                class="card text-white bg-success border-0 d-flex justify-content-between flex-row-reverse">
                <div class="card-header bg-transparent border-0"><i class="fa-solid fa-user-check"></i>
                </div>
                <div class="card-body">
                    <p class="card-text mb-2 fw-medium">Active Members</p>
                    <h2 class="card-title fs-4">{{ $activeMembers }}</h2>
                </div>
            </a>
            <a href="javascript:void(0)"
                class="card text-white bg-secondary border-0 d-flex justify-content-between flex-row-reverse">
                <div class="card-header bg-transparent border-0"><i class="fa-solid fa-user-minus"></i>
                </div>
                <div class="card-body">
                    <p class="card-text mb-2 fw-medium">Expired Members</p>
                    <h2 class="card-title fs-4">{{ $expiredMembers }}</h2>
                </div>
            </a>
            <a href="javascript:void(0)"
                class="card text-white bg-warning border-0 d-flex justify-content-between flex-row-reverse">
                <div class="card-header bg-transparent border-0"><i class="fa-solid fa-user-plus"></i></div>
                <div class="card-body">
                    <p class="card-text mb-2 fw-medium">Pending Approval</p>
                    <h2 class="card-title fs-4">{{ $pendingApprovals }}</h2>
                </div>
            </a>
            <a href="javascript:void(0)"
                class="card text-white bg-primary border-0 d-flex justify-content-between flex-row-reverse">
                <div class="card-header bg-transparent border-0"><i
                        class="fa-solid fa-arrow-right-to-bracket"></i></div>
                <div class="card-body">
                    <p class="card-text mb-2 fw-medium">New Members In This Month</p>
                    <h2 class="card-title fs-4">{{ $thisMonthSignups }}</h2>
                </div>
            </a>
            <a href="javascript:void(0)"
                class="card text-white bg-danger border-0 d-flex justify-content-between flex-row-reverse">
                <div class="card-header bg-transparent border-0"><i class="fa-regular fa-clock"></i></div>
                <div class="card-body">
                    <p class="card-text mb-2 fw-medium">Expiring Soon</p>
                    <h2 class="card-title fs-4">{{ $expiringSoon }}</h2>
                </div>
            </a>
        </div>
    </div>
    <div class="repeat-holder mt-5">
        <h2 class="fs-5 common-heading mb-4 fw-semibold">Quick Actions</h2>
        <div class="row">
            <div class="col-xl-3 col-md-6 my-xl-0 my-2">
                <a href="{{ route('club-member.list', ['type' => 'addMember']) }}"
                    class="action-box card bg-white border-0 d-flex align-items-center justify-content-between flex-row-reverse h-100">
                    <div class="card-header bg-transparent border-0">
                        <i class="fa-solid fa-user-plus text-warning fs-4"></i>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Add Club Member</p>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-md-6 my-xl-0 my-2">
                <a href="{{ route('swimming-member.list', ['type' => 'addMember']) }}"
                    class="action-box card bg-white border-0 d-flex align-items-center justify-content-between flex-row-reverse h-100">
                    <div class="card-header bg-transparent border-0">
                        <i class="fa-solid fa-user-plus text-info fs-4"></i>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Add Swimming member</p>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-md-6 my-xl-0 my-2">
                <a href="{{ route('memberActionApproval.list')}}"
                    class="action-box card bg-white border-0 d-flex align-items-center justify-content-between flex-row-reverse h-100">
                    <div class="card-header bg-transparent border-0">
                        <i class="fa-regular fa-circle-check fs-4 text-success"></i>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Member Approval</p>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-md-6 my-xl-0 my-2">
                <a href="javascript:void(0)" id="viewReportBtn"
                    class="action-box card bg-white border-0 d-flex align-items-center justify-content-between flex-row-reverse h-100">
                    <div class="card-header bg-transparent border-0">
                        <i class="fa-regular fa-file text-secondary fs-4"></i>
                    </div>
                    <div class="card-body">
                        <p class="card-text">View Report</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
    <div class="repeat-holder mt-5">
        <div class="row">
            <div class="col-xl-6">
                <div class="member-list-part my-xl-0 my-2">
                    <div
                        class="d-flex flex-wrap align-items-center justify-content-between gap-2 gap-lg-3 mb-2">
                        <h2 class="fs-5 common-heading mb-md-0 fw-semibold">Club Member list</h2>
                        <a href="{{ route('club-member.list', ['type' => 'addMember']) }}" class="btn btn-info">+ Add Club member</a>
                        {{-- <button class="btn btn-info" data-bs-toggle="modal"
                            data-bs-target="#addclubmember">+ Add club member</button> --}}
                    </div>
                    <div class="table-responsive">
                        <table class="table rounded-3 overflow-hidden clubmemberlist" cellspacing="0"
                            width="100%">
                            <thead>
                                <tr>
                                    <th class="text-white fw-medium text-nowrap">Name</th>
                                    <th class="text-white fw-medium text-nowrap">Card Number</th>
                                    <th class="text-white fw-medium text-nowrap">Wallet</th>
                                    <th class="text-white fw-medium text-nowrap">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ( $clubMembers as $clubMember)
                                @php
                                    $latestActivePlan = $clubMember->purchaseHistory->where('status','active')->sortByDesc('expiry_date')->first();
                                    $planExpired = $latestActivePlan && $latestActivePlan->expiry_date && \Carbon\Carbon::parse($latestActivePlan->expiry_date)->isPast();
                                @endphp
                                <tr>
                                    <td class="text-nowrap">{{ $clubMember->name }}</td>
                                    <td class="text-nowrap">{{ $clubMember->cardDetails?->card_no ?? '-' }}</td>
                                    <td class="text-nowrap">₹ {{$clubMember->walletDetails?->current_balance ?? 0}}</td>
                                    @if ($clubMember->status == 'active')
                                        <td class="text-nowrap">
                                            <span class="text-success">Active</span>
                                            @if($planExpired)
                                                <br><span class="badge bg-danger" style="font-size:0.68rem;">Plan Expired</span>
                                            @endif
                                        </td>
                                    @elseif ($clubMember->status == 'pending')
                                        <td class="text-warning text-nowrap">Pending</td>
                                    @elseif ($clubMember->status == 'rejected')
                                        <td class="text-danger text-nowrap">Rejected</td>
                                    @endif
                                </tr>
                                @endforeach
                                {{-- <tr>
                                    <td class="text-nowrap">Soumen Das</td>
                                    <td class="text-nowrap">12345abcd</td>
                                    <td class="text-nowrap">Rs 2000</td>
                                    <td class="text-success text-nowrap">Active</td>
                                </tr>
                                <tr>
                                    <td class="text-nowrap">Soumen Dass</td>
                                    <td class="text-nowrap">12345abcd</td>
                                    <td class="text-nowrap">Rs 2000</td>
                                    <td class="text-secondary text-nowrap">Inactive</td>
                                </tr>
                                <tr>
                                    <td class="text-nowrap">Soumen Dasss</td>
                                    <td class="text-nowrap">12345abcd</td>
                                    <td class="text-nowrap">Rs 2000</td>
                                    <td class="text-danger text-nowrap">Blocked</td>
                                </tr> --}}
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end">
                        <a href="{{ route('club-member.list') }}" class="fw-semibold"><small><u>View All</u></small></a>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="member-list-part my-xl-0 my-2">
                    <div
                        class="d-flex flex-wrap align-items-center justify-content-between gap-2 gap-lg-3 mb-2">
                        <h2 class="fs-5 common-heading mb-md-0 fw-semibold">Club Swimmer list</h2>
                        <a href="{{ route('swimming-member.list', ['type' => 'addMember']) }}" class="btn btn-info">+ Add Swimming member</a>
                        {{-- <button class="btn btn-info" data-bs-toggle="modal"
                            data-bs-target="#addswimmingmember">+ Add Swimming member</button> --}}
                    </div>
                    <div class="table-responsive">
                        <table class="table rounded-3 overflow-hidden clubmemberlist" cellspacing="0"
                            width="100%">
                            <thead>
                                <tr>
                                    <th class="text-white fw-medium text-nowrap">Name</th>
                                    {{-- <th class="text-white fw-medium text-nowrap">Card Number</th> --}}
                                    <th class="text-white fw-medium text-nowrap">Exp. Date</th>
                                    <th class="text-white fw-medium text-nowrap">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ( $swimMembers as $swimMember)
                                @php
                                    $latestActiveSwimPlan = $swimMember->purchaseHistory->where('status','active')->sortByDesc('expiry_date')->first();
                                    $swimPlanExpired = $latestActiveSwimPlan && $latestActiveSwimPlan->expiry_date && \Carbon\Carbon::parse($latestActiveSwimPlan->expiry_date)->isPast();
                                @endphp
                                <tr>
                                    <td class="text-nowrap">{{ $swimMember->name }}</td>
                                    {{-- <td class="text-nowrap">{{ $swimMember->cardDetails?->card_no ?? '-' }}</td> --}}
                                    <td class="text-nowrap {{ $swimPlanExpired ? 'text-danger fw-semibold' : '' }}">
                                        {{ isset($swimMember->purchaseHistory[0]) ? \Carbon\Carbon::parse($swimMember->purchaseHistory[0]->expiry_date)->format('d/m/Y') : 'N/A' }}
                                        @if($swimPlanExpired)
                                            <i class="fa-solid fa-circle-exclamation ms-1" title="Plan expired"></i>
                                        @endif
                                    </td>
                                    @if ($swimMember->status == 'active')
                                        <td class="text-nowrap">
                                            <span class="text-success">Active</span>
                                            @if($swimPlanExpired)
                                                <br><span class="badge bg-danger" style="font-size:0.68rem;">Plan Expired</span>
                                            @endif
                                        </td>
                                    @elseif ($swimMember->status == 'pending')
                                        <td class="text-warning text-nowrap">Pending</td>
                                    @elseif ($swimMember->status == 'rejected')
                                        <td class="text-danger text-nowrap">Rejected</td>
                                    @endif
                                </tr>
                                @endforeach
                                {{-- <tr>
                                    <td class="text-nowrap">Soumen Das</td>
                                    <td class="text-nowrap">12345abcd</td>
                                    <td class="text-nowrap">Rs 2000</td>
                                    <td class="text-success text-nowrap">Active</td>
                                </tr>
                                <tr>
                                    <td class="text-nowrap">Soumen Dass</td>
                                    <td class="text-nowrap">12345abcd</td>
                                    <td class="text-nowrap">Rs 2000</td>
                                    <td class="text-secondary text-nowrap">Inactive</td>
                                </tr>
                                <tr>
                                    <td class="text-nowrap">Soumen Dasss</td>
                                    <td class="text-nowrap">12345abcd</td>
                                    <td class="text-nowrap">Rs 2000</td>
                                    <td class="text-danger text-nowrap">Blocked</td>
                                </tr> --}}
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end">
                        <a href="{{ route('swimming-member.list') }}" class="fw-semibold"><small><u>View
                                    All</u></small></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modalComponent')
    <!-- card entry Modal start -->
    {{-- <div class="modal fade" id="cardentry" tabindex="-1" aria-labelledby="cardEntryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h2 class="modal-title fs-5 fw-semibold" id="cardEntryModalLabel">Gate Entry Logs</h2>
                    <button type="button" class="btn-close bg-transparent fs-5 lh-1" data-bs-dismiss="modal"
                        aria-label="Close"><i class="fa-regular fa-circle-xmark"></i></button>
                </div>
                <div class="modal-body">
                    <form action="">
                        <div class="row">
                            <div class="col-12">
                                <label for="" class="form-label fw-semibold text-dark mb-3"><span
                                        class="text-info rounded-3 label-icon p-1 d-inline-flex align-items-center justify-content-center me-2"><i
                                            class="fa-regular fa-user"></i></span>Member Details</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="Member name">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="Card No.">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <input type="text" onfocus="(this.type='date')"
                                                class="form-control py-2 shadow-none" id=""
                                                placeholder="Membership start date">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <input type="text" onfocus="(this.type='date')"
                                                class="form-control py-2 shadow-none" id=""
                                                placeholder="Membership End date">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="Wallet balance">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <input type="text" onfocus="(this.type='time')"
                                                class="form-control py-2 shadow-none" id="" placeholder="Entry time">
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
    </div> --}}
    <!-- card entry Modal end  -->

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
                    <form action="">
                        <div class="row">
                            <div class="col-lg-6">
                                <label for="" class="form-label fw-semibold text-dark mb-3"><span
                                        class="text-info rounded-3 label-icon p-1 d-inline-flex align-items-center justify-content-center me-2"><i
                                            class="fa-regular fa-user"></i></span> Personal Details</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="Full Name">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <input type="email" class="form-control py-2 shadow-none" id=""
                                                placeholder="Email">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <input type="tel" class="form-control py-2 shadow-none" id=""
                                                placeholder="Phone">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <select name="" id="" class="form-select py-2 shadow-none">
                                                <option value="">Blood Group</option>
                                                <option value="">B+</option>
                                                <option value="">AB+</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <textarea class="form-control py-2 shadow-none" id="" rows="3"
                                                placeholder="Address"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <label class="file-upload-box position-relative text-center border rounded-3 w-100 p-2">
                                                <input type="file" class="file-input opacity-0 position-absolute start-0 w-100">
                                                <div class="upload-content">
                                                    <i class="upload-icon"><i
                                                            class="fa-solid fa-arrow-up-from-bracket"></i></i>
                                                    <p class="upload-text mb-0">
                                                        Passport size Image & Signature
                                                    </p>
                                                    <small class="text-muted">
                                                        PNG & JPEG, max file size 10kb
                                                    </small>
                                                </div>
                                            </label>
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
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="Spouse Full Name">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <input type="email" class="form-control py-2 shadow-none" id=""
                                                placeholder="Email">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <input type="tel" class="form-control py-2 shadow-none" id=""
                                                placeholder="Phone">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <select name="" id="" class="form-select py-2 shadow-none">
                                                <option value="">Blood Group</option>
                                                <option value="">B+</option>
                                                <option value="">AB+</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <textarea class="form-control py-2 shadow-none" id="" rows="3"
                                                placeholder="Address"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <label class="file-upload-box position-relative text-center border rounded-3 w-100 p-2">
                                                <input type="file" class="file-input opacity-0 position-absolute start-0 w-100">
                                                <div class="upload-content">
                                                    <i class="upload-icon"><i
                                                            class="fa-solid fa-arrow-up-from-bracket"></i></i>
                                                    <p class="upload-text mb-0">
                                                        Passport size Image & Signature
                                                    </p>
                                                    <small class="text-muted">
                                                        PNG & JPEG, max file size 10kb
                                                    </small>
                                                </div>
                                            </label>
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
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="inlineRadioOptions"
                                                    id="inlineRadio1" value="option1">
                                                <label class="form-check-label"
                                                    for="inlineRadio1"><small>Annual</small></label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="inlineRadioOptions"
                                                    id="inlineRadio2" value="option2">
                                                <label class="form-check-label"
                                                    for="inlineRadio2"><small>Lifetime</small></label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="inlineRadioOptions"
                                                    id="inlineRadio3" value="option3">
                                                <label class="form-check-label"
                                                    for="inlineRadio3"><small>Silver</small></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="A/C Head">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="Card No.">
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <div class="form-part mb-3">
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="Taxable Amt.">
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <div class="form-part mb-3">
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="GST%">
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <div class="form-part mb-3">
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="GST Amt">
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <div class="form-part mb-3">
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="Receipt Amt">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <select name="" id="" class="form-select py-2 shadow-none">
                                                <option value="">Bank Name</option>
                                                <option value="">Bank Name 1</option>
                                                <option value="">Bank Name 2</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="Remarks">
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="text-end mod-footer mt-3">
                            <button type="button" class="btn btn-info fw-semibold"
                                data-bs-dismiss="modal">Cancel</button>
                            <input type="submit" class="btn btn-primary fw-semibold" value="Add member">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- add Club Member Modal end  -->

    <!-- add swimming Member Modal start  -->
    <div class="modal fade" id="addswimmingmember" tabindex="-1" aria-labelledby="addswimmingmemberModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h2 class="modal-title fs-5 fw-semibold" id="addswimmingmemberModalLabel">Swimming-only membership
                    </h2>
                    <button type="button" class="btn-close bg-transparent fs-5 lh-1" data-bs-dismiss="modal"
                        aria-label="Close"><i class="fa-regular fa-circle-xmark"></i></button>
                </div>
                <div class="modal-body">
                    <form action="">
                        <div class="row">
                            <div class="col-lg-12">
                                <label for="" class="form-label fw-semibold text-dark mb-3"><span
                                        class="text-info rounded-3 label-icon p-1 d-inline-flex align-items-center justify-content-center me-2"><i
                                            class="fa-regular fa-user"></i></span> Personal Details</label>
                                <div class="row">
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="Full Name">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <input type="email" class="form-control py-2 shadow-none" id=""
                                                placeholder="Email">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <input type="tel" class="form-control py-2 shadow-none" id=""
                                                placeholder="Phone">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="Address">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <input type="number" class="form-control py-2 shadow-none" id=""
                                                placeholder="Age">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <select name="" id="" class="form-select py-2 shadow-none">
                                                <option value="">Sex</option>
                                                <option value="">Male</option>
                                                <option value="">Female</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <input type="number" class="form-control py-2 shadow-none" id=""
                                                placeholder="Height">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <input type="number" class="form-control py-2 shadow-none" id=""
                                                placeholder="Weight">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-4">
                                        <div class="form-part mb-3">
                                            <input type="number" class="form-control py-2 shadow-none" id=""
                                                placeholder="Pulse Rate">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-4">
                                        <div class="form-part mb-3">
                                            <select name="" id="" class="form-select py-2 shadow-none">
                                                <option value="">Batch</option>
                                                <option value="">Batch1</option>
                                                <option value="">Batch2</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-4">
                                        <div class="form-part mb-3">
                                            <select name="" id="" class="form-select py-2 shadow-none">
                                                <option value="">Vaccination</option>
                                                <option value="">vaccination1</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" value="" id="flexCheck1">
                                            <label class="form-check-label" for="flexCheck1">
                                                <small>I have gone thorugh</small>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-lg-8">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100"><small>I am not suffering
                                                    from</small></label>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" id="inlineCheckbox1"
                                                    value="option1">
                                                <label class="form-check-label" for="inlineCheckbox1"><small>Any sudden
                                                        or
                                                        occasional faint</small></label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" id="inlineCheckbox2"
                                                    value="option2">
                                                <label class="form-check-label" for="inlineCheckbox2"><small>Lung/Heart
                                                        trouble</small></label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" id="inlineCheckbox3"
                                                    value="option3">
                                                <label class="form-check-label" for="inlineCheckbox3"><small>Skin
                                                        disease</small></label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" id="inlineCheckbox4"
                                                    value="option4">
                                                <label class="form-check-label" for="inlineCheckbox4"><small>Any other
                                                        disease</small></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-part mb-3">
                                            <label class="file-upload-box position-relative text-center border rounded-3 w-100 p-2">
                                                <input type="file" class="file-input opacity-0 position-absolute start-0 w-100">
                                                <div class="upload-content">
                                                    <i class="upload-icon"><i
                                                            class="fa-solid fa-arrow-up-from-bracket"></i></i>
                                                    <p class="upload-text mb-0">
                                                        Upload Passport size Image & Signature
                                                    </p>
                                                    <small class="text-muted">
                                                        Image format, PNG & JPEG, max file size 10kb
                                                    </small>
                                                </div>
                                            </label>
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
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="Father/Guardian’s Full Name">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-part mb-3">
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="Father/Guardian’s Occupation">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-part mb-3">
                                            <label class="file-upload-box position-relative text-center border rounded-3 w-100 p-2">
                                                <input type="file" class="file-input opacity-0 position-absolute start-0 w-100">
                                                <div class="upload-content">
                                                    <i class="upload-icon"><i
                                                            class="fa-solid fa-arrow-up-from-bracket"></i></i>
                                                    <p class="upload-text mb-0">
                                                        Upload Passport size Image & Signature
                                                    </p>
                                                    <small class="text-muted">
                                                        Image format, PNG & JPEG, max file size 10kb
                                                    </small>
                                                </div>
                                            </label>
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
                                    <div class="col-12">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100"><small>Plan type</small></label>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="inlineRadioOptions2"
                                                    id="inlineRadio4" value="option1">
                                                <label class="form-check-label"
                                                    for="inlineRadio4"><small>Annual</small></label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="inlineRadioOptions2"
                                                    id="inlineRadio5" value="option2">
                                                <label class="form-check-label"
                                                    for="inlineRadio5"><small>Lifetime</small></label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="inlineRadioOptions2"
                                                    id="inlineRadio6" value="option3">
                                                <label class="form-check-label"
                                                    for="inlineRadio6"><small>Silver</small></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <select name="" id="" class="form-select py-2 shadow-none">
                                                <option value="">Card Type</option>
                                                <option value="">Card Type1</option>
                                                <option value="">Card Type2</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="Card No.">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="Card Mode">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="A/C Head">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="Taxable Amt. (Min 500)">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="GST%">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="GST Amt">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="Receipt Amt">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <select name="" id="" class="form-select py-2 shadow-none">
                                                <option value="">Bank Name</option>
                                                <option value="">Bank Name 1</option>
                                                <option value="">Bank Name 2</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="Remarks">
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="text-end mod-footer mt-3">
                            <button type="button" class="btn btn-info fw-semibold"
                                data-bs-dismiss="modal">Cancel</button>
                            <input type="submit" class="btn btn-primary fw-semibold" value="Add Swimming-membership">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- add swimming Member Modal end  -->

    <!-- ===================== Membership Report Modal ===================== -->
    <div class="modal fade" id="membershipReportModal" tabindex="-1" aria-labelledby="membershipReportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">

                {{-- Header --}}
                <div class="modal-header border-0 pb-0"
                    style="background:linear-gradient(135deg,#1e3a5f,#2563eb);border-radius:12px 12px 0 0;">
                    <div>
                        <h5 class="fw-bold mb-0" id="membershipReportModalLabel" style="color:#fff !important;">
                            <i class="fa-solid fa-chart-bar me-2"></i>Membership Reports
                        </h5>
                        <small style="color:#fff;">Generate and download membership data reports</small>
                    </div>
                    <button type="button" class="btn-close bg-transparent fs-5 lh-1" data-bs-dismiss="modal"
                        aria-label="Close" style="filter:invert(1);"><i class="fa-regular fa-circle-xmark" style="color:#fff;"></i></button>
                </div>

                {{-- Tabs inside header --}}
                <div style="background:linear-gradient(135deg,#1e3a5f,#2563eb);padding:0 1rem 0;">
                    <div class="d-flex gap-1" id="reportTabs">
                        <button class="report-tab-btn active" data-report="memberships"
                            style="border:none;background:rgba(255,255,255,0.18);color:#fff;border-radius:8px 8px 0 0;padding:8px 18px;font-size:0.82rem;font-weight:600;transition:all 0.2s;">
                            <i class="fa-solid fa-users me-1"></i>Membership
                        </button>
                        <button class="report-tab-btn" data-report="expiry_fines"
                            style="border:none;background:transparent;color:#fff;border-radius:8px 8px 0 0;padding:8px 18px;font-size:0.82rem;font-weight:600;transition:all 0.2s;">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i>Expiry &amp; Fines
                        </button>
                        <button class="report-tab-btn" data-report="renewals"
                            style="border:none;background:transparent;color:#fff;border-radius:8px 8px 0 0;padding:8px 18px;font-size:0.82rem;font-weight:600;transition:all 0.2s;">
                            <i class="fa-solid fa-rotate-right me-1"></i>Renewal History
                        </button>
                    </div>
                </div>

                <div class="modal-body p-0">

                    {{-- Filter Bar --}}
                    <div class="px-4 py-3 border-bottom" style="background:#f8fafc;">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label mb-1 fw-semibold" style="font-size:0.75rem;color:#64748b;text-transform:uppercase;letter-spacing:.04em;">Membership Type</label>
                                <select id="rptMemberType" class="form-select form-select-sm shadow-none">
                                    <option value="all">All (Club + Swimming)</option>
                                    <option value="club">Club Membership</option>
                                    <option value="swimming">Swimming Membership</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1 fw-semibold" style="font-size:0.75rem;color:#64748b;text-transform:uppercase;letter-spacing:.04em;">Period</label>
                                <select id="rptPeriod" class="form-select form-select-sm shadow-none">
                                    <option value="daily">Today</option>
                                    <option value="weekly">Last 7 Days</option>
                                    <option value="monthly" selected>Last 30 Days</option>
                                    <option value="3months">Last 3 Months</option>
                                    <option value="6months">Last 6 Months</option>
                                    <option value="9months">Last 9 Months</option>
                                    <option value="yearly">Last 1 Year</option>
                                    <option value="custom">Custom Range</option>
                                </select>
                            </div>
                            <div class="col-md-2 rpt-custom-range d-none">
                                <label class="form-label mb-1 fw-semibold" style="font-size:0.75rem;color:#64748b;text-transform:uppercase;">From</label>
                                <input type="date" id="rptFromDate" class="form-control form-control-sm shadow-none">
                            </div>
                            <div class="col-md-2 rpt-custom-range d-none">
                                <label class="form-label mb-1 fw-semibold" style="font-size:0.75rem;color:#64748b;text-transform:uppercase;">To</label>
                                <input type="date" id="rptToDate" class="form-control form-control-sm shadow-none">
                            </div>
                            <div class="col-md-auto ms-auto">
                                <button class="btn btn-primary btn-sm px-4" id="generateReportBtn">
                                    <i class="fa-solid fa-magnifying-glass me-1"></i>Generate
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Result area --}}
                    <div id="reportResultArea" class="p-4">
                        <div class="text-center py-5" style="color:#94a3b8;">
                            <div style="width:72px;height:72px;background:#f1f5f9;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                                <i class="fa-solid fa-chart-bar fs-3" style="color:#cbd5e1;"></i>
                            </div>
                            <p class="fw-medium mb-1" style="color:#475569;">No report generated yet</p>
                            <small>Select your filters above and click <strong>Generate</strong> to view the report.</small>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- Membership Report Modal end -->
@endsection

@section('customJS')
<style>
    .report-tab-btn { cursor:pointer; }
    .report-tab-btn.active { background:rgba(255,255,255,0.92) !important; color:#1e3a5f !important; }
    #rpt-table thead th {
        background:#1e3a5f; color:#fff; font-size:0.75rem;
        text-transform:uppercase; letter-spacing:.04em;
        padding:10px 12px; white-space:nowrap; position:sticky; top:0; z-index:1;
    }
    #rpt-table tbody td { font-size:0.82rem; padding:8px 12px; vertical-align:middle; }
    #rpt-table tbody tr:hover { background:#f0f7ff; }
    .rpt-stat-card { border-radius:10px; padding:14px 18px; }
</style>
<script>
$(function () {

    var activeReportType = 'memberships';
    var lastReportData   = null;
    var lastReportMeta   = {};

    /* Open modal */
    $('#viewReportBtn').on('click', function () {
        $('#membershipReportModal').modal('show');
    });

    /* Tabs */
    $(document).on('click', '.report-tab-btn', function () {
        $('.report-tab-btn').css({ background:'transparent', color:'rgba(255,255,255,0.65)' }).removeClass('active');
        $(this).addClass('active');
        activeReportType = $(this).data('report');
        lastReportData   = null;
        resetResult();
    });

    /* Period change */
    $('#rptPeriod').on('change', function () {
        $('.rpt-custom-range').toggleClass('d-none', $(this).val() !== 'custom');
        lastReportData = null;
        resetResult();
    });

    function resetResult() {
        $('#reportResultArea').html(
            '<div class="text-center py-5" style="color:#94a3b8;">'
            + '<div style="width:72px;height:72px;background:#f1f5f9;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">'
            + '<i class="fa-solid fa-chart-bar fs-3" style="color:#cbd5e1;"></i></div>'
            + '<p class="fw-medium mb-1" style="color:#475569;">No report generated yet</p>'
            + '<small>Select your filters above and click <strong>Generate</strong> to view the report.</small>'
            + '</div>'
        );
    }

    /* Generate */
    $('#generateReportBtn').on('click', function () {
        var period = $('#rptPeriod').val();
        var payload = {
            _token:      '{{ csrf_token() }}',
            report_type: activeReportType,
            member_type: $('#rptMemberType').val(),
            period:      period,
            from_date:   period === 'custom' ? $('#rptFromDate').val() : '',
            to_date:     period === 'custom' ? $('#rptToDate').val() : '',
        };

        if (period === 'custom' && (!payload.from_date || !payload.to_date)) {
            toastr.warning('Please select both From and To dates.');
            return;
        }

        $('#reportResultArea').html(
            '<div class="text-center py-5">'
            + '<span class="spinner-border text-primary" style="width:2.5rem;height:2.5rem;"></span>'
            + '<p class="mt-3 text-muted small">Generating report...</p></div>'
        );

        $.ajax({
            url:  '{{ route("membership.report") }}',
            type: 'POST',
            data: payload,
            success: function (res) {
                if (res.statusCode !== 200) {
                    toastr.error(res.error || 'Something went wrong.');
                    resetResult();
                    return;
                }
                lastReportData = res.data;
                lastReportMeta = { from: res.from, to: res.to, type: activeReportType, payload: payload };
                renderReport(activeReportType, res);
            },
            error: function () {
                toastr.error('Something went wrong.');
                resetResult();
            }
        });
    });

    /* ── Render ──────────────────────────────────────────────────── */
    function renderReport(type, res) {
        var data = res.data;

        /* Summary stats */
        var statsHtml = buildStats(type, data, res);

        /* Table */
        var headers = [], rows = [];
        if (type === 'memberships') {
            headers = ['#','Name','Card No','Type','Plan','Start Date','Expiry Date','Fee','Net Amount','Status','Tag'];
            $.each(data, function (i, r) {
                rows.push([
                    i+1, r.name, r.card_no, r.member_type, r.plan,
                    r.start_date, r.expiry_date, '₹'+r.fee, '₹'+r.net_amount, r.status,
                    r.is_renewal ? '<span class="badge" style="background:#f59e0b;color:#fff;">Renewal</span>'
                                 : '<span class="badge bg-success">New</span>'
                ]);
            });
        } else if (type === 'expiry_fines') {
            headers = ['#','Name','Card No','Type','Plan','Expiry Date','Days Overdue','Pending Fine','Status'];
            $.each(data, function (i, r) {
                var badge = r.expiry_status === 'Expired'
                    ? '<span class="badge bg-danger">Expired</span>'
                    : '<span class="badge" style="background:#f59e0b;color:#fff;">Expiring Soon</span>';
                var fineVal = parseFloat(r.pending_fine.replace(',',''));
                rows.push([
                    i+1, r.name, r.card_no, r.member_type, r.plan, r.expiry_date,
                    r.days_overdue > 0 ? r.days_overdue+' days' : '—',
                    fineVal > 0 ? '<span class="text-danger fw-semibold">₹'+r.pending_fine+'</span>' : '₹'+r.pending_fine,
                    badge
                ]);
            });
        } else if (type === 'renewals') {
            headers = ['#','Name','Card No','Type','Plan','Renewal Date','New Expiry','Fee','Fine at Renewal','Net Amount','Status'];
            $.each(data, function (i, r) {
                var fineVal = parseFloat(r.fine_at_renewal.replace(',',''));
                rows.push([
                    i+1, r.name, r.card_no, r.member_type, r.plan,
                    r.renewal_date, r.expiry_date, '₹'+r.fee,
                    fineVal > 0 ? '<span class="text-danger">₹'+r.fine_at_renewal+'</span>' : '<span class="text-muted">₹'+r.fine_at_renewal+'</span>',
                    '₹'+r.net_amount, r.status
                ]);
            });
        }

        var tableHtml = '';
        if (!data.length) {
            tableHtml = '<div class="text-center py-4 text-muted"><i class="fa-solid fa-inbox fs-3 mb-2 d-block"></i><small>No records found for the selected period.</small></div>';
        } else {
            tableHtml = '<div class="table-responsive" style="max-height:340px;overflow-y:auto;">'
                      + '<table class="table table-hover mb-0" id="rpt-table"><thead><tr>';
            $.each(headers, function (_, h) { tableHtml += '<th>' + h + '</th>'; });
            tableHtml += '</tr></thead><tbody>';
            $.each(rows, function (_, cells) {
                tableHtml += '<tr>';
                $.each(cells, function (_, c) { tableHtml += '<td class="text-nowrap">' + c + '</td>'; });
                tableHtml += '</tr>';
            });
            tableHtml += '</tbody></table></div>';
        }

        /* Download bar */
        var dlBar = data.length ? (
            '<div class="d-flex justify-content-between align-items-center mb-3">'
            + '<div>'
            + '<span class="fw-semibold me-2" style="font-size:0.82rem;color:#475569;">'
            + '<i class="fa-regular fa-calendar me-1"></i>' + res.from + ' – ' + res.to + '</span>'
            + '<span class="badge bg-primary bg-opacity-10 text-primary">' + data.length + ' record(s)</span>'
            + '</div>'
            + '<div class="d-flex gap-2">'
            + '<button class="btn btn-sm btn-outline-success" id="downloadCsvBtn" style="font-size:0.78rem;">'
            + '<i class="fa-solid fa-file-csv me-1"></i>Download CSV</button>'
            + '<button class="btn btn-sm btn-outline-danger" id="downloadPdfBtn" style="font-size:0.78rem;">'
            + '<i class="fa-solid fa-file-pdf me-1"></i>Download PDF</button>'
            + '</div></div>'
        ) : '';

        $('#reportResultArea').html(statsHtml + dlBar + tableHtml);

        /* Store table data for CSV */
        window._rptHeaders = headers;
        window._rptRows    = rows;
    }

    /* ── Summary stats ───────────────────────────────────────────── */
    function buildStats(type, data, res) {
        if (!data.length) return '';
        var cards = [];

        if (type === 'memberships') {
            var newCount     = data.filter(function(r){ return !r.is_renewal; }).length;
            var renewalCount = data.filter(function(r){ return  r.is_renewal; }).length;
            var totalFee     = data.reduce(function(s,r){ return s + parseFloat(r.fee.replace(',','')); }, 0);
            var totalNet     = data.reduce(function(s,r){ return s + parseFloat(r.net_amount.replace(',','')); }, 0);
            cards = [
                { icon:'fa-users',           label:'Total',    val: data.length,         color:'#2563eb', bg:'#eff6ff' },
                { icon:'fa-user-plus',        label:'New',      val: newCount,             color:'#16a34a', bg:'#f0fdf4' },
                { icon:'fa-rotate-right',     label:'Renewal',  val: renewalCount,         color:'#d97706', bg:'#fffbeb' },
                { icon:'fa-indian-rupee-sign',label:'Total Fee',val:'₹'+totalFee.toFixed(2), color:'#7c3aed', bg:'#fdf4ff' },
            ];
        } else if (type === 'expiry_fines') {
            var expired      = data.filter(function(r){ return r.expiry_status === 'Expired'; }).length;
            var expiringSoon = data.filter(function(r){ return r.expiry_status !== 'Expired'; }).length;
            var totalFine    = data.reduce(function(s,r){ return s + parseFloat(r.pending_fine.replace(',','')); }, 0);
            cards = [
                { icon:'fa-list',                  label:'Total',        val: data.length,         color:'#2563eb', bg:'#eff6ff' },
                { icon:'fa-circle-xmark',          label:'Expired',      val: expired,              color:'#dc2626', bg:'#fef2f2' },
                { icon:'fa-clock',                 label:'Expiring Soon',val: expiringSoon,          color:'#d97706', bg:'#fffbeb' },
                { icon:'fa-indian-rupee-sign',     label:'Total Fines',  val:'₹'+totalFine.toFixed(2), color:'#dc2626', bg:'#fef2f2' },
            ];
        } else if (type === 'renewals') {
            var totalFeeR = data.reduce(function(s,r){ return s + parseFloat(r.fee.replace(',','')); }, 0);
            var totalFineR= data.reduce(function(s,r){ return s + parseFloat(r.fine_at_renewal.replace(',','')); }, 0);
            var totalNetR = data.reduce(function(s,r){ return s + parseFloat(r.net_amount.replace(',','')); }, 0);
            cards = [
                { icon:'fa-rotate-right',      label:'Renewals',   val: data.length,          color:'#2563eb', bg:'#eff6ff' },
                { icon:'fa-indian-rupee-sign', label:'Total Fee',  val:'₹'+totalFeeR.toFixed(2), color:'#16a34a', bg:'#f0fdf4' },
                { icon:'fa-triangle-exclamation',label:'Total Fine',val:'₹'+totalFineR.toFixed(2), color:'#dc2626', bg:'#fef2f2' },
                { icon:'fa-wallet',            label:'Net Collected',val:'₹'+totalNetR.toFixed(2), color:'#7c3aed', bg:'#fdf4ff' },
            ];
        }

        var html = '<div class="row g-2 mb-3">';
        $.each(cards, function(_, c) {
            html += '<div class="col-6 col-md-3">'
                  + '<div class="rpt-stat-card d-flex align-items-center gap-3" style="background:' + c.bg + ';border:1px solid ' + c.color + '22;">'
                  + '<div style="width:36px;height:36px;border-radius:8px;background:' + c.color + '18;display:flex;align-items:center;justify-content:center;">'
                  + '<i class="fa-solid ' + c.icon + '" style="color:' + c.color + ';font-size:0.9rem;"></i></div>'
                  + '<div><div style="font-size:0.68rem;color:#64748b;text-transform:uppercase;letter-spacing:.04em;">' + c.label + '</div>'
                  + '<div class="fw-bold" style="color:' + c.color + ';font-size:1rem;">' + c.val + '</div></div>'
                  + '</div></div>';
        });
        html += '</div>';
        return html;
    }

    /* ── CSV Download ────────────────────────────────────────────── */
    $(document).on('click', '#downloadCsvBtn', function () {
        if (!window._rptHeaders || !window._rptRows) return;
        var csvRows = [window._rptHeaders.join(',')];
        $.each(window._rptRows, function(_, row) {
            var clean = $.map(row, function(c) {
                var txt = $('<div>').html(String(c)).text().replace(/"/g, '""');
                return '"' + txt + '"';
            });
            csvRows.push(clean.join(','));
        });
        var blob = new Blob([csvRows.join('\n')], { type: 'text/csv;charset=utf-8;' });
        var url  = URL.createObjectURL(blob);
        var a    = document.createElement('a');
        a.href = url;
        var safeFrom = (lastReportMeta.from || '').replace(/\//g, '-');
        var safeTo   = (lastReportMeta.to   || '').replace(/\//g, '-');
        a.download = 'membership_report_' + safeFrom + '_to_' + safeTo + '.csv';
        a.click();
        URL.revokeObjectURL(url);
    });

    /* ── PDF Download ────────────────────────────────────────────── */
    $(document).on('click', '#downloadPdfBtn', function () {
        if (!lastReportMeta.payload) return;
        var p = lastReportMeta.payload;
        var url = '{{ route("membership.report.pdf") }}'
            + '?report_type=' + encodeURIComponent(p.report_type)
            + '&member_type=' + encodeURIComponent(p.member_type)
            + '&period='      + encodeURIComponent(p.period)
            + '&from_date='   + encodeURIComponent(p.from_date || '')
            + '&to_date='     + encodeURIComponent(p.to_date   || '');
        window.open(url, '_blank');
    });

});
</script>
@endsection
