<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Admin;
use App\Models\User;
use App\Models\Status;
use App\Models\Priority;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Services\MemberValidationService;

class CompanyController extends BaseController
{
    /**
     * Register a new company with admin user
     */
    public function register(Request $request)
    {
        try {
            // Validate Mastermind membership first
            $memberService = new MemberValidationService();
            if (!$memberService->isActiveMember($request->email)) {
                return $this->sendError('VendorConnect is only available to active Mastermind members. Please use your Mastermind member email or contact support.', [], 403);
            }

            $validator = Validator::make($request->all(), [
                'company_name' => 'required|string|max:255',
                'company_email' => 'nullable|email|max:255',
                'company_phone' => 'nullable|string|max:50',
                'company_address' => 'nullable|string|max:500',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            DB::beginTransaction();

            // Create the admin user
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => 1,
            ]);

            // Assign admin role
            $adminRole = Role::where('name', 'admin')->first();
            if ($adminRole) {
                $user->assignRole($adminRole);
            }

            // Create the Admin record (company)
            $admin = Admin::create([
                'user_id' => $user->id,
                'company_name' => $request->company_name,
                'company_email' => $request->company_email,
                'company_phone' => $request->company_phone,
                'company_address' => $request->company_address,
            ]);

            // Create default statuses for the new company
            $defaultStatuses = [
                ['title' => 'Pending', 'slug' => 'pending', 'admin_id' => $admin->id],
                ['title' => 'In Progress', 'slug' => 'in-progress', 'admin_id' => $admin->id],
                ['title' => 'Completed', 'slug' => 'completed', 'admin_id' => $admin->id],
                ['title' => 'Accepted', 'slug' => 'accepted', 'admin_id' => $admin->id],
                ['title' => 'Rejected', 'slug' => 'rejected', 'admin_id' => $admin->id],
                ['title' => 'Active', 'slug' => 'active', 'admin_id' => $admin->id],
                ['title' => 'Inactive', 'slug' => 'inactive', 'admin_id' => $admin->id],
                ['title' => 'Submitted', 'slug' => 'submitted', 'admin_id' => $admin->id],
                ['title' => 'Archive', 'slug' => 'archive', 'admin_id' => $admin->id],
            ];

            foreach ($defaultStatuses as $status) {
                Status::create($status);
            }

            // Create default priorities for the new company
            $defaultPriorities = [
                ['title' => 'Low', 'slug' => 'low', 'admin_id' => $admin->id],
                ['title' => 'Medium', 'slug' => 'medium', 'admin_id' => $admin->id],
                ['title' => 'High', 'slug' => 'high', 'admin_id' => $admin->id],
                ['title' => 'Urgent', 'slug' => 'urgent', 'admin_id' => $admin->id],
                ['title' => 'Critical', 'slug' => 'critical', 'admin_id' => $admin->id],
            ];

            foreach ($defaultPriorities as $priority) {
                Priority::create($priority);
            }

            // Create default workspace for the new company
            \App\Models\Workspace::create([
                'admin_id' => $admin->id,
                'name' => 'Default Workspace',
                'is_primary' => true,
            ]);

            DB::commit();

            // Generate token for the new user
            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->sendResponse([
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                ],
                'company' => [
                    'id' => $admin->id,
                    'company_name' => $admin->company_name,
                    'company_email' => $admin->company_email,
                ],
                'token' => $token,
            ], 'Company registered successfully', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendServerError('Error registering company: ' . $e->getMessage());
        }
    }

    /**
     * Get current company information
     */
    public function show()
    {
        try {
            $adminId = getAdminIdByUserRole();
            
            $admin = Admin::with('user')->find($adminId);
            
            if (!$admin) {
                return $this->sendNotFound('Company not found');
            }

            return $this->sendResponse([
                'id' => $admin->id,
                'company_name' => $admin->company_name,
                'company_email' => $admin->company_email,
                'company_phone' => $admin->company_phone,
                'company_address' => $admin->company_address,
                'owner' => [
                    'id' => $admin->user->id,
                    'name' => $admin->user->first_name . ' ' . $admin->user->last_name,
                    'email' => $admin->user->email,
                ],
                'created_at' => $admin->created_at,
            ], 'Company information retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving company: ' . $e->getMessage());
        }
    }
}

