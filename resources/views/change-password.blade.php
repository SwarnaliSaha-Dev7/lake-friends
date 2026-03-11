@extends('base.app')

@section('title', 'Change Password')
{{-- @section('page_title', $page_title) --}}

@section('content')
    <form action="{{ route('password.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">

            {{-- Current Password --}}
            <div class="col-md-8">
                <div class="form-part mb-3">
                    <label class="form-label w-100 mb-1">
                        <small>Current Password</small>
                    </label>
                    <input type="password"
                        class="form-control py-2 shadow-none"
                        name="current_password"
                        placeholder="Current Password"
                        required>

                    @error('current_password', 'updatePassword')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row">
            {{-- New Password --}}
            <div class="col-md-8">
                <div class="form-part mb-3">
                    <label class="form-label w-100 mb-1">
                        <small>New Password</small>
                    </label>
                    <input type="password"
                        class="form-control py-2 shadow-none"
                        name="password"
                        placeholder="New Password"
                        required>
                    @error('password', 'updatePassword')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Confirm Password --}}
            <div class="col-md-8">
                <div class="form-part mb-3">
                    <label class="form-label w-100 mb-1">
                        <small>Confirm Password</small>
                    </label>
                    <input type="password"
                        class="form-control py-2 shadow-none"
                        name="password_confirmation"
                        placeholder="Confirm Password"
                        required>
                    @error('password_confirmation', 'updatePassword')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary fw-semibold">Submit</button>
    </form>
@endsection

@section('customJS')
@endsection
