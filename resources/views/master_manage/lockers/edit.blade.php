@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')

    <form action="{{ route('manage-lockers.update', $lockers->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row">
            <!-- data type text -->
            <div class="col-xl-6 col-md-6">
                <div class="form-part mb-3">
                    <label for="" class="form-label w-100 mb-1 w-100"><small>Locker No.</small></label>
                    <input type="text" class="form-control py-2 shadow-none" name="locker_number" id="" placeholder="Locker No." value="{{ old('locker_number', $lockers->locker_number) }}" required>
                    @error('locker_number')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <input type="hidden" name="is_active" value="0">

            <!-- data type checkbox list -->
            <div class="col-md-6">
                <div class="form-part mb-3">
                    <label for="" class="form-label w-100"><small></small></label>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" value="1" {{ old('is_active', $lockers->is_active) ? 'checked' : '' }} id="is_active" name="is_active">
                        <label class="form-check-label" for="checkDefault">
                            <small>Active</small>
                        </label>
                    </div>
                </div>
            </div>

        </div>
        <!-- <button class="btn btn-primary fw-semibold">Default</button> -->
        <button class="btn btn-primary fw-semibold">Update</button>
    </form>

@endsection

@section('customJS')
@endsection
