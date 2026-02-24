@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')

    <form action="{{ route('manage-gst-rates.update', $gst->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row">
        <!-- data type number -->
        <div class="col-xl-4 col-md-6">
                <div class="form-part mb-3">
                    <input type="number" name="gst_percentage" class="form-control py-2 shadow-none" placeholder="GST Percentage"  value="{{ old('gst_percentage', $gst->gst_percentage) }}" required>
                </div>
        </div>

        </div>
        <!-- <button class="btn btn-primary fw-semibold">Default</button> -->
        <button class="btn btn-primary fw-semibold">Update</button>
    </form>

@endsection

@section('customJS')
@endsection