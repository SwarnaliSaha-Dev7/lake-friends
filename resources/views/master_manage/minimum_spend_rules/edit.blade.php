@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')

    <form action="{{ route('manage-minimum-spend-rules.update', $minSpendRules->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row">

            <!-- data type number -->
            <div class="col-xl-6 col-md-6">
                    <div class="form-part mb-3">
                        <label for="" class="form-label w-100 mb-1 w-100"><small>Minimum Spend Amount</small></label>
                        <input type="number" name="minimum_amount" class="form-control py-2 shadow-none" placeholder="Minimum Spend Amount"  value="{{ old('minimum_amount', $minSpendRules->minimum_amount) }}" step="0.01" min="0" max="99999999">
                    </div>
            </div>            

        </div>
        <!-- <button class="btn btn-primary fw-semibold">Default</button> -->
        <button class="btn btn-primary fw-semibold">Update</button>
    </form>

@endsection

@section('customJS')
@endsection