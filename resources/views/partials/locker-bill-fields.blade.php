<div class="d-none mt-3" id="lockerPriceWrapper">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="badge bg-info-subtle text-info border border-info rounded-pill px-2 py-1" id="lockerPurchaseType">-</span>
        <small class="text-muted">Valid till <span class="fw-semibold" id="lockerValidTill">-</span></small>
    </div>
    <div class="row g-2">
        <div class="col-6">
            <label class="form-label mb-1"><small>Taxable Amount</small></label>
            <input type="number" class="form-control shadow-none" id="lockerTaxable" step="0.01" min="0">
        </div>
        <div class="col-6">
            <label class="form-label mb-1"><small>GST %</small></label>
            <input type="number" class="form-control shadow-none" id="lockerGstPct" step="0.01" min="0" max="100">
        </div>
        <div class="col-6">
            <label class="form-label mb-1"><small>GST Amount</small></label>
            <input type="text" class="form-control shadow-none" id="lockerGstAmt" readonly>
        </div>
        <div class="col-6">
            <label class="form-label mb-1"><small>Payment Mode</small></label>
            <input type="text" class="form-control shadow-none" id="lockerPaymentMode" placeholder="Payment Mode">
        </div>
        <div class="col-6">
            <label class="form-label mb-1"><small>Bank</small></label>
            <select class="form-select shadow-none" id="lockerBankId">
                <option value="">Bank Name</option>
                @foreach ($bankList as $bank)
                    <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6">
            <label class="form-label mb-1"><small>A/C Head</small></label>
            <input type="text" class="form-control shadow-none" id="lockerAcHead" placeholder="A/C Head" value="Locker">
        </div>
        <div class="col-12">
            <label class="form-label mb-1"><small>Remarks</small></label>
            <input type="text" class="form-control shadow-none" id="lockerRemarks" placeholder="Remarks">
        </div>
    </div>
    <div class="text-end mt-3">
        <small>Net Amount to Pay</small>
        <h5 class="fw-semibold mb-0">₹ <span id="lockerPrice">0.00</span></h5>
    </div>
</div>
