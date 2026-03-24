@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')

    <form action="{{ route('manage-fine-rules.update', $fineRules->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-xl-6 col-md-8">

                <div class="form-part mb-3">
                    <label class="form-label mb-1 w-100"><small>Membership Plan</small></label>
                    <input type="text" class="form-control py-2 shadow-none bg-light" readonly
                        value="{{ $fineRules->membershipPlanType?->name ?? 'Global (Default)' }}">
                    <small class="text-muted">Plan cannot be changed after creation.</small>
                </div>

                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label mb-1 w-100"><small>Per Day Fine (₹)</small></label>
                        <input type="number" name="per_day_fine_amount" class="form-control py-2 shadow-none"
                            placeholder="e.g. 10" step="0.01" min="0" required
                            value="{{ old('per_day_fine_amount', $fineRules->per_day_fine_amount ?? 10) }}">
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label mb-1 w-100"><small>Grace Days <span class="text-muted">(after expiry)</span></small></label>
                        <input type="number" name="grace_days" class="form-control py-2 shadow-none"
                            placeholder="e.g. 76" min="0"
                            value="{{ old('grace_days', $fineRules->grace_days ?? 76) }}">
                        <small class="text-muted">76 = April 1 → June 15</small>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label mb-1 w-100"><small>Max Fine Cap (₹) <span class="text-muted">optional</span></small></label>
                        <input type="number" name="max_fine_cap" class="form-control py-2 shadow-none"
                            placeholder="No cap" step="0.01" min="0"
                            value="{{ old('max_fine_cap', $fineRules->max_fine_cap) }}">
                    </div>
                </div>

                <div class="d-flex gap-2 mt-1">
                    <button class="btn btn-primary fw-semibold">Update</button>
                    <a href="{{ route('manage-fine-rules.index') }}" class="btn btn-light fw-semibold">Cancel</a>
                </div>
            </div>
        </div>
    </form>

@endsection

@section('customJS')
@endsection
