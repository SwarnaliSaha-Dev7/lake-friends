@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')

    <form action="{{ route('manage-lockers.store') }}" method="POST">
        @csrf
        <div class="row">
            <!-- data type text -->
            <div class="col-xl-6 col-md-6">
                <div class="form-part mb-3">                    
                    <label for="" class="form-label w-100 mb-1 w-100"><small>Locker No.</small></label>
                    <input type="text" class="form-control py-2 shadow-none" name="locker_number" id="" placeholder="Locker No." value="{{ old('locker_number') }}" required>
                    @error('locker_number')
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