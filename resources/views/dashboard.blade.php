@extends('base.app')

@section('title', 'LakeFriends Calcutta')

@section('content')
    <div class="repeat-holder">
        <div class="box-grid">
            <a href="#"
                class="card text-white bg-info border-0 d-flex justify-content-between flex-row-reverse">
                <div class="card-header bg-transparent border-0"><i class="fa-solid fa-users"></i></div>
                <div class="card-body">
                    <p class="card-text mb-2 fw-medium">Total Members</p>
                    <h2 class="card-title fs-4">120</h2>
                </div>
            </a>
            <a href="#"
                class="card text-white bg-success border-0 d-flex justify-content-between flex-row-reverse">
                <div class="card-header bg-transparent border-0"><i class="fa-solid fa-user-check"></i>
                </div>
                <div class="card-body">
                    <p class="card-text mb-2 fw-medium">Active Members</p>
                    <h2 class="card-title fs-4">120</h2>
                </div>
            </a>
            <a href="#"
                class="card text-white bg-secondary border-0 d-flex justify-content-between flex-row-reverse">
                <div class="card-header bg-transparent border-0"><i class="fa-solid fa-user-minus"></i>
                </div>
                <div class="card-body">
                    <p class="card-text mb-2 fw-medium">Expired Members</p>
                    <h2 class="card-title fs-4">120</h2>
                </div>
            </a>
            <a href="#"
                class="card text-white bg-warning border-0 d-flex justify-content-between flex-row-reverse">
                <div class="card-header bg-transparent border-0"><i class="fa-solid fa-user-plus"></i></div>
                <div class="card-body">
                    <p class="card-text mb-2 fw-medium">Pending Approval</p>
                    <h2 class="card-title fs-4">120</h2>
                </div>
            </a>
            <a href="#"
                class="card text-white bg-primary border-0 d-flex justify-content-between flex-row-reverse">
                <div class="card-header bg-transparent border-0"><i
                        class="fa-solid fa-arrow-right-to-bracket"></i></div>
                <div class="card-body">
                    <p class="card-text mb-2 fw-medium">This Month Signups</p>
                    <h2 class="card-title fs-4">120</h2>
                </div>
            </a>
            <a href="#"
                class="card text-white bg-danger border-0 d-flex justify-content-between flex-row-reverse">
                <div class="card-header bg-transparent border-0"><i class="fa-regular fa-clock"></i></div>
                <div class="card-body">
                    <p class="card-text mb-2 fw-medium">Expiring Soon</p>
                    <h2 class="card-title fs-4">120</h2>
                </div>
            </a>
        </div>
    </div>
    <div class="repeat-holder mt-5">
        <h2 class="fs-5 common-heading mb-4 fw-semibold">Quick Actions</h2>
        <div class="row">
            <div class="col-xl-3 col-md-6 my-xl-0 my-2">
                <a href="#"
                    class="action-box card bg-white border-0 d-flex align-items-center justify-content-between flex-row-reverse h-100">
                    <div class="card-header bg-transparent border-0">
                        <i class="fa-solid fa-user-plus text-warning fs-4"></i>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Add Member</p>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-md-6 my-xl-0 my-2">
                <a href="#"
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
                <a href="#"
                    class="action-box card bg-white border-0 d-flex align-items-center justify-content-between flex-row-reverse h-100">
                    <div class="card-header bg-transparent border-0">
                        <i class="fa-regular fa-circle-check fs-4 text-success"></i>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Approval</p>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-md-6 my-xl-0 my-2">
                <a href="#"
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
                        <button class="btn btn-info" data-bs-toggle="modal"
                            data-bs-target="#addclubmember">+ Add club member</button>
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
                                <tr>
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
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end">
                        <a href="club-member.html" class="fw-semibold"><small><u>View All</u></small></a>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="member-list-part my-xl-0 my-2">
                    <div
                        class="d-flex flex-wrap align-items-center justify-content-between gap-2 gap-lg-3 mb-2">
                        <h2 class="fs-5 common-heading mb-md-0 fw-semibold">Club Swimmer list</h2>
                        <button class="btn btn-info" data-bs-toggle="modal"
                            data-bs-target="#addswimmingmember">+ Add Swimming member</button>
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
                                <tr>
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
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end">
                        <a href="swimming-member.html" class="fw-semibold"><small><u>View
                                    All</u></small></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modalComponent')
    <!-- card entry Modal start -->
    <div class="modal fade" id="cardentry" tabindex="-1" aria-labelledby="cardEntryModalLabel" aria-hidden="true">
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
    </div>
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
                                            <label class="file-upload-box text-center border rounded-3 w-100 p-2">
                                                <input type="file" class="file-input d-none">
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
                                            <label class="file-upload-box text-center border rounded-3 w-100 p-2">
                                                <input type="file" class="file-input d-none">
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
                                            <label class="file-upload-box text-center border rounded-3 w-100 p-2">
                                                <input type="file" class="file-input d-none">
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
                                            <label class="file-upload-box text-center border rounded-3 w-100 p-2">
                                                <input type="file" class="file-input d-none">
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
@endsection

@section('customJS')
@endsection
