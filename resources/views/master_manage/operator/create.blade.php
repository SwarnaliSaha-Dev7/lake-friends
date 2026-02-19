@extends('base.app')

@section('title', 'LakeFriends Calcutta')

@section('content')
    <form action="{{ route('manage-operators.store') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="form-part mb-3">
                    <label for="" class="form-label w-100 mb-1 w-100"><small>Name</small></label>
                    <input type="text" class="form-control py-2 shadow-none" id="" name="name" placeholder="Name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-part mb-3">
                    <label for="" class="form-label w-100 mb-1 w-100"><small>Email</small></label>
                    <input type="email" class="form-control py-2 shadow-none" id="" name="email" placeholder="Email" value="{{ old('email') }}" required>
                    @error('email')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-part mb-3">
                    <label for="" class="form-label w-100 mb-1 w-100"><small>Password</small></label>
                    <input type="password" class="form-control py-2 shadow-none" name="password" id="" placeholder="password" required>
                    @error('password')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-part mb-3">
                    <label for="" class="form-label w-100 mb-1 w-100"><small>Status</small></label>
                    <select name="status" id="" class="form-select py-2 shadow-none" required>
                        <option value="" selected="" hidden="" disabled="">Select Status</option>
                        <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror

                </div>
            </div>

        </div>
        <button class="btn btn-primary fw-semibold">Default</button>
        <button type="submit" class="btn btn-info fw-semibold">Submit</button>
    </form>
@endsection

@section('customJS')
@endsection
