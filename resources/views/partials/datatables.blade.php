<link rel="stylesheet" href="https://cdn.datatables.net/3.0.1/css/dataTables.dataTables.min.css">
<script src="https://cdn.datatables.net/3.0.1/js/dataTables.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.data-table').forEach(function (table) {
            if (table.dataset.serverSide === 'true') {
                return;
            }

            if (!table.dataset.dtInitialized) {
                const options = {
                    searching: true,
                    paging: true,
                    ordering: true,
                    info: false,
                    lengthChange: false
                };

                if (table.dataset.orderColumn !== undefined) {
                    options.order = [[
                        Number(table.dataset.orderColumn),
                        table.dataset.orderDirection || 'asc'
                    ]];
                }

                new DataTable(table, options);
                table.dataset.dtInitialized = 'true';
            }
        });
    });
</script>
