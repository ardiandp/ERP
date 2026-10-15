/*
 * Global DataTables initialization for Matrix Admin (Bootstrap 4).
 * Applies DataTables to every listing table on the page.
 *
 * Skipped automatically:
 *   - tables without a <thead> (DataTables requires a header)
 *   - tables marked with the "no-datatable" class
 *   - compact ".table-sm" tables (detail/summary tables)
 *   - tables rendered inside a <form> (editable line-item tables)
 */
$(function () {
    "use strict";

    if (!$.fn || !$.fn.DataTable) {
        return;
    }

    var id = {
        search: "Cari:",
        lengthMenu: "Tampilkan _MENU_ data",
        info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
        infoEmpty: "Tidak ada data",
        infoFiltered: "(difilter dari _MAX_ total data)",
        zeroRecords: "Data tidak ditemukan",
        emptyTable: "Belum ada data",
        paginate: {
            first: "Awal",
            previous: "Sebelumnya",
            next: "Berikutnya",
            last: "Akhir"
        }
    };

    $("table.table").filter(function () {
        var $table = $(this);

        if ($table.find("thead th").length === 0) {
            return false;
        }
        if ($table.hasClass("no-datatable") || $table.hasClass("table-sm")) {
            return false;
        }
        if ($table.closest("form").length > 0) {
            return false;
        }

        return true;
    }).each(function () {
        var $table = $(this);

        if ($.fn.DataTable.isDataTable(this)) {
            return;
        }

        // Drop the "no data" placeholder row so DataTables reports the empty state itself.
        $table.find("tbody tr").each(function () {
            var $cells = $(this).children("td");
            if ($cells.length === 1 && $cells.attr("colspan")) {
                $(this).remove();
            }
        });

        // Columns with an empty header (usually the action column) must not be sortable.
        var nonSortable = [];
        $table.find("thead th").each(function (index) {
            if ($.trim($(this).text()) === "") {
                nonSortable.push(index);
            }
        });

        var options = {
            language: id,
            order: [],
            autoWidth: false,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]]
        };

        if (nonSortable.length > 0) {
            options.columnDefs = [{ targets: nonSortable, orderable: false }];
        }

        $table.DataTable(options);
    });
});
