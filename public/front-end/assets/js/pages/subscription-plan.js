function queryParams(p) {
    return {
        page: p.offset / p.limit + 1,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}
function SubscriptionHistory(p) {
    return {
        page: p.offset / p.limit + 1,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}
var paypalButtonsRendered = false;
$(document).ready(function () {
    $('a[data-toggle="content"]').on('click', function (e) {
        e.preventDefault();

        var target = $(this).attr('href');
        $('.content').removeClass('active');
        $(target).addClass('active');
    });
});

$(document).ready(function () {
    $('input[name="priceToggle"]').change(function () {
        var selectedTenure = $(this).attr('id');
        $('.monthly-price, .yearly-price, .lifetime-price').addClass('d-none');
        $('.' + selectedTenure + '-price').removeClass('d-none');
    });
});



$(document).ready(function () {
    $('.checkout_btn').on('click', function () {

        var plan_id = $(this).data('planid'); // Use $(this) to reference the clicked button
        var tenure = $('input[name=priceToggle]:checked').attr('id');

        window.location = 'checkout/' + plan_id + '/' + tenure
    });
});


$(document).ready(function () {
    // Get the total price
    var totalPrice = parseFloat($('input[name="total_price"]').val());
    var planName = $('input[name="plan_name"]').val();
    var planId = $('input[name="plan_id"]').val();
    var tenure = $('input[name="tenure"]').val();
    var currencySymbol = $('input[name="currency_symbol"]').val();
    if (totalPrice === 0) {


        // Show the order summary and proceed button
        $('#paymentMethod').text('Payment Method: Free Plan ');
        $('#finalPlan').text('Plan Name: ' + planName);
        $('#finalPrice').text('Total Price: ' + currencySymbol + totalPrice);
        $('#orderSummaryDiv').addClass('bg-label-primary');
        $('#proceedPaymentBtn').removeClass('d-none');
        $('#changePlanBtn').removeClass('d-none');
    }


});
$(document).ready(function () {
    // Add event listener to payment method radio buttons
    $('input[name="options"]').change(function () {
        // Get the selected payment method
        var paymentMethod = $('input[name="options"]:checked').val();
        var planName = $('input[name="plan_name"]').val();
        var planId = $('input[name="plan_id"]').val();
        var tenure = $('input[name="tenure"]').val();
        var currencySymbol = $('input[name="currency_symbol"]').val();
        var totalPrice = $('input[name="total_price"]').val();



        // Update the order summary dynamically
        $('#paymentMethod').text('Payment Method: ' + paymentMethod);
        $('#finalPlan').text('Plan Name: ' + planName);
        $('#finalPrice').text('Total Price: ' + currencySymbol + totalPrice);
        $('#orderSummaryDiv').addClass('bg-label-primary');
        $('#proceedPaymentBtn').removeClass('d-none');
        $('#changePlanBtn').removeClass('d-none');


        switch (paymentMethod) {
            case (PaymentMethod = "stripe"): {
                $('#paypal-button-container').empty();
                paypalButtonsRendered = false;
                $('#stripe_checkout').show();
            } break;
            case (PaymentMethod = "paystack"): {
                $('#paypal-button-container').empty();
                paypalButtonsRendered = false;
                $('#stripe_checkout').hide();
            } break;
            case (PaymentMethod = "phonepe"): {
                $('#paypal-button-container').empty();
                paypalButtonsRendered = false;
                $('#stripe_checkout').hide();
            } break;
            case (PaymentMethod = "pay_pal"): {
                $('#stripe_checkout').hide();
            } break;
        };

    });
});


// Define the PayPal buttons rendering function
function renderPaypalButtons(finalPrice, transaction_id, successUrl) {
    paypal.Buttons({
        createOrder: function (data, actions) {
            // Set up the transaction details
            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: finalPrice, // The amount to be charged
                    },
                    reference_id: transaction_id,
                }]
            });
        },
        onApprove: function (data, actions) {
            // Capture the funds when the customer approves the payment

            return actions.order.capture().then(function (details) {
                // Handle the successful payment

                toastr.success('Transaction completed by ' + details.payer.name.given_name);

                $.ajax({
                    url: successUrl, // Replace with your endpoint URL
                    method: 'POST', // or 'GET', depending on your requirements
                    data: details,
                    success: function (response) {
                        // Handle the successful AJAX response
                        window.location.href = response.redirectUrl;
                    },
                    error: function (xhr, status, error) {
                        // Handle the AJAX error
                        console.error('AJAX request failed:', error);
                    }
                });

            });
        },
        onCancel: function (data) {
            // Handle the cancellation of the payment
            toastr.error('Payment canceled by the user.');
            setTimeout(function () {
                location.reload();
            }, 2000);

        }
    }).render('#paypal-button-container');
}
function renderStripePopup(clientSecret, publicKey) {
    // Initialize Stripe.js with your publishable key
    var stripe = Stripe(publicKey);

    // Create the checkout instance
    stripe.initEmbeddedCheckout({
        clientSecret,
    }).then(function (checkout) {
        // Mount the checkout instance
        checkout.mount('#stripe_checkout');

        // Get the iframe element
        var iframe = document.querySelector('#stripe_checkout iframe');

        // Add event listener for iframe load event
        iframe.addEventListener('load', function () {
            // Get the contentWindow from the iframe
            var iframeWindow = iframe.contentWindow;

            // Add event listener for message event to detect checkout closure
            iframeWindow.addEventListener('message', function (event) {
                // Check if the event data indicates checkout closure
                if (event.data === 'embedded_checkout.closed') {
                    // Checkout closed
                    console.log('Checkout closed');
                    // Handle checkout closure
                }
            });
        });
    }).catch(function (error) {
        // Handle initialization errors
        console.error('Initialization error:', error);
    });
}
function initiatePaystackCheckout(response) {
    var handler = PaystackPop.setup({
        key: response.publicKey, // Replace with your Paystack public key
        email: response.email,
        amount: response.amount, // Amount in kobo
        currency: response.currency,
        ref: response.reference,
        metadata: JSON.parse(response.metadata),
        callback: function (response) {
            // Handle successful payment here
            console.log(response);
            if (response.status == "success" && response.message == "Approved") {

                toastr.success('Paystack Payment Success');
                setTimeout(function () {
                    window.location.href = response.redirecturl;
                }, 2000);

            }

            // Send data to callback URL for verification

        },
        onClose: function () {
            // Handle when the Paystack dialog is closed
            toastr.error('Paystack dialog closed');
        }
    });
    handler.openIframe();
}

