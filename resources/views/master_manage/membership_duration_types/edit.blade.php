@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')

    <form action="{{ route('manage-membership-duration-types.update', $membership_duration_types->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row">
            <!-- data type text -->
            <div class="col-xl-4 col-md-6">
                <div class="form-part mb-3">                    
                    <label for="" class="form-label w-100 mb-1 w-100"><small>Name</small></label>
                    <input type="text" class="form-control py-2 shadow-none" name="name" id="" placeholder="Name" value="{{ old('name', $membership_duration_types->name) }}"
                        required>  
                    @error('name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror                  
                </div>
            </div>

            <!-- data type select option first option used as a placeholder -->
            <div class="col-xl-4 col-md-6">
                <div class="form-part mb-3">
                    <label for="" class="form-label w-100 mb-1 w-100"><small>Duration Months</small></label>
                    <select name="duration_months" id="duration_months" class="form-select py-2 shadow-none">
                        <option value="" selected="" hidden="" disabled="">Select Months
                        </option>

                        @for($i=1; $i<=12; $i++)
                            <option value="{{ $i }}" {{ old('duration_months',$membership_duration_types->duration_months) == $i ? 'selected' : ''}}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            <!-- data type checkbox list -->
            <div class="col-md-6">
                <div class="form-part mb-3">
                    <label for="" class="form-label w-100"><small></small></label>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" value="1" {{ old('is_lifetime', $membership_duration_types->is_lifetime) ? 'checked' : '' }} id="is_lifetime" name="is_lifetime">
                        <label class="form-check-label" for="checkDefault">
                            <small>Is Lifetime</small>
                        </label>
                    </div>
                </div>
            </div>

        </div>
        <!-- <button class="btn btn-primary fw-semibold">Default</button> -->
        <button class="btn btn-info fw-semibold">Submit</button>
    </form>

@endsection

@section('customJS')

<script>
$(document).ready(function () {

    function handleLifetimeToggle() {

        if ($('#is_lifetime').is(':checked')) {
            $('#duration_months').prop('disabled', true);
        } else {
            $('#duration_months').prop('disabled', false);
        }
    }

    // Run once on page load
    handleLifetimeToggle();

    // Run when checkbox changes
    $('#is_lifetime').on('change', function () {
        handleLifetimeToggle();
    });

});
</script>

@endsection