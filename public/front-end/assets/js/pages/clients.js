'use strict';

function queryParams(p) {
    return {
        "status": $('#client_status_filter').val(),
        "internal_purpose": $('#client_internal_purpose_filter').val(),
        page: p.offset / p.limit + 1,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}



window.icons = {
    refresh: 'bx-refresh'
}

function loadingTemplate(message) {
    return '<i class="bx bx-loader-alt bx-spin bx-flip-vertical" ></i>'
}

function nameFormatter(value, row, index) {
    return [row.first_name, row.last_name].join(' ')
}


$('#client_status_filter, #client_internal_purpose_filter').on('change', function (e) {
    e.preventDefault();
    $('#table').bootstrapTable('refresh');
});
