'use strict';
$('#status , #filter_by_type').on('change', function () {
    $('#table').bootstrapTable('refresh');
});
function queryParams(p) {
    return {
        page: p.offset / p.limit + 1,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search,
        status: $('#status').val(),
        type: $('#filter_by_type').val()
    };
}
window.icons = {
    refresh: 'bx-refresh',
    toggleOff: 'bx-toggle-left',
    toggleOn: 'bx-toggle-right'
}
function loadingTemplate(message) {
    return '<i class="bx bx-loader-alt bx-spin bx-flip-vertical" ></i>'
}

function actionFormatter(value, row, index) {
    return [
        '<a href="' + routePrefix + '/plans/edit/' + row.id + '" title=' + label_update + '>' +
        '<i class="bx bx-edit mx-1">' +
        '</i>' +
        '</a>' +
        '<button title=' + label_delete + ' type="button" class="btn delete" data-id=' + row.id + ' data-type="plans">' +
        '<i class="bx bx-trash text-danger mx-1"></i>' +
        '</button>'
    ]
}
$(document).ready(function () {
    $('#createPlanButton').on("click", function (event) {
        event.preventDefault(); // Prevent default form submission
        // Basic validation
        let isValid = true;
        const planName = $('#planName').val().trim();
        const planDescription = $('#planDescription').val().trim();
        const maxProjects = parseInt($('#maxProjects').val());
        const maxClients = parseInt($('#maxClients').val());
        const maxTeamMembers = parseInt($('#maxTeamMembers').val());
        const maxWorkshops = parseInt($('#maxWorkshops').val());
        const selectedModules = [];
        const tenureSwitchChecked = $('#allTenuresSwitch').prop('checked');
        const isPlanFree = $('#planFreeSwitch').prop('checked');
        $('.module-checkbox').each(function () {
            if ($(this).is(':checked')) {
                selectedModules.push($(this).val());
            }
        });
        // Check for empty fields
        if (!planName || !planDescription) {
            isValid = false;
            toastr.error('Please fill in all required fields.'); // Use toastr for error message
        }
        // Check for non-numeric inputs
        if (isNaN(maxProjects) || isNaN(maxClients) || isNaN(maxTeamMembers) || isNaN(maxWorkshops)) {
            isValid = false;
            toastr.error('Maximum values must be numerical.'); // Use toastr for error message
        }
        // Check for negative values
        if (maxProjects < -1 || maxClients < -1 || maxTeamMembers < -1 || maxWorkshops < -1) {
            isValid = false;
            toastr.error('Maximum values cannot be less than -1.'); // Use toastr for error message
        }
        // Check for selected modules
        if (selectedModules.length === 0) {
            isValid = false;
            toastr.error('Please select at least one module.'); // Use toastr for error message
        }
        // Check for tenure pricing if switch is checked and plan is not free
        if (tenureSwitchChecked && !isPlanFree) {
            let prices = {}; // Object to store main prices by tenure
            // Validate main prices and store them in the prices object
            $('.tenure-price').each(function () {
                const tenure = $(this).data('tenure'); // Assuming you have a data attribute for tenure
                const price = $(this).val().trim();
                if (!price) {
                    isValid = false;
                    toastr.error('Please enter price for all tenures.'); // Use toastr for error message
                    return false; // Exit loop early
                }
                const parsedPrice = parseFloat(price);
                if (isNaN(parsedPrice) || parsedPrice < 0) {
                    isValid = false;
                    toastr.error('Price must be a non-negative numerical value.'); // Use toastr for error message
                    return false; // Exit loop early
                }
                if (isNaN(parsedPrice) || parsedPrice < 1) {
                    isValid = false;
                    toastr.error('Price must greater than 0 in paid plan.'); // Use toastr for error message
                    return false; // Exit loop early
                }
                prices[tenure] = parsedPrice; // Store the price with its tenure
            });
            // Additional validation for discounted prices
            if ($('.tenure-discounted-price').length > 0) {
                $('.tenure-discounted-price').each(function () {
                    const tenure = $(this).data('tenure'); // Assuming you have a data attribute for tenure
                    const discountedPrice = $(this).val().trim();
                    if (!discountedPrice) {
                        isValid = false;
                        toastr.error('Please enter discounted price for all tenures.'); // Use toastr for error message
                        return false; // Exit loop early
                    }
                    const parsedDiscountedPrice = parseFloat(discountedPrice);
                    if (isNaN(parsedDiscountedPrice) || parsedDiscountedPrice < 0) {
                        isValid = false;
                        toastr.error('Discounted price must be a non-negative numerical value.'); // Use toastr for error message
                        return false; // Exit loop early
                    }
                    if (parsedDiscountedPrice >= prices[tenure]) {
                        isValid = false;
                        toastr.error('Discounted price must be lower than the main price.'); // Use toastr for error message
                        return false; // Exit loop early
                    }
                });
            }
        }
        var status = $('input[name="status"]:checked').val();
        $(document).ready(function () {
            $('.status').on("click", function () {
                var status = $('input[name="status"]:checked').val();
            });
        })
        if (isValid) {
            var fileInput = document.getElementById('planImage');
            var file = fileInput.files[0];
            var tenurePrices = getTenurePrices();
            var discountedPrices = getDiscountedPrices();
            // return;
            var formData = new FormData();
            formData.append('name', planName);
            formData.append('description', planDescription);
            formData.append('max_projects', maxProjects);
            formData.append('max_clients', maxClients);
            formData.append('max_team_members', maxTeamMembers);
            formData.append('max_workspaces', maxWorkshops);
            formData.append('modules', JSON.stringify(selectedModules));
            formData.append('tenurePrices', JSON.stringify(tenurePrices));
            formData.append('discountedPrices', JSON.stringify(discountedPrices));
            formData.append('planType', $('#plan_type').val());
            formData.append('status', status);
            formData.append('plan_image', file);
            $.ajax({
                url: $('#plan-create-form').attr('action'),
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').attr('value')
                },
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    if (!response.error) {
                        toastr.success(response.message);
                        setTimeout(function () {
                            window.location = response.redirect_url;
                        }, 3000);
                    } else {
                        toastr.error(response.message);
                        setTimeout(function () {
                            window.location.reload();
                        }, 3000);
                    }
                },
                error: function (response) {
                    if (response.status === 422) {
                        var errors = response.responseJSON.errors;
                        $.each(errors, function (field, messages) {
                            toastr.error(messages.join(', '));
                        });
                    } else {
                        toastr.error('An unexpected error occurred.');
                    }
                }
            });
        }
    });
    $('#updatePlanButton').on("click", function (event) {
        event.preventDefault(); // Prevent default form submission
        // Basic validation
        let isValid = true;
        const planName = $('#planName').val().trim();
        const planDescription = $('#planDescription').val().trim();
        const maxProjects = parseInt($('#maxProjects').val());
        const maxClients = parseInt($('#maxClients').val());
        const maxTeamMembers = parseInt($('#maxTeamMembers').val());
        const maxWorkshops = parseInt($('#maxWorkshops').val());
        const selectedModules = [];
        const tenureSwitchChecked = $('#allTenuresSwitch').prop('checked');
        const isPlanFree = $('#planFreeSwitch').prop('checked');
        $('.module-checkbox').each(function () {
            if ($(this).is(':checked')) {
                selectedModules.push($(this).val());
            }
        });
        // Check for empty fields
        if (!planName || !planDescription) {
            isValid = false;
            toastr.error('Please fill in all required fields.'); // Use toastr for error message
        }
        // Check for non-numeric inputs
        if (isNaN(maxProjects) || isNaN(maxClients) || isNaN(maxTeamMembers) || isNaN(maxWorkshops)) {
            isValid = false;
            toastr.error('Maximum values must be numerical.'); // Use toastr for error message
        }
        // Check for selected modules
        if (selectedModules.length === 0) {
            isValid = false;
            toastr.error('Please select at least one module.'); // Use toastr for error message
        }
        // Check for tenure pricing if switch is checked and plan is not free
        if (tenureSwitchChecked && !isPlanFree) {
            let prices = {}; // Object to store main prices by tenure
            // Validate main prices and store them in the prices object
            $('.tenure-price').each(function () {
                const tenure = $(this).data('tenure'); // Assuming you have a data attribute for tenure
                const price = $(this).val().trim();
                if (!price) {
                    isValid = false;
                    toastr.error('Please enter price for all tenures.'); // Use toastr for error message
                    return false; // Exit loop early
                }
                const parsedPrice = parseFloat(price);
                if (isNaN(parsedPrice) || parsedPrice < 0) {
                    isValid = false;
                    toastr.error('Price must be a non-negative numerical value.'); // Use toastr for error message
                    return false; // Exit loop early
                }
                if (isNaN(parsedPrice) || parsedPrice < 1) {
                    isValid = false;
                    toastr.error('Price must greater than 0 in paid plan.'); // Use toastr for error message
                    return false; // Exit loop early
                }
                prices[tenure] = parsedPrice; // Store the price with its tenure
            });
            // Additional validation for discounted prices
            if ($('.tenure-discounted-price').length > 0) {
                $('.tenure-discounted-price').each(function () {
                    const tenure = $(this).data('tenure'); // Assuming you have a data attribute for tenure
                    const discountedPrice = $(this).val().trim();
                    if (!discountedPrice) {
                        isValid = false;
                        toastr.error('Please enter discounted price for all tenures.'); // Use toastr for error message
                        return false; // Exit loop early
                    }
                    const parsedDiscountedPrice = parseFloat(discountedPrice);
                    if (isNaN(parsedDiscountedPrice) || parsedDiscountedPrice < 0) {
                        isValid = false;
                        toastr.error('Discounted price must be a non-negative numerical value.'); // Use toastr for error message
                        return false; // Exit loop early
                    }

                    if (parsedDiscountedPrice >= prices[tenure]) {
                        isValid = false;
                        toastr.error('Discounted price must be lower than the main price.'); // Use toastr for error message
                        return false; // Exit loop early
                    }
                });
            }
        }
        var status = $('input[name="status"]:checked').val();
        $(document).ready(function () {
            $('.status').on("click", function () {
                var status = $('input[name="status"]:checked').val();
            });
        })
        if (isValid) {
            var fileInput = document.getElementById('planImage');
            var file = fileInput.files[0];
            var tenurePrices = getTenurePrices();
            var discountedPrices = getDiscountedPrices();
            console.log(tenurePrices);
            console.log(discountedPrices);
            // return;
            console.log(planName, planDescription);
            var formData = new FormData();
            formData.append('name', planName);
            formData.append('description', planDescription);
            formData.append('max_projects', maxProjects);
            formData.append('max_clients', maxClients);
            formData.append('max_team_members', maxTeamMembers);
            formData.append('max_workspaces', maxWorkshops);
            formData.append('modules', JSON.stringify(selectedModules));
            formData.append('tenurePrices', JSON.stringify(tenurePrices));
            formData.append('discountedPrices', JSON.stringify(discountedPrices));
            formData.append('plan_type', $('#plan_type').val());
            formData.append('status', status);
            if (file) {
                formData.append('plan_image', file);
            }
            $.ajax({
                url: $('#plan-update-form').attr('action'),
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').attr('value')
                },
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    if (response.error) {
                        toastr.success(response.message);
                        setTimeout(function () {
                            window.location = response.redirect_url;
                        }, 3000);
                    } else {
                        toastr.error(response.message);
                        setTimeout(function () {
                            window.location.reload();
                        }, 3000);
                    }
                },
                error: function () {
                    toastr.error('Error occurred!');
                }
            });
        }
    });
});
// Function to get tenure prices
function getTenurePrices() {
    const tenurePrices = [];
    $('.tenure-price').each(function () {
        if ($('#allTenuresSwitch').prop('checked')) {
            // If the switch is checked, set price to null
            tenurePrices.push({
                tenure: $(this).attr('id').replace('Price', ''),
                price: $(this).val().trim(),
            });
        } else {
            // If the switch is not checked, set price to 0
            tenurePrices.push({
                tenure: $(this).attr('id').replace('Price', ''),
                price: 0,
            });
        }
    });
    return tenurePrices;
}
// Function to get discounted prices
function getDiscountedPrices() {
    const discountedPrices = [];
    $('.tenure-discounted-price').each(function () {
        if ($('#allTenuresSwitch').prop('checked')) {
            discountedPrices.push({
                tenure: $(this).attr('id').replace('DiscountedPrice', ''),
                discountedPrice: $(this).val().trim(),
            });
        } else {
            discountedPrices.push({
                tenure: $(this).attr('id').replace('DiscountedPrice', ''),
                discountedPrice: 0,
            });
        }
    });
    return discountedPrices;
}
$(document).ready(function () {
    // Select all checkboxes
    $('#select-all-checkbox').change(function () {
        $('.module-checkbox').prop('checked', $(this).prop('checked'));
    });
    $('.module-checkbox').change(function () {
        if (!$(this).prop('checked')) {
            $('#select-all-checkbox').prop('checked', false);
        }
    });
    function updateSelectAllCheckbox() {
        const allChecked = $('.module-checkbox').length === $('.module-checkbox:checked').length;
        $('#select-all-checkbox').prop('checked', allChecked);
    }
    // Initial check when the page loads
    updateSelectAllCheckbox();
    $('.module-checkbox').on('change', function () {
        updateSelectAllCheckbox();
    });
});
$(document).ready(function () {
    // Hide pricing fields if switch is unchecked by default
    if (!$('#allTenuresSwitch').prop('checked')) {
        $('#plan_type').val('free');
        $('.monthly_tenure , .yearly_tenure , .lifetime_tenure').hide();
    }
    // Toggle visibility of pricing fields when switch is toggled
    $('#allTenuresSwitch').change(function () {
        if ($(this).prop('checked')) {
            $('#plan_type').val('paid');
            $('.monthly_tenure , .yearly_tenure , .lifetime_tenure').show();
        } else {
            $('#plan_type').val('free');
            $('.monthly_tenure , .yearly_tenure , .lifetime_tenure').hide();
        }
    });
});
