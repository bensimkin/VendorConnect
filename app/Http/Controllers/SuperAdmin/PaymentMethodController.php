<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pay_pal_settings = get_settings('pay_pal_settings');

        $phone_pe_settings = get_settings('phone_pe_settings');
        $stripe_settings = get_settings('stripe_settings');
        $paystack_settings = get_settings('paystack_settings');
        return view('settings.payment_method_settings', ['pay_pal_settings' => $pay_pal_settings, 'stripe_settings' => $stripe_settings, 'paystack_settings' => $paystack_settings, 'phone_pe_settings' => $phone_pe_settings]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    // Import the Setting model at the top of your controller

    public function store_paypal_settings(Request $request)
    {   
        
        // Validate the incoming request
        $request->validate([
            'paypal_client_id' => ['required'],
            'paypal_secret_key' => ['required'],
            'paypal_business_email' => ['required'],
            'payment_mode' => ['required'],
            'currency_code' => ['required'],
            'notification_url' => ['nullable'],
        ]);
        
        // Fetch existing PayPal settings if they exist
        $fetched_data = Setting::where('variable', 'pay_pal_settings')->first();

        // Extract form values except for certain fields
        $form_val = $request->except('_token', '_method', 'redirect_url');

        // Prepare data to be stored in the database
        $data = [
            'variable' => 'pay_pal_settings',
            'value' => json_encode($form_val),
        ];

        // If no existing PayPal settings found, create new; otherwise, update existing
        if ($fetched_data == null) {
            Setting::create($data);
        } else {
            $fetched_data->update($data);
        }

        // Flash success message and return JSON response
        // Session::flash('message', 'PayPal settings saved successfully.');

        return response()->json((['error' => false, 'message' => 'PayPal Settings Updated Successfully']));
    }

    public function store_phonepe_settings(Request $request)
    {
        $request->validate([
            'merchant_id' => ['required'],
            'app_id' => ['required'],
            'phonepe_mode' => ['required'],
            'payment_endpoint_url' => ['required'],
            'salt_index' => ['required'],
            'salt_key' => ['required'],
        ]);


        $fetched_data = Setting::where('variable', 'phone_pe_settings')->first();


        $form_val = $request->except('_token', '_method', 'redirect_url');


        $data = [
            'variable' => 'phone_pe_settings',
            'value' => json_encode($form_val),
        ];
        if ($fetched_data == null) {
            Setting::create($data);
        } else {
            $fetched_data->update($data);
        }

        // Session::flash('message', 'PhonePe settings saved successfully.');
        // return Redirect::back()->withFragment('phone_pe');
        return response()->json((['error' => false, 'message' => 'PhonePe Settings Updated Successfully']));

    }
    public function store_stripe_settings(Request $request)
    {

        $request->validate([
            'stripe_publishable_key' => ['required'],
            'stripe_secret_key' => ['required'],
            'payment_mode' => ['required'],
            'currency_code' => ['required'],
            'payment_endpoint_url' => ['required'],
            'stripe_webhook_secret_key' => ['required'],
        ]);

        $fetched_data = Setting::where('variable', 'stripe_settings')->first();

        $form_val = $request->except('_token', '_method', 'redirect_url');

        $data = [
            'variable' => 'stripe_settings',
            'value' => json_encode($form_val),
        ];
        if ($fetched_data == null) {
            Setting::create($data);
        } else {
            $fetched_data->update($data);
        }

        // Session::flash('message', 'Stripe settings saved successfully.');
        // return Redirect::back()->withFragment('stripe');
        return response()->json((['error' => false, 'message' => 'Stripe Settings Updated Successfully']));

    }
    public function store_paystack_settings(Request $request)
    {
        $request->validate([
            'paystack_key_id' => ['required'],
            'paystack_secret_key' => ['required'],
            'payment_endpoint_url' => ['required'],

        ]);

        $fetched_data = Setting::where('variable', 'paystack_settings')->first();
        $form_val = $request->except('_token', '_method', 'redirect_url');
        $data = [
            'variable' => 'paystack_settings',
            'value' => json_encode($form_val),
        ];

        if ($fetched_data == null) {
            Setting::create($data);
        } else {
            $fetched_data->update($data);
        }

        // Session::flash('message', 'Paystack settings saved successfully.');
        // return Redirect::back()->withFragment('pay_stack');
        return response()->json((['error' => false, 'message' => 'Pay Stack Settings Updated Successfully']));

    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
