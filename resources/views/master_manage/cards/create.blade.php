@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')

    <form action="{{ route('manage-cards.store') }}" method="POST">
        @csrf
        <div class="row">
            <!-- data type text -->
            <div class="col-xl-6 col-md-6">
                <div class="form-part mb-3">                    
                    <label for="" class="form-label w-100 mb-1 w-100"><small>Card No.</small></label>
                    <input type="text" class="form-control py-2 shadow-none" name="card_no" id="" placeholder="Card No" value="{{ old('card_no') }}" required>
                    @error('card_no')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror       
                </div>
            </div>

            <!-- data type select option first option used as a placeholder -->
            <div class="col-xl-6 col-md-6">
                <div class="form-part mb-3">
                    <label for="" class="form-label w-100 mb-1 w-100"><small>Select Status</small></label>
                    <select name="status" id="" class="form-select py-2 shadow-none">
                        <option value="" selected="" hidden="" disabled="">Select Status
                        </option>

                        @foreach(\App\Models\Card::statuses() as $status)
                            <option value="{{ $status }}" {{ old('status', 'pending') == $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    @error('status')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

        </div>
        <!-- <button class="btn btn-primary fw-semibold">Default</button> -->
        <button class="btn btn-primary fw-semibold">Submit</button>
    </form>

@endsection

@section('customJS')
@endsection