$(document).ready(function () {

    $('#paymentIntializeBtn').on("click", function () {
        // Get necessary data
        var finalPrice = parseFloat($('input[name="total_price"]').val());
        var paymentMethod = '';
        if (finalPrice > 0) {
            paymentMethod = $('input[name="options"]:checked').val();
        } else {
            paymentMethod = 'free_plan'; // Set payment method to 'free_plan' if price is 0
        }

        if (paymentMethod == 'pay_pal') {
            var paymentMethod = 'paypal';
        }

        // return;
        var planId = $('input[name="plan_id"]').val();
        var tenure = $('input[name="tenure"]').val();
        var user_id = $('input[name="user_id"]').val();
        var currency_symbol = $('input[name="currency_symbol"]').val();
        var url = $(this).data('url');


        // Ajax request
        $.ajax({
            url: url,
            type: 'post',
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').attr('value')
            },
            data: {
                payment_method: paymentMethod,
                plan_id: planId,
                tenure: tenure,
                user_id: user_id,
                currency_symbol: currency_symbol
            },
            success: function (response) {
                // Handle success response
                if (response) {
                    if (response.payment_method === 'free_plan') {
                        toastr.success(response.message);
                        var redirectUrl = response.redirect_url;
                        setTimeout(function () {
                            window.location.href = redirectUrl;
                        }, 2000);
                    }
                    if (response.payment_method === 'phonepe') {
                        toastr.success('Payment initiation successful.');
                        var redirectUrl = response.data.instrumentResponse.redirectInfo.url;
                        setTimeout(function () {
                            window.location.href = redirectUrl;
                        }, 2000);
                    }
                    if (response.payment_method === 'paystack') {
                        initiatePaystackCheckout(response)
                    }
                    if (response.payment_method === 'stripe') {
                        renderStripePopup(response.client_secret, response.publicKey);

                    }
                    if (response.payment_method === 'paypal') {

                        if (!paypalButtonsRendered) {
                            renderPaypalButtons(response.finalPrice, response.transaction_id, response.success_url);
                            paypalButtonsRendered = true;
                        }
                    }

                } else {
                    toastr.error('Empty response received.');
                }
            },
            error: function (xhr, status, error) {
                // Handle error response
                var errors = xhr.responseJSON.errors;
                if (errors) {
                    $.each(errors, function (key, value) {
                        toastr.error(value);
                    });
                } else {
                    if (xhr.responseJSON.error) {
                        console.log(xhr.responseJSON.error);
                        toastr.error(xhr.responseJSON.error);
                    } else {
                        toastr.error('An error occurred. Please try again.');
                    }
                }
            }
        });
    });
});
