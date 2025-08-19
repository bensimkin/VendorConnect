/**
 * Dashboard Analytics
 */
'use strict';

function queryParamsProjectMedia(p) {
    return {
        page: p.offset / p.limit + 1,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}

document.addEventListener('DOMContentLoaded', function () {
    var series = JSON.parse(seriesData);
    var labels = JSON.parse(labelsData);
    var colors = JSON.parse(statusColors);

    var options = {
        chart: {
            type: 'donut'
        },
        series: series,
        labels: labels,
        colors: colors.map(color => getBootstrapColor(color)), // Map color names to their Bootstrap equivalents
        legend: {
            position: 'top'
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '70%'
                }
            }
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    };

    var chart = new ApexCharts(document.querySelector("#taskSummaryChart"), options);
    chart.render();
});

// Function to map color names to Bootstrap color codes
function getBootstrapColor(colorName) {
    const colorMap = {
        'primary': '#0d6efd',
        'secondary': '#6c757d',
        'success': '#198754',
        'danger': '#dc3545',
        'warning': '#ffc107',
        'info': '#0dcaf0',
        'light': '#f8f9fa',
        'dark': '#212529'
    };
    return colorMap[colorName] || colorName; // Return the mapped color or the original if not found
}
function queryParams(p) {
    return {
        "user_id": $('#user_filter').val(),
        "client_id": $('#client_filter').val(),
        "activity": $('#activity_filter').val(),
        "type": 'project',
        "type_id": $('#type_id').val(),
        "date_from": $('#activity_log_between_date_from').val(),
        "date_to": $('#activity_log_between_date_to').val(),
        page: p.offset / p.limit + 1,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}

function queryParamsProjectMilestones(p) {
    return {
        "type_id": $('#type_id').val(),
        "start_date_from": $('#start_date_from').val(),
        "start_date_to": $('#start_date_to').val(),
        "end_date_from": $('#end_date_from').val(),
        "end_date_to": $('#end_date_to').val(),
        "status": $('#status_filter').val(),
        page: p.offset / p.limit + 1,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}

function actionsFormatterProjectMilestones(value, row, index) {
    return [
        '<a href="javascript:void(0);" class="edit-milestone" data-bs-toggle="modal" data-bs-target="#edit_milestone_modal" data-id=' + row.id + ' title=' + label_update + ' class="card-link"><i class="bx bx-edit mx-1"></i></a>' +
        '<button title=' + label_delete + ' type="button" class="btn delete" data-id=' + row.id + ' data-type="milestone" data-table="project_milestones_table">' +
        '<i class="bx bx-trash text-danger mx-1"></i>' +
        '</button>'
    ]
}

function actionsFormatter(value, row, index) {
    return [
        '<button title=' + label_delete + ' type="button" class="btn delete" data-id=' + row.id + ' data-type="activity-log" data-table="activity_log_table">' +
        '<i class="bx bx-trash text-danger mx-1"></i>' +
        '</button>'
    ]
}




$('#start_date_between').on('apply.daterangepicker', function (ev, picker) {
    var startDate = picker.startDate.format('YYYY-MM-DD');
    var endDate = picker.endDate.format('YYYY-MM-DD');

    $('#start_date_from').val(startDate);
    $('#start_date_to').val(endDate);

    $('#project_milestones_table').bootstrapTable('refresh');
});

$('#start_date_between').on('cancel.daterangepicker', function (ev, picker) {
    $('#start_date_from').val('');
    $('#start_date_to').val('');
    $('#project_milestones_table').bootstrapTable('refresh');
    $('#start_date_between').val('');
});

$('#end_date_between').on('apply.daterangepicker', function (ev, picker) {
    var startDate = picker.startDate.format('YYYY-MM-DD');
    var endDate = picker.endDate.format('YYYY-MM-DD');

    $('#end_date_from').val(startDate);
    $('#end_date_to').val(endDate);

    $('#project_milestones_table').bootstrapTable('refresh');
});
$('#end_date_between').on('cancel.daterangepicker', function (ev, picker) {
    $('#end_date_from').val('');
    $('#end_date_to').val('');
    $('#project_milestones_table').bootstrapTable('refresh');
    $('#end_date_between').val('');
});


$('#status_filter').on('change', function (e) {
    e.preventDefault();
    $('#project_milestones_table').bootstrapTable('refresh');
});

$('#milestone_progress').on('change', function (e) {
    var rangeValue = $(this).val();
    $('.milestone-progress').text(rangeValue + '%');
});
