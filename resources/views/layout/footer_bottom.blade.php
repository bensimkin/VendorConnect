<div id="section-not-to-print">
    <footer class="content-footer footer bg-footer-theme mt-2 container-fluid">
        <div class="container-fluid d-flex flex-wrap justify-content-between  flex-md-row flex-column">
            <div class=" mb-md-0 d-flex align-items-start justify-content-between">
                ©
                2024
                , <p>made with by <a href="#" target="_blank"
                        rel="noopener">Developers</a></p>
            </div>
        </div>
    </footer>
</div>
<script>
$(document).on('click', '.delete', function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    var type = $(this).data('type');
    var reload = $(this).data('reload'); // Get the value of data-reload attribute
    if (typeof reload !== 'undefined' && reload === true) {
        reload = true;
    } else {
        reload = false;
    }
    var tableID = $(this).data('table') || 'table';
    var destroy = type == 'users' ? 'delete_user' : (type == 'contract-type' ? 'delete-contract-type' : (type == 'project-media' || type == 'task-media' ? 'delete-media' : (type == 'expense-type' ? 'delete-expense-type' : (type == 'milestone' ? 'delete-milestone' : 'destroy'))));
    type = type == 'contract-type' ? 'contracts' : (type == 'project-media' ? 'projects' : (type == 'task-media' ? 'tasks' : (type == 'expense-type' ? 'expenses' : (type == 'milestone' ? 'projects' : type))));
    var urlPrefix = window.location.pathname.split('/')[1];
    $('#deleteModal').modal('show'); // show the confirmation modal
    $('#deleteModal').off('click', '#confirmDelete');
    $('#deleteModal').on('click', '#confirmDelete', function (e) {
        $('#confirmDelete').html(label_please_wait).attr('disabled', true);
        $.ajax({
            url: '/' + urlPrefix + '/'  + destroy + '/' + id,
            type: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}' // Laravel requires a CSRF token for DELETE requests
            },
            success: function (response) {
                $('#confirmDelete').html(label_yes).attr('disabled', false);
                $('#deleteModal').modal('hide');
                if (response.error == false) {
                    if (reload) {
                        location.reload();
                    } else {
                        console.log(tableID);
                        toastr.success(response.message);
                        if (tableID) {
                            $('#' + tableID).bootstrapTable('refresh');
                        }
                        else {
                            location.reload();
                        }
                    }
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (data) {
                $('#confirmDelete').html(label_yes).attr('disabled', false);
                $('#deleteModal').modal('hide');
                toastr.error(label_something_went_wrong);
            }
        });
    });
});



</script>
