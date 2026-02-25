@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')

    <form action="{{ route('manage-fine-rules.update', $fineRules->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row">

            <!-- data type number -->
            <div class="col-xl-6 col-md-6">
                    <div class="form-part mb-3">
                        <label for="" class="form-label w-100 mb-1 w-100"><small>Per Day Fine Amount</small></label>
                        <input type="number" name="per_day_fine_amount" class="form-control py-2 shadow-none" placeholder="Per Day Fine Amount"  value="{{ old('per_day_fine_amount', $fineRules->per_day_fine_amount) }}" step="0.01" min="0"  max="9999999999" required>
                    </div>
            </div>            

        </div>
        <!-- <button class="btn btn-primary fw-semibold">Default</button> -->
        <button class="btn btn-primary fw-semibold">Update</button>
    </form>

@endsection

@section('customJS')
@endsection