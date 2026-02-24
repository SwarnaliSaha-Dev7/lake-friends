jQuery(function ($) {
    // login js start

    $(function () {
        $(".log-user-icon").on("click", function (e) {
            e.stopPropagation();
            $(".login-dropdown").toggle();
        });
        $(".login-dropdown ul li a").on("click", function (e) {
            e.stopPropagation();
            $(".login-dropdown").hide();
        });
        // Prevent dropdown clicks from bubbling
        $(".login-dropdown").on("click", function (e) {
            e.stopPropagation();
        });
        $(document).on("click", function () {
            $(".login-dropdown").hide();
        });

    });

    // login js end

    // notification js start
    $(function () {

        const $bell = $('.notification > a');
        const $dropdown = $('.notification-dropdown');
        const $badge = $('.notification > a .badge');
        const $closeBtn = $('.close-notific');

        // Toggle dropdown on bell click
        $bell.on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            $dropdown.toggle();
            $badge.hide(); // hide badge when opened
        });

        // Close button click
        $closeBtn.on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            $dropdown.hide();
        });

        // Prevent click inside dropdown from closing it
        $dropdown.on('click', function (e) {
            e.stopPropagation();
        });

        // Click outside → close dropdown
        $(document).on('click', function () {
            $dropdown.hide();
        });

    });
    // notification js end

    $('.nav-menu ul > li > ul').parent().prepend('<i class="arw-nav"></i>');
    function subMenu() {
        $(this).parent('li').find('> ul').stop(true, true).slideToggle();
        $(this).parents('li').siblings().find('ul').stop(true, true).slideUp();
        $(this).toggleClass('actv');
        $(this).parent().siblings().find('.arw-nav').removeClass('actv');
    }
    $('.nav-menu ul > li > .arw-nav').on('click', subMenu);

    // datatable js start
    // document.title = 'Simple DataTable';
    $('.clubmemberlist').DataTable(
        {
            "dom": '<"dt-buttons"Bf><"clear">lirtp',
            "paging": false,
            "autoWidth": false,
            "searching": false,
            "lengthChange": false,
            "info": false,
            "buttons": [
                'colvis',
                'copyHtml5',
                'csvHtml5',
                'excelHtml5',
                'pdfHtml5',
                'print'
            ]
        }
    );

    // 1 Initialize DataTable ONCE
    var table = $('.clubmemberlist2').DataTable({
        dom: '<"dt-buttons"Bf><"clear">rtip', // removed 'l' to hide length dropdown
        paging: true,
        autoWidth: false,
        lengthChange: false,
        searching: true,
        info: false,
        buttons: [
            'colvis',
            'copyHtml5',
            'csvHtml5',
            'excelHtml5',
            'pdfHtml5',
            'print'
        ]
    });

    // 2 Detect Status column dynamically (Status / Satus)
    var statusColumnIndex = -1;

    table.columns().every(function (index) {
        var headerText = $(this.header()).text().trim().toLowerCase();
        if (headerText === 'status' || headerText === 'satus') {
            statusColumnIndex = index;
        }
    });

    // Safety check
    if (statusColumnIndex === -1) {
        console.warn('Status column not found');
        return;
    }

    // 3 Custom status filter (register ONCE)
    $.fn.dataTable.ext.search.push(function (settings, data) {

        // Apply only to this table
        if (settings.nTable !== table.table().node()) {
            return true;
        }

        var selectedStatus = $('#statusFilter').val();

        // Show all if "All" or placeholder
        if (!selectedStatus) return true;

        var statusText = data[statusColumnIndex].trim();
        return statusText === selectedStatus;
    });

    // 4 Redraw table on dropdown change
    $('#statusFilter').on('change', function () {
        table.draw();
    });
    // datatable js end

    // choose file js start
    $('.file-input').on('change', function () {
        let fileName = this.files[0]?.name;
        let textElement = $(this)
            .closest('.file-upload-box')
            .find('.upload-text');

        if (fileName) {
            textElement.text(fileName);
        }
    });
    // choose file js end

    // input radio select js start
    $('.custom-radio input[type="radio"]').on('change', function () {
        // get label text (₹ 1000)
        let amountText = $(this).next('label').text();
        // remove ₹ and spaces → get number only
        let amountValue = amountText.replace(/[^\d]/g, '');
        // set value in input
        $('#amountInput').val(amountValue);
    });
    // input radio select js end

    // Delete each row table js start
    $(function () {
        var table = $('.clubmemberlist2').DataTable();
        let rowToDelete = null;
        $('.clubmemberlist2').on('click', '.delete-row', function (e) {
            e.preventDefault();
            rowToDelete = $(this).closest('tr');
            $('#deleteConfirmModal').modal('show');
        });

        // Confirm delete
        $('#confirmDeleteBtn').on('click', function () {
            if (rowToDelete) {
                table
                    .row(rowToDelete)
                    .remove()
                    .draw(false);

                rowToDelete = null;
            }

            $('#deleteConfirmModal').modal('hide');
        });

    });
    // Delete each row table js end

    // Login Step js start
    function showStep(stepClass) {
        $('[class^="login-step"]').addClass('d-none');
        $(stepClass).removeClass('d-none');
    }

    // Default
    showStep('.login-step1');

    // Forgot password
    $('.login-step1 a:contains("Forgot")').on('click', function (e) {
        e.preventDefault();
        showStep('.login-step2');
    });

    // Back to login
    $('.login-step3 a').on('click', function (e) {
        e.preventDefault();
        showStep('.login-step1');
    });

    function showStep(stepClass) {
        $('[class^="login-step"]').addClass('d-none');
        $(stepClass).removeClass('d-none');
    }

    // OTP submit → go to step 4
    $('#otpModal form').on('submit', function (e) {
        e.preventDefault(); // stop page reload

        // Close modal
        $('#otpModal').modal('hide');

        // After modal fully closes show step 4
        $('#otpModal').one('hidden.bs.modal', function () {
            showStep('.login-step3');
        });
    });
    // Login Step js end

    // Search and multi select js start
    $('.multi-select').select2({
        placeholder: 'Search & Select',
        width: '100%'
    });
    // Search and multi select js end



});
