@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="member-list-part position-relative">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2 flex-wrap">
                    <h2 class="fs-5 common-heading mb-md-0 fw-semibold">Menu list</h2>
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
                        <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addfooditem">+
                            Add items</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table rounded-3 overflow-hidden clubmemberlist2" cellspacing="0"
                        width="100%">
                        <thead>
                            <tr>
                                <th class="text-white fw-medium text-nowrap">Sl. No.</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Name</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Code</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Category
                                </th>
                                <th class="text-white fw-medium align-middle text-nowrap">Image</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Satus</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Price</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($foodItemsList as $items)
                                <tr>
                                    <td class="text-nowrap">{{ $loop->iteration }}</td>
                                    <td class="text-nowrap">{{ $items->name }}</td>
                                    <td class="text-nowrap">{{ $items->code }}</td>
                                    <td class="text-nowrap">{{ $items->foodItemCat->name }}</td>
                                    <td class="text-nowrap"><img src="{{ $items->image }}"
                                            class="rounded-circle" alt="" loading="lazy" fetchpriority="auto"
                                            width="64" height="64"></td>
                                    <td class="text-success text-nowrap">
                                        @if(($items->is_active == 1))
                                            <span class="text-success text-nowrap">
                                                Active
                                            </span>
                                        @else
                                            <span class="text-secondary text-nowrap">
                                                Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">&#8377;{{ $items->foodItemPrice->price ?? '0' }}</td>
                                    <td class="text-nowrap">
                                        <button class="border-0 bg-light p-1 rounded-3 lh-1 action-btn editFoodItem" data-id="{{ $items->id }}"
                                            title="Edit">
                                            <small>
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </small>
                                        </button>
                                        <button
                                            class="border-0 bg-light p-1 rounded-3 lh-1 action-btn delete-row"
                                            data-id="{{ $items->id }}"
                                            title="Delete">
                                            <small><i class="fa-solid fa-trash"></i></small>
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
    <!-- Add Food Item Modal Start-->
    <div class="modal fade" id="addfooditem" tabindex="-1" aria-labelledby="addfooditemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fs-5 fw-semibold" id="addfooditemModalLabel">Add Food Item</h5>
                    <button type="button" class="btn-close bg-transparent fs-5 lh-1" data-bs-dismiss="modal"
                        aria-label="Close"><i class="fa-regular fa-circle-xmark"></i></button>
                </div>
                <div class="modal-body">
                    <form action="" id="foodItemForm" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <div class="form-label fw-semibold text-dark mb-3">
                                        <span class="text-info rounded-3 label-icon p-1 d-inline-flex align-items-center justify-content-center me-2">
                                            <i class="fa-regular fa-user"></i>
                                        </span> Item Details
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-part mb-3">
                                    <label for="" class="form-label"><small>Item Name</small></label>
                                    <input type="text" name="itemName" id="itemName" class="form-control py-2 shadow-none text-only" placeholder="Item Name"  value="{{ old('itemName') }}" required>
                                    <div class="error-div text-danger small"></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3 form-part">
                                    <label for="" class="form-label"><small>Category Name</small></label>
                                    <select name="itemCat" id="itemCat" class="form-select py-2 shadow-none" required>
                                        <option value="" selected="" hidden="" disabled="">Select Food Category
                                        </option>
                                        @foreach($foodCatList as $foodCat)
                                            <option value="{{ $foodCat->id }}" {{ old('itemCat') == $foodCat->id ? 'selected' : '' }}>
                                                {{ $foodCat->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <div class="error-div text-danger small"></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-part mb-3">
                                    <label for="" class="form-label"><small>Item Image</small></label>
                                    <label class="file-upload-box position-relative text-center border rounded-3 w-100 p-2">
                                    <input type="file" name="itemImage" id="itemImage" class="file-input opacity-0 position-absolute start-0 w-100 item-image" placeholder="Item Image" required>
                                    <div class="upload-content">
                                        <i class="upload-icon"><i
                                                class="fa-solid fa-arrow-up-from-bracket"></i></i>
                                        <small class="text-muted">
                                            Image format, PNG & JPEG, max file size 5MB
                                        </small>
                                    </div>
                                    <div class="error-div text-danger small"></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-part mb-3">
                                    <label for="" class="form-label"><small>Item Price</small></label>
                                    <input type="number" name="itemPrice" id="itemPrice" class="form-control py-2 shadow-none" placeholder="Item Price" value="{{ old('itemPrice') }}" min="0" max="9999999999" step="0.01" required>
                                    <div class="error-div text-danger small"></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-part mb-3">
                                    <label for="" class="form-label"><small>Item Code</small></label>
                                    <input type="text" name="itemCode" id="itemCode" class="form-control py-2 shadow-none " placeholder="Item Code"  value="{{ old('itemCode') }}" required>
                                    <div class="error-div text-danger small"></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-part mb-3">
                                    <label for="" class="form-label w-100 mb-1 w-100"><small>Select Status</small></label>
                                    <select name="itemstatus" id="itemstatus" class="form-select py-2 shadow-none" required>
                                        <option value="" selected="" hidden="" disabled="">Select Status
                                        </option>

                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>

                                    <div class="error-div text-danger small"></div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="text-end mod-footer">
                                    <button type="submit" class="btn btn-primary fw-semibold" value="submit" id="foodItem_submit">Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Add Food Item Modal End-->

    <!-- Edit Food Item Modal Start-->
    <div class="modal fade" id="editfooditem" tabindex="-1" aria-labelledby="addfooditemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fs-5 fw-semibold" id="addfooditemModalLabel">Edit Food Item</h5>
                    <button type="button" class="btn-close bg-transparent fs-5 lh-1" data-bs-dismiss="modal"
                        aria-label="Close"><i class="fa-regular fa-circle-xmark"></i></button>
                </div>
                <div class="modal-body">
                    <form action="" id="editfoodItemForm" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="edit_item_id" id="edit_item_id">

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <div class="form-label fw-semibold text-dark mb-3">
                                        <span class="text-info rounded-3 label-icon p-1 d-inline-flex align-items-center justify-content-center me-2">
                                            <i class="fa-regular fa-user"></i>
                                        </span> Item Details
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-part mb-3">
                                    <label for="" class="form-label"><small>Item Name</small></label>
                                    <input type="text" name="itemName" id="edit_itemName" class="form-control py-2 shadow-none text-only" placeholder="Item Name"  required>
                                    <div class="error-div text-danger small"></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3 form-part">
                                    <label for="" class="form-label"><small>Category Name</small></label>
                                    <select name="itemCat" id="edit_itemCat" class="form-select py-2 shadow-none" required>
                                        <option value="" selected="" hidden="" disabled="">Select Food Category
                                        </option>
                                        @foreach($foodCatList as $foodCat)
                                            <option value="{{ $foodCat->id }}">{{ $foodCat->name }}</option>
                                        @endforeach
                                    </select>

                                    <div class="error-div text-danger small"></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-part mb-3">
                                    <label for="" class="form-label"><small>Item Image</small></label>

                                    <label class="file-upload-box position-relative text-center border rounded-3 w-100 p-2">

                                    <input type="file" name="itemImage" id="edit_itemImage" class="file-input opacity-0 position-absolute start-0 w-100 item-image-edit" placeholder="Item Image">

                                    <div class="upload-content" id="edit_uploadContent">
                                        <i class="upload-icon">
                                            <i class="fa-solid fa-arrow-up-from-bracket"></i>
                                        </i>
                                        <small class="text-muted">
                                            Image format, PNG & JPEG, max file size 5MB
                                        </small>
                                    </div>

                                    <div class="mt-2">
                                        <img id="edit_itemPreview" src="" width="80" class="rounded d-none">
                                    </div>

                                    </label>

                                    <div class="error-div text-danger small"></div>


                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-part mb-3">
                                    <label for="" class="form-label"><small>Item Code</small></label>
                                    <input type="text" name="itemCode" id="edit_itemCode" class="form-control py-2 shadow-none " placeholder="Item Code"   required>
                                    <div class="error-div text-danger small"></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-part mb-3">
                                    <label for="" class="form-label w-100 mb-1 w-100"><small>Select Status</small></label>
                                    <select name="itemstatus" id="edit_itemstatus" class="form-select py-2 shadow-none" required>
                                        <option value="" selected="" hidden="" disabled="">Select Status
                                        </option>

                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>

                                    <div class="error-div text-danger small"></div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="text-end mod-footer">
                                    <input type="submit" class="btn btn-primary fw-semibold" value="Update" id="editFoodItem_submit">
                                </div>
                            </div>
                        </div>
                    </form>
                    <form id="">
                        <div class="col-12">
                            <div class="mb-3">
                                <div class="form-label fw-semibold text-dark mb-3"><span
                                        class="text-info rounded-3 label-icon p-1 d-inline-flex align-items-center justify-content-center me-2"><i
                                            class="fa-regular fa-user"></i></span> Price Manage
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-part mb-3 d-flex align-items-center gap-2 mod-footer">

                                <label for="" class="form-label"><small>Current Price</small></label>

                                <input type="hidden" id="price_item_id" name="item_id">

                                <input type="number" name="itemPrice" id="edit_itemPrice" class="form-control py-2 shadow-none" placeholder="Current price" readonly>

                                <div class="error-div text-danger small"></div>

                                <button type="button" class="btn btn-info fw-semibold changePriceBtn" data-bs-toggle="modal" data-bs-target="#changeprice">Change Price</button>
                            </div>
                        </div>
                        <div class="col-12">
                            <div id="pendingPriceMessage" class="mb-3 d-flex align-items-center gap-2 justify-content-between text-warning fw-semibold" style="display:none;">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Edit Food Item Modal End-->

    <!-- Change Price Modal -->
    <div class="modal fade" id="changeprice" tabindex="-1" aria-labelledby="changepriceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fs-5 fw-semibold" id="changepriceModalLabel">Edit Food Price</h5>
                    <button type="button" class="btn-close bg-transparent fs-5 lh-1" data-bs-dismiss="modal"
                        aria-label="Close"><i class="fa-regular fa-circle-xmark"></i></button>
                </div>
                <div class="modal-body">
                    <form id="changePriceForm">
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <div class="form-label fw-semibold text-dark mb-3">
                                        <span class="text-info rounded-3 label-icon p-1 d-inline-flex align-items-center justify-content-center me-2">
                                            <i class="fa-regular fa-user"></i>
                                        </span>
                                        Change Item Price
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-part mb-3">
                                    <label for="" class="form-label"><small>New Price</small></label>
                                    <input type="number" name="newPrice" id="newPrice"  class="form-control py-2 shadow-none" placeholder="New Price"  min="0" max="9999999999" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="text-end mod-footer">
                                    <input type="submit" class="btn btn-primary fw-semibold" value="Submit for approval" >
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Change Price Modal End-->

    <!-- Delete row table Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-3">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to delete this item?</p>
                </div>

                <input type="hidden" id="delete_food_id" value="">

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
    <!-- Delete row table Modal End-->
@endsection

@section('customJS')
    <script>
        $(document).ready(function(){

            $('.text-only').on('input', function() {
                this.value = this.value.replace(/[^A-Za-z\s]/g, '');
            });


            $('#foodItemForm').on('submit', function(e){
                e.preventDefault();
                const $btn = $('#foodItem_submit');
                const originalText = $btn.html();


                let isValid = true;

                //Item Name validation
                let itemName  = $('#itemName').val();
                let nameError = $('#itemName').next('.error-div');

                if(itemName == ''){
                    nameError.text('Item name is required');
                    $('#itemName').addClass('is-invalid');
                    isValid = false;
                }
                else{
                    nameError.text('');
                    $('#itemName').removeClass('is-invalid');
                }

                // CATEGORY VALIDATION
                let itemCat  = $('#itemCat').val();
                let catError = $('#itemCat').next('.error-div');

                if( itemCat == null){
                    catError.text('Please select category');
                    $('#itemCat').addClass('is-invalid');
                    isValid = false;
                }
                else{
                    catError.text('');
                    $('#itemCat').removeClass('is-invalid');
                }

                // IMAGE VALIDATION
                $('.item-image').each(function(){

                    let fileInput  = this;
                    let errorDiv   = $(this).closest('.form-part').find('.error-div');
                    let errors = [];

                    if(fileInput.files.length == 0){
                        errors.push('Image is required');
                    }
                    else {

                        let file = fileInput.files[0];
                        let allowedtypes = ['image/jpeg','image/png'];
                        let maxSize = 5 * 1024 * 1024;

                        if(!allowedtypes.includes(file.type)){
                            errors.push('Only JPG, JPEG and PNG images are allowed.');
                        }

                        if(file.size > maxSize){
                            errors.push('Image must be less than 5MB.');
                        }
                    }

                    if(errors.length > 0){
                        isValid = false;
                        errorDiv.html(errors.join('<br>'));
                        $(this).addClass('is-invalid');
                    }
                    else{
                        errorDiv.text('');
                        $(this).removeClass('is-invalid');
                    }

                });

                //PRICE VALIDATION
                let itemPrice  = $('#itemPrice').val();
                let priceError = $('#itemPrice').next('.error-div');

                if(itemPrice == '' || itemPrice <= 0){

                    priceError.text('Enter valid price');
                    $('#itemPrice').addClass('is-invalid');
                    isValid = false;
                }

                else{
                    priceError.text('');
                    $('#itemPrice').removeClass('is-invalid');
                }

                //Item Code validation
                let itemCode  = $('#itemCode').val();
                let codeError = $('#itemCode').next('.error-div');

                if(itemCode == ''){
                    codeError.text('Item code is required');
                    $('#itemCode').addClass('is-invalid');
                    isValid = false;
                }
                else{
                    codeError.text('');
                    $('#itemCode').removeClass('is-invalid');
                }

                //Item Status validation
                let itemstatus  = $('#itemstatus').val();
                let statusError = $('#itemstatus').next('.error-div');

                if(itemstatus == ''){
                    statusError.text('Status is required');
                    $('#itemstatus').addClass('is-invalid');
                    isValid = false;
                }
                else{
                    statusError.text('');
                    $('#itemstatus').removeClass('is-invalid');
                }

                if(!isValid){
                    return ;
                }

                $btn.prop('disabled', true);
                $btn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...');


                //AJAX SUBMIT
                let formData = new FormData($('#foodItemForm')[0]);

                $.ajax({
                    url: "{{ route('manage-food-items.store') }}",

                    type: "POST",

                    data: formData,
                    processData: false,
                    contentType: false,

                    success:function(response){

                        if(response.statusCode == 200){
                            $btn.html(originalText);
                            toastr.success(response.message);

                            $('#foodItemForm')[0].reset();

                            setTimeout(() =>location.reload(),1500);

                        }
                        else{
                            $btn.html(originalText);
                            $btn.prop('disabled', false);

                            if(response.error){
                                toastr.error(response.error);
                            }
                            else{
                                toastr.error("Something went wrong. Please try again.");
                                //console.log(response);
                            }
                        }
                    },
                    error:function(xhr, status, error){

                        $btn.html(originalText);
                        $btn.prop('disabled', false);
                        toastr.error("Something went wrong, Please try again.");

                        let responseError = xhr.responseJSON?.error
                            ?? "Something went wrong, Please try again.";
                        console.error(responseError);

                    }
                });
            });

            $(document).on('click','.editFoodItem',function(){

                let id = $(this).data('id');

                // RESET pending message and button state
                $('#pendingPriceMessage').hide().text('');
                let btn = $('.changePriceBtn');

                btn.prop('disabled', false).removeClass('disabled').css({
                    'opacity': '',
                    'background-color': '',
                    'border-color': '',
                    'color': '',
                    'cursor': ''
                });

                $.ajax({
                    url: "/manage-food-items/" + id + "/edit",
                    type: "GET",

                    success:function(response){
                        let data = response.data;

                        $('#edit_item_id').val(data.id);
                        $('#price_item_id').val(data.id);

                        $('#edit_itemName').val(data.name);
                        $('#edit_itemCat').val(data.category_id);
                        $('#edit_itemPrice').val(data.food_item_price?.price);
                        $('#edit_itemCode').val(data.code);
                        $('#edit_itemstatus').val(data.is_active);

                         // Show current image
                        if(data.image){
                            $('#edit_itemPreview')
                                .attr('src', '/' + data.image)
                                .removeClass('d-none');
                        }

                        // CHECK PENDING PRICE REQUEST
                        if(response.pendingApproval && response.pendingApproval.request_payload){

                            let payload = response.pendingApproval.request_payload;

                            if (typeof payload === "string") {
                                payload = JSON.parse(payload);
                            }

                            let message =
                                `Price change request is pending : ₹${payload.old_price} → ₹${payload.new_price} awaiting approval`;

                            $('#pendingPriceMessage')
                                .text(message)
                                .show();

                            let btn = $('.changePriceBtn');

                            let originalStyles = {
                                background: btn.css('background-color'),
                                border: btn.css('border-color'),
                                color: btn.css('color')
                            };

                            btn.prop('disabled', true).css({
                                'opacity': '1',
                                'background-color': originalStyles.background,
                                'border-color': originalStyles.border,
                                'color': originalStyles.color,
                                'cursor': 'not-allowed'
                            });

                        } else {

                            $('#pendingPriceMessage').hide();
                            $('.changePriceBtn')
                                .prop('disabled', false)
                                .removeClass('disabled');
                        }

                        $('#editfooditem').modal('show');
                    }
                });

            });

            $('#editfoodItemForm').on('submit', function(e){

                e.preventDefault();

                let id = $('#edit_item_id').val();

                let formData = new FormData(this);

                $.ajax({

                    url: "/manage-food-items/"+id,

                    type: "POST",   // Laravel PUT via method spoofing

                    data: formData,

                    processData:false,
                    contentType:false,

                    success:function(response){

                        if(response.statusCode == 200){

                            toastr.success(response.message);

                            $('#editfoodItemForm')[0].reset();

                            $('#editfooditem').modal('hide');

                            setTimeout(function(){
                                location.reload();
                            },1500);

                        }
                        else{

                            toastr.error(response.error);
                        }

                    },

                    error:function(xhr){

                        console.log(xhr.responseText);

                        toastr.error("Something went wrong");

                    }

                });

            });

            $('#edit_itemImage').on('change', function(){

                let reader = new FileReader();

                reader.onload = function(e){

                    $('#edit_itemPreview')
                        .attr('src', e.target.result)
                        .removeClass('d-none');

                    $('#edit_uploadContent').hide();
                };

                reader.readAsDataURL(this.files[0]);

            });

            $('#changePriceForm').on('submit', function(e){
                e.preventDefault();

                let formData = {
                    item_id: $('#price_item_id').val(),
                    new_price: $('#newPrice').val(),
                    _token: "{{ csrf_token() }}"
                };

                $.ajax({
                    url: "{{ route('foodItemPriceApproval.request') }}",

                    type: "POST",

                    data: formData,

                    success:function(response){

                        if(response.statusCode == 200){

                            toastr.success(response.message);

                            $('#changeprice').modal('hide');

                            setTimeout(function(){
                                location.reload();
                            },1500);

                        }
                        else{
                            toastr.error(response.error);
                        }
                },

                error:function(){
                    toastr.error("Something went wrong");
                }
                });
            });

            $(document).on("click", ".delete-row", function () {

                let foodId = $(this).data("id");

                $('#delete_food_id').val(foodId);

                $('#deleteConfirmModal').modal('show');

            });

            $('#confirmDeleteBtn').click(function(){

                let foodId = $("#delete_food_id").val();

                $.ajax({

                    url: "/manage-food-items/" + foodId,

                    type: "DELETE",

                    data: {
                        _token: "{{ csrf_token() }}"
                    },

                    success:function(response){

                        if(response.statusCode == 200){

                            toastr.success(response.message);

                            $('#deleteConfirmModal').modal('hide');

                            setTimeout(function(){
                                location.reload();
                            },1000);

                        } else {

                            toastr.error(response.message || response.error);

                        }

                    },

                    error:function(xhr){

                        toastr.error("Delete failed");

                        console.log(xhr.responseText);

                    }

                });

            });


        });
    </script>

@endsection
