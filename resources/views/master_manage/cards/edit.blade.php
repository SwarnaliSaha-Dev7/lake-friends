@extends('base.app')

@section('title', $title)
@section('page_title', $page_title)

@section('content')

    <form action="{{ route('manage-cards.update', $cards->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row">
            <!-- data type text -->
            <div class="col-xl-6 col-md-6">
                <div class="form-part mb-3">
                    <label for="" class="form-label w-100 mb-1 w-100"><small>Card No.</small></label>
                    <input type="text" class="form-control py-2 shadow-none" name="card_no" id="" placeholder="Card No" value="{{ old('card_no', $cards->card_no) }}" required>
                    @error('card_no')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- data type select option first option used as a placeholder -->
            <div class="col-xl-6 col-md-6">
                <div class="form-part mb-3">
                    <label for="" class="form-label w-100 mb-1 w-100"><small>Select Status</small></label>
                    <select name="status" id="" class="form-select py-2 shadow-none">
                        <option value="" selected="" hidden="" disabled="">Select Status
                        </option>

                        @foreach(\App\Models\Card::statuses() as $status)
                            <option value="{{ $status }}" {{ old('status', $cards->status) == $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    @error('status')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

        </div>
        <div class="d-flex align-items-center gap-3 flex-wrap">
            @if(!empty($cardMapping))
                <div class="d-flex align-items-center gap-2">
                    <small class="text-muted">Membership Expiry:</small>
                    <span class="fw-semibold">
                        {{ $membershipExpiry ? \Carbon\Carbon::parse($membershipExpiry)->format('d/m/Y') : '—' }}
                    </span>
                </div>

                @if($isMembershipExpired)
                    <button
                        type="button"
                        class="btn btn-outline-danger fw-semibold"
                        id="delinkCardBtn"
                        data-card-id="{{ $cards->id }}"
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
        $(document).on('click', '#delinkCardBtn', function () {
            const cardId = $(this).data('card-id');
            if (!cardId) return;

            // if (!confirm('Are you sure you want to delink this card from the member?')) return;

            $.ajax({
                url: '{{ route("manage-cards.delink") }}',
                type: 'POST',
                data: {
                    card_id: cardId,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    if (response.statusCode === 200) {
                        toastr.success(response.message || 'Card delinked successfully');
                        location.reload();
                    } else {
                        toastr.error(response.message || 'Unable to delink card');
                    }
                },
                error: function (xhr) {
                    const msg = xhr.responseJSON?.message || 'Unable to delink card';
                    toastr.error(msg);
                }
            });
        });
    </script>
@endsection
