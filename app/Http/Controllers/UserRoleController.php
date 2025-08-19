<?php

namespace App\Http\Controllers;

use App\Models\UserRole;
use App\Services\DeletionService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserRoleController extends Controller
{
    public function addRole(Request $request)
    {
        $adminId = getAdminIdByUserRole();
        $formFields = $request->validate([
            'user_role' => ['required'],
        ]);
        $formFields['name'] = $request->user_role;
        $formFields['guard_name'] = 'web';
        $roleIds = $request->input('role_ids');
        if ($status = Role::create($formFields)) {
            // $status->roles()->attach($roleIds);
            $status->givePermissionTo($roleIds);
            return response()->json(['error' => false, 'message' => 'Role Created successfully.', 'id' => $status->id, 'status' => $status]);
        } else {
            return response()->json(['error' => true, 'message' => 'Role couldn\'t created.']);
        }
    }
    public function list()
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $userRole = Role::orderBy($sort, $order);
        // $userRole->where('admin_id', getAdminIdByUserRole());
        if ($search) {
            $userRole = $userRole->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%');
            });
        }
        $total = $userRole->count();
        $permissions = session()->get('permissions');
        // dd($permissions);
        $userRole = $userRole
            ->paginate(request("limit"))
            ->through(
                fn ($userRole) => [
                    'id' => $userRole->id,
                    'role' => $userRole->name,


                    'actions'=>  (in_array('edit_user_role', $permissions) ?
                    '<a href="javascript:void(0);" class="edit-user-role" data-bs-toggle="modal" data-bs-target="#edit_user_role" data-id=' .$userRole->id. ' title="update" class="card-link"><i class="bx bx-edit mx-1"></i></a>' : "") .

                    (($userRole->name != 'Admin' && $userRole->name != 'Requester' && $userRole->name != 'Tasker') ?
                    (in_array('delete_user_role', $permissions) ? '<button title="delete" type="button" class="btn delete" data-id=' .$userRole->id. ' data-type="status">' .
                     '<i class="bx bx-trash text-danger mx-1"></i>' .
                     '</button>' : "") : ""),
                    'created_at' => format_date($userRole->created_at),
                    'updated_at' => format_date($userRole->updated_at),
                ]
            );


        return response()->json([
            "rows" => $userRole->items(),
            "total" => $total,
        ]);
    }

    public function get($id)
    {
        $user_role = Role::with('permissions')->findOrFail($id);
        return response()->json(['user_role' => $user_role]);
    }
    public function update(Request $request)
    {
    //    return dd($request->all());
        $user_role = Role::findOrFail($request->id);
        $formFields = $request->validate([
            'user_role' => ['required'],
        ]);

        if($request->id == 1 || $request->id == 2 || $request->id == 3)
        {
            $formFields['name'] = $user_role->name;
        }else{
            $formFields['name'] = $request->user_role;
        }
        $roleIds = $request->input('role_ids');
        if ($user_role->update($formFields)) {
            $user_role->syncPermissions($roleIds);
            return response()->json(['error' => false, 'message' => 'Role updated successfully.', 'id' => $user_role->id]);
        } else {
            return response()->json(['error' => true, 'message' => 'Role couldn\'t updated.']);
        }
    }
    public function destroy($id)
    {
        $user_role = Role::findOrFail($id);
        if ($user_role) {
            $response = DeletionService::delete(Role::class, $id, 'User Role');
            return $response;
        } else {
            return response()->json(['error' => true, 'message' => 'User Role can\'t be deleted.']);
        }
    }
}
