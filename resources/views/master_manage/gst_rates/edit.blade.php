@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')

    <form action="{{ route('manage-gst-rates.update', $gst->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row">
            
            <div class="col-xl-6 col-md-6">
                <div class="form-part mb-3">
                    <label for="" class="form-label w-100 mb-1 w-100"><small>GST Type</small></label>
                    <input type="text" name="gst_type" class="form-control py-2 shadow-none" value="{{ ucwords(str_replace('_',' ',$gst->gst_type)) }}" readonly>
                </div>
            </div>

            <!-- data type number -->
            <div class="col-xl-6 col-md-6">
                    <div class="form-part mb-3">
                        <label for="" class="form-label w-100 mb-1 w-100"><small>GST Percentage</small></label>
                        <input type="number" name="gst_percentage" class="form-control py-2 shadow-none" placeholder="GST Percentage"  value="{{ old('gst_percentage', $gst->gst_percentage) }}" step="0.01" min="0" max="100" required>
                    </div>
            </div>            

        </div>
        <!-- <button class="btn btn-primary fw-semibold">Default</button> -->
        <button class="btn btn-primary fw-semibold">Update</button>
    </form>

@endsection

@section('customJS')
@endsection