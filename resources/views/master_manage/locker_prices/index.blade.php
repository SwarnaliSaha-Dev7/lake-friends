@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')

    <form action="{{ route('manage-locker-prices.update') }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-xl-6 col-md-6">
                <div class="form-part mb-3">
                    <label class="form-label w-100 mb-1"><small>Club Locker Cost (first time, 1 year) ₹</small></label>
                    <input type="number" name="club_first_price" class="form-control py-2 shadow-none @error('club_first_price') is-invalid @enderror"
                        value="{{ old('club_first_price', $lockerPrice->club_first_price) }}" step="0.01" min="0" required>
                    @error('club_first_price')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="col-xl-6 col-md-6">
                <div class="form-part mb-3">
                    <label class="form-label w-100 mb-1"><small>Club Locker Rent (yearly renewal) ₹</small></label>
                    <input type="number" name="club_renewal_price" class="form-control py-2 shadow-none @error('club_renewal_price') is-invalid @enderror"
                        value="{{ old('club_renewal_price', $lockerPrice->club_renewal_price) }}" step="0.01" min="0" required>
                    @error('club_renewal_price')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="col-xl-6 col-md-6">
                <div class="form-part mb-3">
                    <label class="form-label w-100 mb-1"><small>Swimming Locker Cost (6 months) ₹</small></label>
                    <input type="number" name="swim_price" class="form-control py-2 shadow-none @error('swim_price') is-invalid @enderror"
                        value="{{ old('swim_price', $lockerPrice->swim_price) }}" step="0.01" min="0" required>
                    @error('swim_price')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="col-xl-6 col-md-6">
                <div class="form-part mb-3">
                    <label class="form-label w-100 mb-1"><small>Locker GST %</small></label>
                    <input type="number" name="gst_percentage" class="form-control py-2 shadow-none @error('gst_percentage') is-invalid @enderror"
                        value="{{ old('gst_percentage', $lockerPrice->gst_percentage) }}" step="0.01" min="0" max="100" required>
                    @error('gst_percentage')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <p class="text-muted small mb-3">
            These are the default amounts on a new locker bill. Staff can still edit the amount and GST on each bill.
        </p>

        <button class="btn btn-primary fw-semibold">Update</button>
    </form>

@endsection

@section('customJS')
@endsection
