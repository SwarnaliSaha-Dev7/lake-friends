@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')
    

    <div class="repeat-holder">
        <form action="" id="filterForm">
            <div class="row">
                <div class="col-lg-6">
                    <h2 class="fs-5 common-heading mb-3 fw-semibold">Filter by</h2>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3 form-part">
                                <select name="item_name" id="item_name" class="form-select py-2 shadow-none">
                                    <option value="" selected>Name</option>
                                    @foreach ($itemNames as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3 form-part">
                                <select name="item_category" id="item_category" class="form-select py-2 shadow-none">
                                    <option value="" selected>Category</option>
                                    @foreach ($itemCategories as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
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
                        <h2 class="fs-5 common-heading mb-md-0 fw-semibold">Item & Category Overview</h2>
                        <!-- <button class="btn btn-info" >+
                            Add inventory</button> -->
                    </div>
                    <div class="table-responsive">
                        <table class="table rounded-3 overflow-hidden clubmemberlist2" cellspacing="0"
                            width="100%">
                            <thead>
                                <tr>
                                    <th class="text-white fw-medium text-nowrap">Item Name</th>
                                    <th class="text-white fw-medium text-nowrap">Category Name</th>
                                    <th class="text-white fw-medium text-nowrap">Current Stock</th>
                                    <th class="text-white fw-medium text-nowrap">Stock Alert</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($godownStockList as $item)
                                <tr>
                                    <td class="text-nowrap">{{ $item->foodItem?->name ?? 'N/A' }}</td>
                                    <td class="text-nowrap">{{ $item->foodItem?->foodItemCat?->name ?? 'N/A' }}</td>
                                    <td class="text-nowrap">{{ $item->quantity }}</td>
                                    @php
                                        if ($item->quantity > $item->foodItem->low_stock_alert_qty) {
                                            $class = 'badge text-success border border-success rounded-pill bg-success-subtle fw-medium py-2 px-3';
                                            $stockAlert = 'In-stock';
                                        } else {
                                            $class = 'badge text-danger border border-danger rounded-pill bg-danger-subtle fw-medium py-2 px-3';
                                            $stockAlert = 'Low-stock';
                                        }
                                    @endphp
                                    <td class="text-nowrap"><span class="{{ $class }}" style="min-width: 82px;">{{ $stockAlert }}</span></td>
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

@endsection

@section('customJS')
<script>
    $(document).ready(function() {
        var table = $('.clubmemberlist2').DataTable();

        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            var selectedName = $('#item_name').val() ? $('#item_name option:selected').text().trim().toLowerCase() : '';
            var selectedCategory = $('#item_category').val() ? $('#item_category option:selected').text().trim().toLowerCase() : '';

            var itemName = data[0].toLowerCase();
            var categoryName = data[1].toLowerCase();

            var nameMatch = !selectedName || itemName.includes(selectedName);
            var categoryMatch = !selectedCategory || categoryName.includes(selectedCategory);

            return nameMatch && categoryMatch;
        });

        $('#item_name, #item_category').on('change', function() {
            table.draw();
        });
    });
</script>
@endsection
