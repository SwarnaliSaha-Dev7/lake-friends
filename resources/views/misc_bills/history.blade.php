@extends('base.app')

@section('title', 'Misc Bill History')
@section('page_title', 'Misc Bill History')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="member-list-part position-relative">

                <div class="d-flex align-items-center justify-content-between gap-2 mb-3 flex-wrap">
                    <h2 class="fs-5 common-heading mb-0 fw-semibold">Misc Bill History</h2>
                </div>

                {{-- Stat cards --}}
                <div class="row g-3 mb-4">
                    <div class="col-6 col-xl-3">
                        <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-between"
                            style="background:linear-gradient(135deg,#29b6f6,#0288d1);">
                            <div>
                                <div class="small opacity-75 mb-1">Total Bills</div>
                                <div class="fs-4 fw-bold">{{ $totalBills }}</div>
                            </div>
                            <i class="fa-solid fa-receipt fs-2 opacity-50"></i>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-between"
                            style="background:linear-gradient(135deg,#66bb6a,#388e3c);">
                            <div>
                                <div class="small opacity-75 mb-1">Total Revenue</div>
                                <div class="fs-4 fw-bold">₹{{ number_format($totalRevenue, 0) }}</div>
                            </div>
                            <i class="fa-solid fa-indian-rupee-sign fs-2 opacity-50"></i>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-between"
                            style="background:linear-gradient(135deg,#ffa726,#e65100);">
                            <div>
                                <div class="small opacity-75 mb-1">Total GST</div>
                                <div class="fs-4 fw-bold">₹{{ number_format($totalGst, 0) }}</div>
                            </div>
                            <i class="fa-solid fa-tag fs-2 opacity-50"></i>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-between"
                            style="background:linear-gradient(135deg,#ab47bc,#7b1fa2);">
                            <div>
                                <div class="small opacity-75 mb-1">Avg Bill Value</div>
                                <div class="fs-4 fw-bold">₹{{ number_format($avgBill, 0) }}</div>
                            </div>
                            <i class="fa-solid fa-chart-line fs-2 opacity-50"></i>
                        </div>
                    </div>
                </div>

                {{-- Date filter --}}
                <form method="GET" action="{{ route('misc-bills.history') }}" class="row g-2 align-items-end mb-3">
                    <div class="col-sm-auto">
                        <label class="form-label small fw-semibold mb-1">From</label>
                        <input type="date" name="start_date" class="form-control form-control-sm shadow-none" value="{{ $startDate }}">
                    </div>
                    <div class="col-sm-auto">
                        <label class="form-label small fw-semibold mb-1">To</label>
                        <input type="date" name="end_date" class="form-control form-control-sm shadow-none" value="{{ $endDate }}">
                    </div>
                    <div class="col-sm-auto">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fa-solid fa-filter me-1"></i> Filter
                        </button>
                        @if(request()->hasAny(['start_date','end_date']))
                            <a href="{{ route('misc-bills.history') }}" class="btn btn-sm btn-outline-secondary ms-1">
                                <i class="fa-solid fa-xmark me-1"></i> Reset
                            </a>
                        @endif
                    </div>
                    <div class="col-sm-auto ms-sm-auto">
                        <a href="{{ route('misc-bills.report.download', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                            class="btn btn-sm btn-outline-danger fw-semibold">
                            <i class="fa-solid fa-file-pdf me-1"></i> Download PDF
                        </a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table rounded-3 overflow-hidden clubmemberlist2" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th class="text-white fw-medium align-middle text-nowrap">Sl No</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Bill No</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Buyer</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Items</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Date</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Payment</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Net Amount</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Status</th>
                                <th class="text-white fw-medium align-middle text-nowrap">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bills as $bill)
                                <tr>
                                    <td class="text-nowrap">{{ $loop->iteration }}</td>
                                    <td class="text-nowrap fw-medium">{{ $bill->bill_no }}</td>
                                    <td class="text-nowrap">{{ $bill->buyer_name ?: 'Cash Customer' }}</td>
                                    <td>
                                        @foreach($bill->items as $it)
                                            <div class="small text-nowrap">
                                                {{ $it->miscItem->name ?? '—' }} <span class="text-muted">&times;{{ rtrim(rtrim(number_format($it->quantity, 2), '0'), '.') }}</span>
                                            </div>
                                        @endforeach
                                    </td>
                                    <td class="text-nowrap">{{ \Carbon\Carbon::parse($bill->created_at)->format('d-m-Y h:i A') }}</td>
                                    <td class="text-nowrap text-capitalize">{{ str_replace('_', ' ', $bill->payment_mode) }}</td>
                                    <td class="text-nowrap fw-semibold">Rs {{ number_format($bill->net_amount, 2) }}</td>
                                    <td class="text-nowrap">
                                        <span class="badge border rounded-pill px-3 py-1 {{ $bill->status === 'paid' ? 'bg-success-subtle text-success border-success' : 'bg-danger-subtle text-danger border-danger' }}">
                                            {{ ucfirst($bill->status) }}
                                        </span>
                                    </td>
                                    <td class="text-nowrap">
                                        <button class="border-0 bg-light p-1 rounded-3 lh-1 action-btn view-misc-bill-btn"
                                            data-id="{{ $bill->id }}" title="View">
                                            <small><i class="fa-regular fa-eye"></i></small>
                                        </button>
                                        <a href="{{ route('misc-bills.receipt', $bill->id) }}" target="_blank"
                                            class="border-0 bg-light p-1 rounded-3 lh-1 action-btn ms-1 d-inline-flex align-items-center" title="Download Receipt">
                                            <small><i class="fa-solid fa-download"></i></small>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">No misc bills in this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('modalComponent')

    {{-- View Bill Modal --}}
    <div class="modal fade" id="viewMiscBillModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h2 class="modal-title fs-5 fw-semibold">Bill Details</h2>
                    <button type="button" class="btn-close bg-transparent fs-5 lh-1" data-bs-dismiss="modal">
                        <i class="fa-regular fa-circle-xmark"></i>
                    </button>
                </div>
                <div class="modal-body" id="viewMiscBillModalBody">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('customJS')
