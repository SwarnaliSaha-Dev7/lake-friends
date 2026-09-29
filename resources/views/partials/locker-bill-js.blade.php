<script>
    window.lockerBill = {
        typeLabels: { first: 'First Time · 1 Year', renewal: 'Renewal (Rent) · 1 Year', swim: 'Swimming · 6 Months' },

        fill: function (quote) {
            if (!quote) return;
            $('#lockerPurchaseType').text(this.typeLabels[quote.purchase_type] || quote.purchase_type);
            $('#lockerValidTill').text(new Date(quote.valid_till).toLocaleDateString('en-IN', { timeZone: 'Asia/Kolkata' }));
            $('#lockerTaxable').val(parseFloat(quote.taxable_amount).toFixed(2));
            $('#lockerGstPct').val(parseFloat(quote.gst_percentage));
            $('#lockerPaymentMode, #lockerRemarks').val('');
            $('#lockerBankId').val('');
            $('#lockerAcHead').val('Locker');
            this.recalc();
        },

        recalc: function () {
            var taxable = parseFloat($('#lockerTaxable').val()) || 0;
            var gstPct  = parseFloat($('#lockerGstPct').val()) || 0;
            var gstAmt  = Math.round(taxable * gstPct) / 100;
            $('#lockerGstAmt').val(gstAmt.toFixed(2));
            $('#lockerPrice').text((taxable + gstAmt).toFixed(2));
        },

        payload: function () {
            return {
                taxable_amount: $('#lockerTaxable').val(),
                gst_percentage: $('#lockerGstPct').val(),
                payment_mode:   $('#lockerPaymentMode').val(),
                bank_id:        $('#lockerBankId').val(),
                ac_head:        $('#lockerAcHead').val(),
                remarks:        $('#lockerRemarks').val(),
            };
        },

        validate: function () {
            var taxable = $('#lockerTaxable').val();
            var gstPct  = $('#lockerGstPct').val();
            if (taxable === '' || isNaN(taxable) || parseFloat(taxable) < 0) return 'Please enter a valid taxable amount.';
            if (gstPct === '' || isNaN(gstPct) || parseFloat(gstPct) < 0 || parseFloat(gstPct) > 100) return 'Please enter a valid GST %.';
            return null;
        },
    };

    $(document).on('input', '#lockerTaxable, #lockerGstPct', function () {
        window.lockerBill.recalc();
    });
</script>
