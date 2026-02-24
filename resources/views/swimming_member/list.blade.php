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
                                <option value="Inactive">Inactive</option>
                                <option value="Blocked">Blocked</option>
                            </select>
                        </div>
                        <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addswimmingmember">+ Add club member</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table rounded-3 overflow-hidden clubmemberlist2" cellspacing="0"
                        width="100%">
                        <thead>
                            <tr>
                                <th class="text-white fw-medium align-middle text-nowrap">Name</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Phone</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Card Number
                                </th>
                                <th class="text-white fw-medium align-middle text-nowrap">Wallet</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Exp. Date</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Approve by
                                </th>
                                <th class="text-white fw-medium align-middle text-nowrap">Satus</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-nowrap">Soumen Das</td>
                                <td class="text-nowrap">+91 9025 321423</td>
                                <td class="text-nowrap">12345abcd</td>
                                <td class="text-nowrap">₹ 2000</td>
                                <td class="text-nowrap">12/31/2026</td>
                                <td class="text-nowrap">Admin</td>
                                <td class="text-success text-nowrap">Active</td>
                                <td class="text-nowrap">
                                    <button class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"
                                        data-bs-toggle="modal" data-bs-target="#viewprofile"
                                        title="View Profile"><small><i
                                                class="fa-regular fa-eye"></i></small></button>
                                    <button class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"
                                        data-bs-toggle="modal" data-bs-target="#membershipplan"
                                        title="Membership Plan"><small><i
                                                class="fa-sharp fa-clock-rotate-left"></i></small></button>
                                    <button class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"
                                        data-bs-toggle="modal" data-bs-target="#walletrecharge"
                                        title="Wallet Recharge"><small><i
                                                class="fa-solid fa-wallet"></i></small></button>
                                    <button class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"
                                        data-bs-toggle="modal" data-bs-target="#planrenewal"
                                        title="Plan Renewal"><small><i
                                                class="fa-solid fa-rotate-right"></i></small></button>
                                    <button class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"
                                        title="Edit"><small><i
                                                class="fa-solid fa-pen-to-square"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn delete-row"
                                        title="Delete"><small><i
                                                class="fa-solid fa-trash"></i></small></button>

                                </td>
                            </tr>
                            <tr>
                                <td class="text-nowrap">Soumen Das</td>
                                <td class="text-nowrap">+91 9025 321423</td>
                                <td class="text-nowrap">12345abcd</td>
                                <td class="text-nowrap">₹ 2000</td>
                                <td class="text-nowrap">12/31/2026</td>
                                <td class="text-nowrap">Admin</td>
                                <td class="text-danger text-nowrap">Blocked</td>
                                <td class="text-nowrap">
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-regular fa-eye"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-sharp fa-clock-rotate-left"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-wallet"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-rotate-right"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-pen-to-square"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-trash"></i></small></button>

                                </td>
                            </tr>
                            <tr>
                                <td class="text-nowrap">Soumen Das</td>
                                <td class="text-nowrap">+91 9025 321423</td>
                                <td class="text-nowrap">12345abcd</td>
                                <td class="text-nowrap">₹ 2000</td>
                                <td class="text-nowrap">12/31/2026</td>
                                <td class="text-nowrap">Admin</td>
                                <td class="text-secondary text-nowrap">Inactive</td>
                                <td class="text-nowrap">
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-regular fa-eye"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-sharp fa-clock-rotate-left"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-wallet"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-rotate-right"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-pen-to-square"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-trash"></i></small></button>

                                </td>
                            </tr>
                            <tr>
                                <td class="text-nowrap">Soumen Das</td>
                                <td class="text-nowrap">+91 9025 321423</td>
                                <td class="text-nowrap">12345abcd</td>
                                <td class="text-nowrap">₹ 2000</td>
                                <td class="text-nowrap">12/31/2026</td>
                                <td class="text-nowrap">Admin</td>
                                <td class="text-success text-nowrap">Active</td>
                                <td class="text-nowrap">
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-regular fa-eye"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-sharp fa-clock-rotate-left"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-wallet"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-rotate-right"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-pen-to-square"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-trash"></i></small></button>

                                </td>
                            </tr>
                            <tr>
                                <td class="text-nowrap">Soumen Das</td>
                                <td class="text-nowrap">+91 9025 321423</td>
                                <td class="text-nowrap">12345abcd</td>
                                <td class="text-nowrap">₹ 2000</td>
                                <td class="text-nowrap">12/31/2026</td>
                                <td class="text-nowrap">Admin</td>
                                <td class="text-secondary text-nowrap">Inactive</td>
                                <td class="text-nowrap">
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-regular fa-eye"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-sharp fa-clock-rotate-left"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-wallet"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-rotate-right"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-pen-to-square"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-trash"></i></small></button>

                                </td>
                            </tr>
                            <tr>
                                <td class="text-nowrap">Soumen Das</td>
                                <td class="text-nowrap">+91 9025 321423</td>
                                <td class="text-nowrap">12345abcd</td>
                                <td class="text-nowrap">₹ 2000</td>
                                <td class="text-nowrap">12/31/2026</td>
                                <td class="text-nowrap">Admin</td>
                                <td class="text-secondary text-nowrap">Inactive</td>
                                <td class="text-nowrap">
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-regular fa-eye"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-sharp fa-clock-rotate-left"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-wallet"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-rotate-right"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-pen-to-square"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-trash"></i></small></button>

                                </td>
                            </tr>
                            <tr>
                                <td class="text-nowrap">Soumen Das</td>
                                <td class="text-nowrap">+91 9025 321423</td>
                                <td class="text-nowrap">12345abcd</td>
                                <td class="text-nowrap">₹ 2000</td>
                                <td class="text-nowrap">12/31/2026</td>
                                <td class="text-nowrap">Admin</td>
                                <td class="text-success text-nowrap">Active</td>
                                <td class="text-nowrap">
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-regular fa-eye"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-sharp fa-clock-rotate-left"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-wallet"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-rotate-right"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-pen-to-square"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-trash"></i></small></button>

                                </td>
                            </tr>
                            <tr>
                                <td class="text-nowrap">Soumen Das</td>
                                <td class="text-nowrap">+91 9025 321423</td>
                                <td class="text-nowrap">12345abcd</td>
                                <td class="text-nowrap">₹ 2000</td>
                                <td class="text-nowrap">12/31/2026</td>
                                <td class="text-nowrap">Admin</td>
                                <td class="text-success text-nowrap">Active</td>
                                <td class="text-nowrap">
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-regular fa-eye"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-sharp fa-clock-rotate-left"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-wallet"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-rotate-right"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-pen-to-square"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-trash"></i></small></button>

                                </td>
                            </tr>
                            <tr>
                                <td class="text-nowrap">Soumen Dass</td>
                                <td class="text-nowrap">+91 9025 321423</td>
                                <td class="text-nowrap">12345abcd</td>
                                <td class="text-nowrap">₹ 2000</td>
                                <td class="text-nowrap">12/31/2026</td>
                                <td class="text-nowrap">Admin</td>
                                <td class="text-success text-nowrap">Active</td>
                                <td class="text-nowrap">
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-regular fa-eye"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-sharp fa-clock-rotate-left"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-wallet"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-rotate-right"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-pen-to-square"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-trash"></i></small></button>

                                </td>
                            </tr>
                            <tr>
                                <td class="text-nowrap">Soumen Dasss</td>
                                <td class="text-nowrap">+91 9025 321423</td>
                                <td class="text-nowrap">12345abcd</td>
                                <td class="text-nowrap">₹ 2000</td>
                                <td class="text-nowrap">12/31/2026</td>
                                <td class="text-nowrap">Admin</td>
                                <td class="text-danger text-nowrap">Blocked</td>
                                <td class="text-nowrap">
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-regular fa-eye"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-sharp fa-clock-rotate-left"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-wallet"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-rotate-right"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-pen-to-square"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-trash"></i></small></button>

                                </td>
                            </tr>
                            <tr>
                                <td class="text-nowrap">Ssoumen Das</td>
                                <td class="text-nowrap">+91 9025 321423</td>
                                <td class="text-nowrap">12345abcd</td>
                                <td class="text-nowrap">₹ 2000</td>
                                <td class="text-nowrap">12/31/2026</td>
                                <td class="text-nowrap">Admin</td>
                                <td class="text-danger text-nowrap">Blocked</td>
                                <td class="text-nowrap">
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-regular fa-eye"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-sharp fa-clock-rotate-left"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-wallet"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-rotate-right"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-pen-to-square"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-trash"></i></small></button>

                                </td>
                            </tr>
                            <tr>
                                <td class="text-nowrap">Sssoumen Das</td>
                                <td class="text-nowrap">+91 9025 321423</td>
                                <td class="text-nowrap">12345abcd</td>
                                <td class="text-nowrap">₹ 2000</td>
                                <td class="text-nowrap">12/31/2026</td>
                                <td class="text-nowrap">Admin</td>
                                <td class="text-danger text-nowrap">Blocked</td>
                                <td class="text-nowrap">
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-regular fa-eye"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-sharp fa-clock-rotate-left"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-wallet"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-rotate-right"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-pen-to-square"></i></small></button>
                                    <button
                                        class="border-0 bg-light p-1 rounded-3 lh-1 action-btn"><small><i
                                                class="fa-solid fa-trash"></i></small></button>

                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modalComponent')
    <!-- add Club Member Modal start -->
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
                    <form action="" id="swimmingMemberForm">
                        @csrf
                        <div class="row">
                            <div class="col-lg-12">
                                <label for="" class="form-label fw-semibold text-dark mb-3"><span
                                        class="text-info rounded-3 label-icon p-1 d-inline-flex align-items-center justify-content-center me-2"><i
                                            class="fa-regular fa-user"></i></span> Personal Details</label>
                                <div class="row">
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Full Name</small></label>
                                            <input type="text" class="form-control py-2 shadow-none" name="swim_name" id=""
                                                placeholder="Full Name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Email</small></label>
                                            <input type="email" class="form-control py-2 shadow-none" name="swim_email" id=""
                                                placeholder="Email" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Phone</small></label>
                                            <input type="tel" class="form-control py-2 shadow-none phone-input" name="swim_phone" id=""
                                                placeholder="Phone" required>
                                            <span class="error-div text-danger"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Address</small></label>
                                            <input type="text" class="form-control py-2 shadow-none" name="swim_address" id=""
                                                placeholder="Address" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Age</small></label>
                                            <input type="number" class="form-control py-2 shadow-none" name="swim_age" id=""
                                                placeholder="Age" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Sex</small></label>
                                            <select name="swim_sex" id="" class="form-select py-2 shadow-none" required>
                                                <option value="">Sex</option>
                                                <option value="male">Male</option>
                                                <option value="female">Female</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Height</small></label>
                                            <input type="number" class="form-control py-2 shadow-none" name="swim_height" id=""
                                                placeholder="Height" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Weight</small></label>
                                            <input type="number" class="form-control py-2 shadow-none" name="swim_weight" id=""
                                                placeholder="Weight" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-4">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Pulse Rate</small></label>
                                            <input type="number" class="form-control py-2 shadow-none" name="swim_pulse_rate" id=""
                                                placeholder="Pulse Rate" required>
                                        </div>
                                    </div>
                                    {{-- <div class="col-md-6 col-xl-4">
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
                                    </div> --}}
                                    <div class="col-md-6 col-xl-4">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Batch</small></label>
                                            <input type="text" class="form-control py-2 shadow-none" name="swim_batch" id=""
                                                placeholder="Batch" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-4">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Vaccination</small></label>
                                            <input type="text" class="form-control py-2 shadow-none" name="swim_vaccination" id=""
                                                placeholder="Vaccination" required>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" value="swim_i_agree" name="" id="flexCheck1" required>
                                            <label class="form-check-label" for="flexCheck1">
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
                                                    value="occational_faint" name="swim_disease[]">
                                                <label class="form-check-label" for="inlineCheckbox1"><small>Any sudden
                                                        or
                                                        occasional faint</small></label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" id="inlineCheckbox2"
                                                    value="lung_heart_trouble" name="swim_disease[]">
                                                <label class="form-check-label" for="inlineCheckbox2"><small>Lung/Heart
                                                        trouble</small></label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" id="inlineCheckbox3"
                                                    value="skin_disease" name="swim_disease[]">
                                                <label class="form-check-label" for="inlineCheckbox3"><small>Skin
                                                        disease</small></label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" id="inlineCheckbox4"
                                                    value="other_disease" name="swim_disease[]">
                                                <label class="form-check-label" for="inlineCheckbox4"><small>Any other
                                                        disease</small></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Photo</small></label>
                                            <label class="file-upload-box text-center border rounded-3 w-100 p-2">
                                                <input type="file" class="file-input opacity-0 position-absolute profile-image" name="swim_image" required>
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
                                            <span class="error-div text-danger"></span>
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
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="Father/Guardian’s Full Name" name="swim_guardian_name">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Father/Guardian’s Occupation</small></label>
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="Father/Guardian’s Occupation" name="swim_guardian_occupation">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Father/Guardian’s Photo</small></label>
                                            <label class="file-upload-box text-center border rounded-3 w-100 p-2">
                                                <input type="file" class="file-input opacity-0 position-absolute profile-image" name="swim_guardian_image" required>
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
                                    <div class="col-12">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100"><small>Plan type</small></label>
                                            @foreach ($membershipPlanList as $membershipPlan)
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="inlineRadioOptions2"
                                                        id="inlineRadio4" value="{{$membershipPlan->id}}">
                                                    <label class="form-check-label"
                                                        for="inlineRadio4"><small>{{$membershipPlan->name}}</small></label>
                                                </div>
                                            @endforeach
                                            {{-- <div class="form-check form-check-inline">
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
                                            </div> --}}
                                        </div>
                                    </div>
                                    {{-- <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <select name="" id="" class="form-select py-2 shadow-none">
                                                <option value="">Card Type</option>
                                                <option value="">Card Type1</option>
                                                <option value="">Card Type2</option>
                                            </select>
                                        </div>
                                    </div> --}}
                                    <div class="col-md-6 col-xl-4">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Card No.</small></label>
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="Card No.">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-4">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Card Mode</small></label>
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="Card Mode">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-4">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>A/C Head</small></label>
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="A/C Head">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Taxable Amt. (Min 500)</small></label>
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="Taxable Amt. (Min 500)">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>GST%</small></label>
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="GST%">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>GST Amt</small></label>
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="GST Amt">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-3">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Receipt Amt</small></label>
                                            <input type="text" class="form-control py-2 shadow-none" id=""
                                                placeholder="Receipt Amt">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Bank Name</small></label>
                                            <select name="" id="" class="form-select py-2 shadow-none">
                                                <option value="">Bank Name</option>
                                                <option value="">Bank Name 1</option>
                                                <option value="">Bank Name 2</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-part mb-3">
                                            <label for="" class="form-label w-100 mb-1 w-100"><small>Remarks</small></label>
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
                            <input type="submit" class="btn btn-primary fw-semibold" value="Add Swimming-membership" id="swim_submit">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- add Club Member Modal end  -->

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
                                <td class="pe-3"><small>Soumen Das</small></td>
                            </tr>
                            <tr>
                                <td class="text-secondary ps-3">
                                    <small>Club Name:</small>
                                </td>
                                <td class="pe-3"><small>Soumen Das</small></td>
                            </tr>
                            <tr>
                                <td class="text-secondary ps-3">
                                    <small>Member’s Id:</small>
                                </td>
                                <td class="pe-3"><small>GH231</small></td>
                            </tr>
                            <tr>
                                <td class="text-secondary ps-3">
                                    <small>Card No:</small>
                                </td>
                                <td class="pe-3"><small>12345abcd</small></td>
                            </tr>
                            <tr>
                                <td class="text-secondary ps-3">
                                    <small>Card No:</small>
                                </td>
                                <td class="pe-3"><small>12345abcd</small></td>
                            </tr>
                            <tr>
                                <td class="text-secondary ps-3">
                                    <small>Current Active Plan:</small>
                                </td>
                                <td class="pe-3"><small>Gold</small></td>
                            </tr>
                            <tr>
                                <td class="text-secondary ps-3">
                                    <small>Plan Expiry Date:</small>
                                </td>
                                <td class="pe-3"><small>12-31-2006</small></td>
                            </tr>
                            <tr>
                                <td class="text-secondary ps-3">
                                    <small>Current Wallet Balance:</small>
                                </td>
                                <td class="pe-3"><small>₹2,450.00</small></td>
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
                            <h5 class="card-title fs-4 fw-semibold text-white mb-0">₹2,450.00</h5>
                        </div>
                    </div>
                    <form action="">
                        <div class="row">
                            <div class="col-12">
                                <div class="input-group mb-3">
                                    <span
                                        class="input-group-text bg-transparent border-end-0 bg-white"><small>₹</small></span>
                                    <input id="amountInput" type="text" class="form-control border-start-0 shadow-none"
                                        aria-label="Amount" placeholder="0">
                                </div>
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
                                                for="inlineRadio1"><small>₹ 1000</small></label>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <div class="form-check position-relative p-0 custom-radio mb-3">
                                            <input
                                                class="form-check-input m-0 w-100 h-100 position-absolute top-0 start-0 rounded-0 opacity-0"
                                                type="radio" name="inlineRadioOptions" id="inlineRadio2"
                                                value="option1">
                                            <label class="form-check-label w-100 border p-2 rounded-3"
                                                for="inlineRadio2"><small>₹ 2000</small></label>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <div class="form-check position-relative p-0 custom-radio mb-3">
                                            <input
                                                class="form-check-input m-0 w-100 h-100 position-absolute top-0 start-0 rounded-0 opacity-0"
                                                type="radio" name="inlineRadioOptions" id="inlineRadio3"
                                                value="option1">
                                            <label class="form-check-label w-100 border p-2 rounded-3"
                                                for="inlineRadio3"><small>₹ 800</small></label>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-md-6">
                                        <div class="form-check position-relative p-0 custom-radio mb-3">
                                            <input
                                                class="form-check-input m-0 w-100 h-100 position-absolute top-0 start-0 rounded-0 opacity-0"
                                                type="radio" name="inlineRadioOptions" id="inlineRadio4"
                                                value="option1">
                                            <label class="form-check-label w-100 border p-2 rounded-3"
                                                for="inlineRadio4"><small>₹ 9000</small></label>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="text-end">
                            <button class="btn btn-primary fw-semibold">Recharge Wallet</button>
                        </div>
                    </form>
                    <div class="d-flex justify-content-between align-items-center gap-3 my-4">
                        <div class="form-label fw-semibold text-dark mb-3"><span
                                class="text-info rounded-3 label-icon p-1 d-inline-flex align-items-center justify-content-center me-2"><i
                                    class="fa-solid fa-wallet"></i></span> Recent Transactions</div>
                        <span><a href="#" class="text-dark"><i class="fa-regular fa-calendar fs-4"></i></a></span>
                    </div>
                    <div class="bg-light p-2">
                        <table class="table border-0 m-0 wallet-table">
                            <tbody>
                                <tr>
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
                                        <small class="fw-semibold">Usd</small> <br>
                                        <small class="text-black-50">Today, 2:30 PM</small>
                                    </td>
                                    <td class="text-danger text-end border-secondary bg-transparent align-middle">+₹4000
                                    </td>
                                </tr>
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
                            <tbody>
                                <tr class="active-member">
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
                                </tr>
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

        //name validation
        $('.text-only').on('input', function () {
            this.value = this.value.replace(/[^A-Za-z\s]/g, '');
        });

        //phone no validation
        $('.phone-input').on('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 10);
        });

        $('#swimmingMemberForm').on('submit', function (e) {
            e.preventDefault();

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
                    let maxSize = 2 * 1024 * 1024; // 2MB

                    let errors = [];

                    if (!allowedTypes.includes(file.type)) {
                        errors.push('Only JPG, JPEG and PNG images are allowed.');
                    }

                    if (file.size > maxSize) {
                        errors.push('Image must be less than 2MB.');
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

            if (!isValid) {
                return isValid;
            }

            let swimmingMemberformData = new FormData($("#swimmingMemberForm")[0]);
            $.ajax({
                url: "{{ route('swimming-member.store') }}",
                type: "POST",
                data: swimmingMemberformData,
                processData: false,
                contentType: false,
                // data:{
                // "_token": "{{ csrf_token() }}",
                // "balance": balance,
                // "user_id": userId
                // },
                success: function(response) {
                    console.log(response);
                    // if (response.statusCode == 200) {
                    //     toastr.success("Balance added successfully.");
                    //     document.activeElement.blur();//removes focus from modal
                    //     $('#kt_modal_create_api_key').modal('hide');
                    //     $("#balanceAmount").val("");
                    //     setTimeout(function() {
                    //         location.reload();
                    //     }, 1500);
                    // } else {
                    //     if(response.message){
                    //         toastr.error(response.message);
                    //     }
                    //     else{
                    //         toastr.error("Something went wrong, Please try again.");
                    //     }
                    // }
                },
                error: function(xhr, status, error) {
                    // Handle errors
                    console.error(xhr.responseText);
                }
            });

        });

    });
</script>
@endsection
