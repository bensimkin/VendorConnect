<?php

namespace App\Http\Controllers\SuperAdmin;

use Carbon\Carbon;
use App\Models\Plan;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Services\DeletionService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subscriptions = Subscription::all();
        $plans = Plan::where('status', 'active')->get();
        return view('superadmin.subscriptions.list', ['subscriptions' => $subscriptions, 'plans' => $plans]);
    }
    public function list()
    {


        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $plan_id = request('plan_id');
        $status = request('status');

        $subscriptions = Subscription::orderBy($sort, $order);
        if ($plan_id) {
            $subscriptions = $subscriptions->where('plan_id', $plan_id);
        }
        if ($status) {
            $subscriptions = $subscriptions->where('status', $status);
        }

        if ($search) {
            $subscriptions = $subscriptions->where(function ($query) use ($search) {
                $query->where('payment_method', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%')
                    ->orWhere('charging_price', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($query) use ($search) {
                        $query->where('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('plan', function ($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%');
                    });
            });
        }
        $total = $subscriptions->count();
        $subscriptions = $subscriptions->paginate(request("limit"));

        $subscriptions = $subscriptions->map(function ($subscription) {
            $user = $subscription->user;
            $plan = $subscription->plan;

            switch ($subscription->status) {
                case 'active':
                    $statusBadge = '<span class="badge bg-label-primary">Active</span>';
                    break;
                case 'inactive':
                    $statusBadge = '<span class="badge bg-label-danger">Inactive</span>';
                    break;
                case 'pending':
                    $statusBadge = '<span class="badge bg-label-warning">Pending</span>';
                    break;
                default:
                    $statusBadge = '<span class="badge bg-label-secondary">' . ucfirst($subscription->status) . '</span>';
                    break;
            }
            // Extract the modules array from the features array
            $featuresArray = json_decode($subscription->features, true);
            $modules = isset($featuresArray['modules']) ? $featuresArray['modules'] : [];

            // Define the other attributes
            $otherAttributes = [
                'Max Projects' => $featuresArray['max_projects'] ?? '',
                'Max Team Members' => $featuresArray['max_team_members'] ?? '',
                'Max Workspaces' => $featuresArray['max_workspaces'] ?? '',
                'Max Clients' => $featuresArray['max_clients'] ?? ''
            ];

            // Generate the list items for all attributes
            $listItems = '';
            foreach ($otherAttributes as $attribute => $value) {
                $listItems .= '<li><strong>' . $attribute . ':</strong> ' . $value . '</li>';
            }

            // Generate the list items for modules
            $modulesListItems = '<li><strong>Modules:</strong></li>';
            $modulesListItems .= '<ul>';
            foreach ($modules as $module) {
                $capitalizedModule = ucfirst($module);
                $modulesListItems .= '<li>' . $capitalizedModule . '</li>';
            }
            $modulesListItems .= '</ul>';

            // Wrap all list items in a ul element
            $list = '<ul>' . $listItems . $modulesListItems . '</ul>';


            return [
                'id' => $subscription->id,
                'user_name' => $user->first_name . ' ' . $user->last_name,
                'plan_name' => ucfirst($plan->name),
                'tenure' => ucfirst($subscription->tenure),
                'start_date' => format_date($subscription->starts_at),
                'end_date' => format_date($subscription->ends_at),
                'payment_method' => ucwords(str_replace('_', ' ', $subscription->payment_method)),
                'features' => $list,
                'charging_price' => format_currency($subscription->charging_price),
                'charging_currency' => $subscription->charging_currency,
                'status' => $statusBadge,
            ];
        });

        return response()->json([
            "rows" => $subscriptions,
            "total" => $total,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $plans = Plan::where('status', 'active')->get();

        // Fetch users with admin role
        $users = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();
        $currency_symbol = (get_settings('general_settings')['currency_symbol']);


        return view("superadmin.subscriptions.create", ["plans" => $plans, "users" => $users, "currency_symbol" => $currency_symbol]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|exists:plans,id',
            'user_id' => 'required|exists:users,id',
            'tenure' => 'required|in:monthly,yearly,lifetime',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'payment_method' => 'required|in:offline,bank_transfer,payment_gateway',
            'features' => 'required|string',
            'charging_price' => 'required',
            'charging_currency' => 'required',
            'transaction_id' => 'required',
        ]);

        // Check if the validation fails
        if ($validator->fails()) {
            // Return validation errors to the AJAX request
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $existingSubscription = Subscription::where('user_id', $request->user_id)
            ->where('status', 'active')
            ->first();

        if ($existingSubscription) {
            // If the user already has an active subscription, return an error response
            return response()->json(['error' => 'User already has an active subscription'], 422);
        }

        $currentDate = now();

        // Convert start date and end date strings to Carbon objects
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        // Check if the current date is between the start and end dates
        if ($currentDate->gte($startDate) && $currentDate->lte($endDate)) {
            $status = 'active';
        } else {
            $status = 'inactive';
        }
        $startDate = Carbon::createFromFormat('m/d/Y', $request->start_date)->format('Y-m-d');
        $endDate = Carbon::createFromFormat('m/d/Y', $request->end_date)->format('Y-m-d');

        $subscription = new Subscription();
        $subscription->plan_id = $request->plan_id;
        $subscription->user_id = $request->user_id;
        $subscription->tenure = $request->tenure;
        $subscription->starts_at = $startDate;
        $subscription->ends_at = $endDate;
        $subscription->payment_method = $request->payment_method;
        $subscription->features = $request->features;
        $subscription->charging_price = $request->charging_price;
        $subscription->charging_currency = $request->charging_currency;
        $subscription->status = $status;

        // Add more fields as needed

        // Save the Subscription to the database
        $subscription->save();

        $transaction = new Transaction();
        $transaction->user_id = $subscription->user_id;
        $transaction->subscription_id = $subscription->id;
        $transaction->amount = $subscription->charging_price;
        $transaction->currency = $subscription->charging_currency;
        $transaction->payment_method = $subscription->payment_method;
        $transaction->status = "completed";
        $transaction->transaction_id =   $request->transaction_id;
        $transaction->save();
        // Return a success response to the AJAX request
        return response()->json(['success' => 'Subscription created successfully', 'redirect_url' => route('subscriptions.index')], 200);
    }

    /**
     * Display the specified resource.
     */
    public function get(string $id)
    {
        $subscription = Subscription::with(['user', 'plan', 'transactions'])->findOrFail($id);
        return response()->json(['subscription' => $subscription,]);


    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $subscription = Subscription::findOrFail($id);
        $users = User::where('id', $subscription->user_id)->get();
        $plans = Plan::where('status', 'active')->get();
        $currency_symbol = (get_settings('general_settings')['currency_symbol']);
        return view('superadmin.subscriptions.upgrade', ['subscription' => $subscription, 'plans' => $plans, 'users' => $users, 'currency_symbol' => $currency_symbol]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|exists:plans,id',
            'user_id' => 'required|exists:users,id',
            'tenure' => 'required|in:monthly,yearly,lifetime',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'payment_method' => 'required|in:offline,bank_transfer,payment_gateway',
            'features' => 'required|string',
            'charging_price' => 'required',
            'charging_currency' => 'required',
            'transaction_id' => 'required',
        ]);

        // Check if the validation fails
        if ($validator->fails()) {
            // Return validation errors
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Find the current subscription by ID
        $subscription = Subscription::findOrFail($id);

        // End the current subscription by setting its end date to the current date
        $subscription->ends_at = now()->toDateString();
        $subscription->status = 'inactive';
        $subscription->save();


        $currentDate = now();

        // Convert start date and end date strings to Carbon objects
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        // Check if the current date is between the start and end dates
        if ($currentDate->gte($startDate) && $currentDate->lte($endDate)) {
            $status = 'active';
        } else {
            $status = 'inactive';
        }
        $startDate = Carbon::createFromFormat(
            'm/d/Y',
            $request->start_date
        )->format('Y-m-d');
        $endDate = Carbon::createFromFormat('m/d/Y', $request->end_date)->format('Y-m-d');

        // Create a new subscription record with the provided data
        $newSubscription = new Subscription();
        $newSubscription->plan_id = $request->plan_id;
        $newSubscription->user_id = $request->user_id;
        $newSubscription->tenure = $request->tenure;
        $newSubscription->starts_at = $startDate;
        $newSubscription->ends_at = $endDate;
        $newSubscription->payment_method = $request->payment_method;
        $newSubscription->features = $request->features;
        $newSubscription->charging_price = $request->charging_price;
        $newSubscription->charging_currency = $request->charging_currency;
        $newSubscription->status = $status; // Assuming the new subscription is active
        $newSubscription->save();
        $transaction = new Transaction();
        $transaction->user_id = $newSubscription->user_id;
        $transaction->subscription_id = $newSubscription->id;
        $transaction->amount = $newSubscription->charging_price;
        $transaction->currency = $newSubscription->charging_currency;
        $transaction->status = "completed";
        $transaction->payment_method = $newSubscription->payment_method;
        $transaction->transaction_id =   $request->transaction_id;
        $transaction->save();
        // Return a success response
        return response()->json(['success' => 'Subscription updated successfully', 'redirect_url' => route('subscriptions.index')], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $response = DeletionService::delete(Subscription::class, $id, 'Record');
        return $response;
    }
    public function destroy_multiple(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:activity_logs,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);

        $ids = $validatedData['ids'];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            DeletionService::delete(Subscription::class, $id, 'Record');
        }

        return response()->json(['error' => false, 'message' => 'Record(s) deleted successfully.']);
    }
}
