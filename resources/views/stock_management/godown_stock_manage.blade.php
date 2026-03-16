@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')
    

    <div class="repeat-holder">
        <div class="box-grid">
            <a href="#"
                class="card text-white bg-info border-0 d-flex justify-content-between flex-row-reverse">
                <div class="card-header bg-transparent border-0"><i class="fa-solid fa-wine-bottle"></i>
                </div>
                <div class="card-body">
                    <p class="card-text mb-2 fw-medium">Top Selling Liquor</p>
                    <h2 class="card-title fs-4">Beer</h2>
                </div>
            </a>
            <a href="#"
                class="card text-white bg-success border-0 d-flex justify-content-between flex-row-reverse">
                <div class="card-header bg-transparent border-0"><i
                        class="fa-solid fa-indian-rupee-sign"></i>
                </div>
                <div class="card-body">
                    <p class="card-text mb-2 fw-medium">Total Selling</p>
                    <h2 class="card-title fs-4">₹80,000</h2>
                </div>
            </a>
            <a href="#"
                class="card text-white bg-secondary border-0 d-flex justify-content-between flex-row-reverse">
                <div class="card-header bg-transparent border-0"><i class="fa-solid fa-wine-bottle"></i>
                </div>
                <div class="card-body">
                    <p class="card-text mb-2 fw-medium">Low Stock Items</p>
                    <h2 class="card-title fs-4">10</h2>
                </div>
            </a>
            <a href="#"
                class="card text-white bg-warning border-0 d-flex justify-content-between flex-row-reverse">
                <div class="card-header bg-transparent border-0"><i
                        class="fa-solid fa-indian-rupee-sign"></i></div>
                <div class="card-body">
                    <p class="card-text mb-2 fw-medium">Today’s Sale</p>
                    <h2 class="card-title fs-4">₹1,00,000</h2>
                </div>
            </a>
        </div>
    </div>
    <div class="repeat-holder mt-5">
        <form action="">
            <div class="row">
                <div class="col-lg-6">
                    <h2 class="fs-5 common-heading mb-3 fw-semibold">Filter by date</h2>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3 form-part">
                                <input type="text" class="form-control py-2 shadow-none" placeholder="Form"
                                    onfocus="(this.type='date')">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3 form-part">
                                <input type="text" class="form-control py-2 shadow-none" placeholder="To"
                                    onfocus="(this.type='date')">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <h2 class="fs-5 common-heading mb-3 fw-semibold">Filter by Name</h2>
                    <div class="mb-3 form-part">
                        <select class="form-select multi-select py-2 shadow-none" single>
                            <option value="1" selected="" disabled="" hidden="">All</option>
                            <option value="1">Veg</option>
                            <option value="2">Nonveg</option>
                            <option value="3">Drinks</option>
                        </select>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="repeat-holder mt-5">
        <div class="row">
            <div class="col-12">
                <div class="member-list-part my-xl-0 my-2 position-relative">
                    <div
                        class="d-flex flex-wrap align-items-center justify-content-between gap-2 gap-lg-3 mb-2">
                        <h2 class="fs-5 common-heading mb-md-0 fw-semibold">Stock Overview</h2>
                        <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addinventory">+
                            Add inventory</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table rounded-3 overflow-hidden clubmemberlist2" cellspacing="0"
                            width="100%">
                            <thead>
                                <tr>
                                    <th class="text-white fw-medium text-nowrap">Date</th>
                                    <th class="text-white fw-medium text-nowrap">Category</th>
                                    <th class="text-white fw-medium text-nowrap">Item</th>
                                    <th class="text-white fw-medium text-nowrap">Volume</th>
                                    <th class="text-white fw-medium text-nowrap">Movement</th>
                                    <th class="text-white fw-medium text-nowrap">Direction</th>
                                    {{-- <th class="text-white fw-medium text-nowrap">Action</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($godownStockList as $stock)
                                    <tr>
                                        <td class="text-nowrap">{{ $stock->date }}</td>
                                        <td class="text-nowrap">{{ $stock->category }}</td>
                                        <td class="text-nowrap">{{ $stock->item }}</td>
                                        <td class="text-nowrap">{{ $stock->volume }}</td>
                                        <td class="text-nowrap">{{ $stock->movement_type }}</td>
                                        <td class="text-nowrap">{{ $stock->direction }}</td>
                                        {{-- <td class="text-nowrap"><a href="#"><i
                                                    class="fa-solid fa-pen-to-square"></i></a></td> --}}
                                    </tr>
                                @endforeach
                                {{-- <tr>
                                    <td class="text-nowrap">13-02-2026</td>
                                    <td class="text-nowrap">Johnnie walker</td>
                                    <td class="text-nowrap">Scotch Whiskey</td>
                                    <td class="text-nowrap">750 ml.</td>
                                    <td class="text-nowrap">Perches</td>
                                    <td class="text-nowrap">Out</td>
                                    <td class="text-nowrap"><a href="#"><i
                                                class="fa-solid fa-pen-to-square"></i></a></td>
                                </tr>
                                <tr>
                                    <td class="text-nowrap">13-02-2026</td>
                                    <td class="text-nowrap">Rum</td>
                                    <td class="text-nowrap">Old Monk</td>
                                    <td class="text-nowrap">750 ml.</td>
                                    <td class="text-nowrap">Perches</td>
                                    <td class="text-nowrap">In</td>
                                    <td class="text-nowrap"><a href="#"><i
                                                class="fa-solid fa-pen-to-square"></i></a></td>
                                </tr>
                                <tr>
                                    <td class="text-nowrap">13-02-2026</td>
                                    <td class="text-nowrap">Rum</td>
                                    <td class="text-nowrap">Old Monk</td>
                                    <td class="text-nowrap">750 ml.</td>
                                    <td class="text-nowrap">Perches</td>
                                    <td class="text-nowrap">Out</td>
                                    <td class="text-nowrap"><a href="#"><i
                                                class="fa-solid fa-pen-to-square"></i></a></td>
                                </tr>
                                <tr>
                                    <td class="text-nowrap">13-02-2026</td>
                                    <td class="text-nowrap">Rum</td>
                                    <td class="text-nowrap">Old Monk</td>
                                    <td class="text-nowrap">750 ml.</td>
                                    <td class="text-nowrap">Perches</td>
                                    <td class="text-nowrap">In</td>
                                    <td class="text-nowrap"><a href="#"><i
                                                class="fa-solid fa-pen-to-square"></i></a></td>
                                </tr>
                                <tr>
                                    <td class="text-nowrap">13-02-2026</td>
                                    <td class="text-nowrap">Rum</td>
                                    <td class="text-nowrap">Old Monk</td>
                                    <td class="text-nowrap">750 ml.</td>
                                    <td class="text-nowrap">Perches</td>
                                    <td class="text-nowrap">In</td>
                                    <td class="text-nowrap"><a href="#"><i
                                                class="fa-solid fa-pen-to-square"></i></a></td>
                                </tr>
                                <tr>
                                    <td class="text-nowrap">13-02-2026</td>
                                    <td class="text-nowrap">Rum</td>
                                    <td class="text-nowrap">Old Monk</td>
                                    <td class="text-nowrap">750 ml.</td>
                                    <td class="text-nowrap">Perches</td>
                                    <td class="text-nowrap">In</td>
                                    <td class="text-nowrap"><a href="#"><i
                                                class="fa-solid fa-pen-to-square"></i></a></td>
                                </tr>
                                <tr>
                                    <td class="text-nowrap">13-02-2026</td>
                                    <td class="text-nowrap">Rum</td>
                                    <td class="text-nowrap">Old Monk</td>
                                    <td class="text-nowrap">750 ml.</td>
                                    <td class="text-nowrap">Perches</td>
                                    <td class="text-nowrap">In</td>
                                    <td class="text-nowrap"><a href="#"><i
                                                class="fa-solid fa-pen-to-square"></i></a></td>
                                </tr>
                                <tr>
                                    <td class="text-nowrap">13-02-2026</td>
                                    <td class="text-nowrap">Rum</td>
                                    <td class="text-nowrap">Old Monk</td>
                                    <td class="text-nowrap">750 ml.</td>
                                    <td class="text-nowrap">Perches</td>
                                    <td class="text-nowrap">In</td>
                                    <td class="text-nowrap"><a href="#"><i
                                                class="fa-solid fa-pen-to-square"></i></a></td>
                                </tr>
                                <tr>
                                    <td class="text-nowrap">13-02-2026</td>
                                    <td class="text-nowrap">Rum</td>
                                    <td class="text-nowrap">Old Monk</td>
                                    <td class="text-nowrap">750 ml.</td>
                                    <td class="text-nowrap">Perches</td>
                                    <td class="text-nowrap">In</td>
                                    <td class="text-nowrap"><a href="#"><i
                                                class="fa-solid fa-pen-to-square"></i></a></td>
                                </tr>
                                <tr>
                                    <td class="text-nowrap">13-02-2026</td>
                                    <td class="text-nowrap">Rum</td>
                                    <td class="text-nowrap">Old Monk</td>
                                    <td class="text-nowrap">750 ml.</td>
                                    <td class="text-nowrap">Perches</td>
                                    <td class="text-nowrap">In</td>
                                    <td class="text-nowrap"><a href="#"><i
                                                class="fa-solid fa-pen-to-square"></i></a></td>
                                </tr> --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modalComponent')
    <!-- add Inventory Modal start -->
    <div class="modal fade" id="addinventory" tabindex="-1" aria-labelledby="addinventoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h2 class="modal-title fs-5 fw-semibold" id="addinventoryModalLabel">Add inventory</h2>
                    <button type="button" class="btn-close bg-transparent fs-5 lh-1" data-bs-dismiss="modal"
                        aria-label="Close"><i class="fa-regular fa-circle-xmark"></i></button>
                </div>
                <div class="modal-body">
                    <form action="" id="addInventoryForm" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3 form-part">
                                    {{-- <select class="form-select py-2 shadow-none multi-select" multiple>
                                        @foreach ($liquors as $liquor)
                                            <option value="{{ $liquor->id }}">{{ $liquor->name }}</option>
                                        @endforeach
                                    </select> --}}
                                    <label class="form-label" for="liquor_id">Liquor</label>
                                    <select class="form-select py-2 shadow-none" name="liquor_id" id="liquor_id">
                                        <option value="" selected disabled>Select Liquor</option>
                                        @foreach ($liquors as $liquor)
                                            <option value="{{ $liquor->id }}">{{ $liquor->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3 form-part">
                                    <label class="form-label" for="movement">Movement</label>
                                    <select class="form-select py-2 shadow-none" name="movement" id="movement">
                                        <option value="" selected disabled>Select Movement</option>
                                        <option value="purchase">Purchase</option>
                                        <option value="sale">Sale</option>
                                        <option value="adjustment">Adjustment</option>
                                        <option value="wastage">Wastage</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3 form-part">
                                    <label class="form-label" for="direction">Direction</label>
                                    <select class="form-select py-2 shadow-none" name="direction" id="direction">
                                        <option value="" selected disabled>Select Direction</option>
                                        <option value="in">In</option>
                                        <option value="out">Out</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3 form-part">
                                    <label class="form-label" for="quantity">Quantity</label>
                                    <input type="number" class="form-control py-2 shadow-none" name="quantity" id="quantity">
                                </div>
                            </div>
                        </div>
                        <div class="text-end mod-footer mt-3">
                            <button type="button" class="btn btn-info fw-semibold"
                                data-bs-dismiss="modal">Cancel</button>
                            <input type="submit" class="btn btn-primary fw-semibold" value="Submit" id="addInventorySubmit">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- add Inventory Modal end  -->
@endsection

@section('customJS')
    <script>
        $(document).ready(function(){
            $('#addInventoryForm').submit(function(e){
                e.preventDefault();
                $('#addInventorySubmit').prop('disabled', true);
                $('#addInventorySubmit').val('Submitting...');
                var formData = $(this).serialize();
                $.ajax({
                    url: "{{ route('godown-stock-manage.store') }}",
                    type: "POST",
                    data: formData,
                    success: function(response){
                        console.log(response);
                        if(response.statusCode == 200){
                            $('#addinventory').modal('hide');
                            $('#addInventoryForm')[0].reset();
                            toastr.success(response.message);
                            $('#addInventorySubmit').prop('disabled', false);
                            $('#addInventorySubmit').val('Submit');
                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        }else{
                            toastr.error(response.message);
                            $('#addInventorySubmit').prop('disabled', false);
                            $('#addInventorySubmit').val('Submit');
                        }
                    },
                    error: function(xhr, status, error){
                        console.log(error);
                        toastr.error('Something went wrong');
                        $('#addInventorySubmit').prop('disabled', false);
                        $('#addInventorySubmit').val('Submit');
                    }
                });
            });
            


        });
    </script>

@endsection