<script>
$(document).ready(function () {

    $(document).on('click', '.view-misc-bill-btn', function () {
        var id = $(this).data('id');
        $('#viewMiscBillModalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>');
        $('#viewMiscBillModal').modal('show');

        $.get('{{ route("misc-bills.show", ":id") }}'.replace(':id', id), function (res) {
            if (res.statusCode !== 200) {
                $('#viewMiscBillModalBody').html('<p class="text-danger text-center py-4">Failed to load bill.</p>');
                return;
            }
            renderMiscBillModal(res.data);
        }).fail(function () {
            $('#viewMiscBillModalBody').html('<p class="text-danger text-center py-4">Something went wrong.</p>');
        });
    });

    function renderMiscBillModal(bill) {
        var statusColor = { paid: 'text-success', cancelled: 'text-danger' };
        var sc = statusColor[bill.status] || 'text-muted';

        var html = '<div class="mb-3 pb-2 border-bottom d-flex justify-content-between align-items-start">'
            + '<div>'
            + '<div class="fw-bold fs-6">' + bill.bill_no + '</div>'
            + '<div class="text-muted small">' + (bill.buyer_name || 'Cash Customer') + (bill.buyer_contact ? ' · ' + bill.buyer_contact : '') + '</div>'
            + '</div>'
            + '<span class="fw-semibold ' + sc + '">' + bill.status.charAt(0).toUpperCase() + bill.status.slice(1) + '</span>'
            + '</div>';

        var rows = '';
        (bill.items || []).forEach(function (it) {
            rows += '<tr>'
                + '<td class="text-muted small">' + (it.misc_item ? it.misc_item.name : '—') + '</td>'
                + '<td class="text-center text-muted small">' + parseFloat(it.quantity) + ' ' + (it.unit || '') + '</td>'
                + '<td class="text-center text-muted small">' + parseFloat(it.gst_percentage).toFixed(2) + '%</td>'
                + '<td class="text-end text-muted small">Rs ' + (parseFloat(it.total_amount) + parseFloat(it.gst_amount)).toFixed(2) + '</td>'
                + '</tr>';
        });

        html += '<div class="border rounded-3 overflow-hidden mb-3">'
            + '<table class="table table-sm mb-0"><thead><tr>'
            + '<th style="font-size:0.75rem;">Item</th><th class="text-center" style="font-size:0.75rem;">Qty</th>'
            + '<th class="text-center" style="font-size:0.75rem;">GST</th><th class="text-end" style="font-size:0.75rem;">Total</th>'
            + '</tr></thead><tbody>' + rows + '</tbody></table>'
            + '</div>';

        html += '<div class="p-3 bg-light border rounded-3">'
            + '<div class="row mb-1 border-bottom pb-1"><div class="col-8 text-end text-muted small">Subtotal</div><div class="col-4 text-center fw-semibold small">Rs ' + parseFloat(bill.subtotal).toFixed(2) + '</div></div>'
            + '<div class="row mb-1 border-bottom pb-1"><div class="col-8 text-end text-muted small">GST</div><div class="col-4 text-center fw-semibold small">Rs ' + parseFloat(bill.gst_amount).toFixed(2) + '</div></div>'
            + '<div class="row py-1 bg-dark text-white rounded-3 mx-0 mt-1"><div class="col-8 text-end small">Net Amount</div><div class="col-4 text-center fw-bold">Rs ' + parseFloat(bill.net_amount).toFixed(2) + '</div></div>'
            + '</div>';

        if (bill.remarks) {
            html += '<div class="mt-2 small text-muted"><strong>Remarks:</strong> ' + bill.remarks + '</div>';
        }
        if (bill.status === 'cancelled') {
            html += '<div class="mt-2 small text-danger"><strong>Cancelled by:</strong> ' + (bill.cancelled_by ? bill.cancelled_by.name : '—')
                + (bill.cancel_reason ? ' — ' + bill.cancel_reason : '') + '</div>';
        }

        $('#viewMiscBillModalBody').html(html);
    }

});
</script>
@endsection
