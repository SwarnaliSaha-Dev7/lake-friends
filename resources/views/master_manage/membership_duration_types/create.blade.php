@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')

    <form action="{{ route('manage-membership-duration-types.store') }}" method="POST">
        @csrf
        <div class="row">
            <!-- data type text -->
            <div class="col-xl-6 col-md-6">
                <div class="form-part mb-3">                    
                    <label for="" class="form-label w-100 mb-1 w-100"><small>Name</small></label>
                    <input type="text" class="form-control py-2 shadow-none" name="name" id="" placeholder="Name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror       
                </div>
            </div>

            <!-- data type select option first option used as a placeholder -->
            <div class="col-xl-6 col-md-6">
                <div class="form-part mb-3">
                    <label for="" class="form-label w-100 mb-1 w-100"><small>Duration Months</small></label>
                    <select name="duration_months" id="duration_months" class="form-select py-2 shadow-none">
                        <option value="" selected="" hidden="" disabled="">Select Duration Months
                        </option>

                        @for($i=1; $i<=12; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                    @error('duration_months')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- data type checkbox list -->
            <div class="col-md-6">
                <div class="form-part mb-3">
                    <label for="" class="form-label w-100"><small></small></label>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" value="1" id="is_lifetime" name="is_lifetime">
                        <label class="form-check-label" for="is_lifetime">
                            <small>Is Lifetime</small>
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" value="1" id="is_minimum_spend_applicable" name="is_minimum_spend_applicable">
                        <label class="form-check-label" for="is_minimum_spend_applicable">
                            <small>Minimum Spend Applicable</small>
                        </label>
                    </div>
                </div>
            </div>

        </div>
        <!-- <button class="btn btn-primary fw-semibold">Default</button> -->
        <button class="btn btn-primary fw-semibold">Submit</button>
    </form>

@endsection

@section('customJS')

<script>
    $(document).ready(function(){
        // When Lifetime checkbox changes
        $("#is_lifetime").on("change", function(){

            if( $(this).is(':checked')) {

                // Disable dropdown
                $("#duration_months").prop('disabled', true);

                // Clear dropdown value
                $("#duration_months").vale("");
            }

            else {
                // Clear dropdown value
                $("#duration_months").prop('disabled', false);
            }
        });

        // When dropdown changes
        $("#duration_months").on("change", function(){

        if($(this).val() !== ''){
            // Uncheck lifetime
            $("#is_lifetime").prop('checked', false);
        }
        });
    });
</script>

@endsection