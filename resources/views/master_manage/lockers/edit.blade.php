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
        <div class="d-flex align-items-center gap-3 flex-wrap">
            @if(!empty($allocation))
                <div class="d-flex align-items-center gap-2">
                    <small class="text-muted">Allocation Expiry:</small>
                    <span class="fw-semibold">
                        {{ $allocationExpiry ? \Carbon\Carbon::parse($allocationExpiry)->format('d/m/Y') : '—' }}
                    </span>
                </div>

                @if($isAllocationExpired)
                    <button
                        type="button"
                        class="btn btn-outline-danger fw-semibold"
                        id="delinkLockerBtn"
                        data-locker-id="{{ $lockers->id }}"
                    >
                        Delink From Member
                    </button>
                @endif
            @endif
        </div>

        <button class="btn btn-primary fw-semibold">Update</button>
    </form>

@endsection

@section('customJS')
    <script>
        $(document).on('click', '#delinkLockerBtn', function () {
            const lockerId = $(this).data('locker-id');
            if (!lockerId) return;

            // if (!confirm('Are you sure you want to delink this locker from the member?')) return;

            $.ajax({
                url: '{{ route("manage-lockers.delink") }}',
                type: 'POST',
                data: {
                    locker_id: lockerId,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    if (response.statusCode === 200) {
                        toastr.success(response.message || 'Locker delinked successfully');
                        location.reload();
                    } else {
                        toastr.error(response.message || 'Unable to delink locker');
                    }
                },
                error: function (xhr) {
                    const msg = xhr.responseJSON?.message || 'Unable to delink locker';
                    toastr.error(msg);
                }
            });
        });
    </script>
@endsection
