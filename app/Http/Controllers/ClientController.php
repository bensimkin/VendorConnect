<?php

namespace App\Http\Controllers;

use App\Models\ClientFile;
use Throwable;
use App\Models\Task;
use App\Models\User;
use App\Models\Client;
use App\Models\Project;
use App\Models\Template;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\DeletionService;
use App\Notifications\VerifyEmail;
use Spatie\Permission\Models\Role;
use App\Notifications\AccountCreation;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $workspace = Workspace::find(session()->get('workspace_id'));
        $clients = $workspace->clients ?? [];
        return view('clients.clients', ['clients' => $clients]);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('clients.create_client');
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $adminId = Auth::user()->id;
        // $adminId = 1;
        ini_set('max_execution_time', 300);

        $formFields = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'company' => 'nullable',
            'email' => ['required', 'email', 'unique:clients,email', 'unique:users,email'],
            'phone' => 'nullable',
            'country_code' => 'nullable',
            'address' => 'nullable',
            'city' => 'nullable',
            'state' => 'nullable',
            'country' => 'nullable',
            'zip' => 'nullable',
            'dob' => 'nullable',
            'doj' => 'nullable',
            'client_note' => 'nullable',
            'files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,csv,xls,xlsx,txt,ppt,pptx|max:10240',
        ]);


        if ($request->hasFile('profile')) {
            $formFields['photo'] = $request->file('profile')->store('photos', 'public');
        } else {
            $formFields['photo'] = 'photos/no-image.jpg';
        }

        if ($request->input('dob')) {
            $dob = $request->input('dob');
            $formFields['dob'] = format_date($dob, false, app('php_date_format'), 'Y-m-d');
        }

        if ($request->input('doj')) {
            $doj = $request->input('doj');
            $formFields['doj'] = format_date($doj, false, app('php_date_format'), 'Y-m-d');
        }

        $formFields['status'] = 1;
        $formFields['admin_id'] = $adminId;

        if ($client = Client::create($formFields)) {
            $formFields['client_id'] = $client->id;

            // $user = User::create($formFields);
            // $user->assignRole($request->input('role'));

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $filePath = $file->store('client_files', 'public');

                    ClientFile::create([
                        'client_id' => $client->id,
                        'file_path' => $filePath,
                    ]);
                }
            }
            Session::flash('message', 'Client created successfully.');
            return response()->json(['error' => false, 'id' => $client->id]);
        } else {
            return response()->json(['error' => true, 'message' => 'something went wrong.']);
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $workspace = Workspace::find(session()->get('workspace_id'));
        $client = Client::findOrFail($id);
        $projects = $client->projects;
        $tasks = $client->tasks()->count();
        // $users = $workspace->users;
        // $clients = $workspace->clients;
        return view('clients.client_profile', ['projects' => $projects, 'tasks' => $tasks, 'auth_user' => getAuthenticatedUser()]);
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $client = Client::findOrFail($id);
        $user = User::where('client_id', $client->id)->first();
        $title = 'Client Edit';
        $files = ClientFile::where('client_id', $client->id)->get();
        // return $user;
        $UserRoles  = Role::where('guard_name', 'web')->get();
        return view('front-end.clients_update', compact('title', 'UserRoles', 'user', 'files'))->with('client', $client);
        // return view('front-end.clients_update',compact('title','UserRoles', 'user'));
    }

    public function deleteFile($fileId)
    {
        $file = ClientFile::findOrFail($fileId);

        // Delete the file from storage
        \Storage::disk('public')->delete($file->file_path);

        // Delete the file record from the database
        $file->delete();

        Session::flash('message', 'File deleted successfully.');
        return response()->json(['error' => false, 'id' => $fileId]);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        ini_set('max_execution_time', 300);
        // $client = Client::findOrFail($id);
        $client_id = $request->id;
        $client = Client::findOrFail($client_id);
        $user = User::where('client_id', $client_id)->first();
        // $internal_purpose = $request->has('internal_purpose') && $request->input('internal_purpose') == 'on' ? 1 : 0;
        // if (!empty($request->input('password'))) {
        //     $request->merge(['password' => NULL]);
        // }

        $rules = [
            'first_name' => 'required',
            'id' => 'required',
            'last_name' => 'required',
            'company' => 'nullable',
            'email' => ['required'],
            'phone' => 'nullable',
            'country_code' => 'nullable',
            'address' => 'nullable',
            'city' => 'nullable',
            'state' => 'nullable',
            // 'role' => 'required',
            'country' => 'nullable',
            'zip' => 'nullable',
            'dob' => 'nullable',
            'doj' => 'nullable',
            'client_note' => 'nullable',
            'files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,csv,xls,xlsx,txt,ppt,pptx|max:10240',

        ];
        // if (!$internal_purpose && $client->password === NULL) {
        //     $rules['password'] = 'required|min:6';
        // } else {
        //     $rules['password'] = 'nullable';
        // }
        // $rules['password_confirmation'] = 'required_with:password|same:password';
        $formFields = $request->validate($rules);
        if ($request->hasFile('upload')) {
            if ($client->photo != 'photos/no-image.jpg' && $client->photo !== null)
                Storage::disk('public')->delete($client->photo);
            $formFields['photo'] = $request->file('upload')->store('photos', 'public');
        }
        $status = 1;

        $formFields['status'] = $status;

        // if (isset($formFields['password']) && !empty($formFields['password'])) {
        //     $password = $formFields['password'];
        //     $formFields['password'] = bcrypt($formFields['password']);
        // } else {
        //     unset($formFields['password']);
        // }
        // $formFields['internal_purpose'] = $internal_purpose;
        $client->update($formFields);
        // $user->update($formFields);
        // $user->syncRoles($request->input('role'));
        // $require_ev = 0;
        // if (!$internal_purpose && $client->email_verified_at === null && $client->email_verification_mail_sent === 0) {
        //     $require_ev = isAdminOrHasAllDataAccess() && $request->has('require_ev') && $request->input('require_ev') == 0 ? 0 : 1;
        // }
        // $send_account_creation_email = 0;
        // if (!$internal_purpose && $client->acct_create_mail_sent === 0) {
        //     $send_account_creation_email = 1;
        // }
        try {
            //     if (!$internal_purpose && $require_ev == 0) {
            //         $client->update(['email_verified_at' => now()->tz(config('app.timezone'))]);
            //         $client->update(['email_verification_mail_sent' => 1]);
            //     }
            //     if (!$internal_purpose && $require_ev == 1) {
            //         $client->notify(new VerifyEmail($client));
            //         $client->update(['email_verification_mail_sent' => 1]);
            //     }
            //     if (!$internal_purpose && $send_account_creation_email == 1 && isEmailConfigured()) {
            //         $account_creation_template = Template::where('type', 'email')
            //             ->where('name', 'account_creation')
            //             ->first();
            //         if (!$account_creation_template || ($account_creation_template->status !== 0)) {
            //             $client->notify(new AccountCreation($client, $password));
            //             $client->update(['acct_create_mail_sent' => 1]);
            //     }
            // }
        } catch (TransportExceptionInterface $e) {
            // dd($e->getMessage());
        } catch (Throwable $e) {
            // Catch any other throwable, including non-Exception errors
            dd($e->getMessage());
        }

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $filePath = $file->store('client_files', 'public');

                ClientFile::create([
                    'client_id' => $client->id,
                    'file_path' => $filePath,
                ]);
            }
        }
        Session::flash('message', 'Client details updated successfully.');
        return response()->json(['error' => false, 'id' => $client->id]);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $client = Client::findOrFail($id);

        if ($client) {
            $response = DeletionService::delete(Client::class, $id, 'Client');
            return $response;
        }
    }
    public function destroy_multiple(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:clients,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);
        $ids = $validatedData['ids'];
        $deletedClients = [];
        $deletedClientNames = [];
        $deletedUsers = [];
        $deletedUsersNames = [];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $client = Client::findOrFail($id);
            if ($client) {
                $users = User::where('client_id', $id)->get();
                foreach ($users as $user) {
                    DeletionService::delete(User::class, $user->id, 'User');
                    $deletedUsers[] = $user->id;
                    $deletedUsersNames[] = $user->first_name . ' ' . $user->last_name;
                }
                $deletedClients[] = $id;
                $deletedClientNames[] = $client->first_name . ' ' . $client->last_name;
                DeletionService::delete(Client::class, $id, 'Client');
                $client->todos()->delete();
            }
        }
        return response()->json(['error' => false, 'message' => 'Clients(s) deleted successfully.', 'id' => $deletedClients, 'titles' => $deletedClientNames]);
    }
    // public function list()
    // {
    //     $workspace = Workspace::find(session()->get('workspace_id'));
    //     $search = request('search');
    //     $sort = request('sort') ?: 'id';
    //     $order = request('order') ?: 'DESC';
    //     $type = request('type');
    //     $typeId = request('typeId');
    //     $status = isset($_REQUEST['status']) && $_REQUEST['status'] !== '' ? $_REQUEST['status'] : "";
    //     $internal_purpose = isset($_REQUEST['internal_purpose']) && $_REQUEST['internal_purpose'] !== '' ? $_REQUEST['internal_purpose'] : "";
    //     if ($type && $typeId) {
    //         if ($type == 'project') {
    //             $project = Project::find($typeId);
    //             $clients = $project->clients();
    //         } elseif ($type == 'task') {
    //             $task = Task::find($typeId);
    //             $clients = $task->project->clients();
    //         } else {
    //             $clients = $workspace->clients();
    //         }
    //     } else {
    //         $clients = $workspace->clients();
    //     }
    //     $clients = $clients->when($search, function ($query) use ($search) {
    //         return $query->where(function ($query) use ($search) {
    //             $query->where('first_name', 'like', '%' . $search . '%')
    //                 ->orWhere('last_name', 'like', '%' . $search . '%')
    //                 ->orWhere('company', 'like', '%' . $search . '%')
    //                 ->orWhere('email', 'like', '%' . $search . '%')
    //                 ->orWhere('phone', 'like', '%' . $search . '%');
    //         });
    //     });
    //     if ($status != '') {
    //         $clients = $clients->where('status', $status);
    //     }
    //     if ($internal_purpose != '') {
    //         $clients = $clients->where('internal_purpose', $internal_purpose);
    //     }
    //     $totalclients = $clients->count();
    //     $canEdit = checkPermission('edit_clients');
    //     $canDelete = checkPermission('delete_clients');
    //     $clients = $clients->select('clients.*')
    //         ->distinct()
    //         ->orderBy($sort, $order)
    //         ->paginate(request('limit'))
    //         ->through(function ($client) use ($workspace, $canEdit, $canDelete) {
    //             $actions = '';
    //             if ($canEdit) {
    //             $actions .= '<a href="' . route('clients.edit', ['id' => $client->id]) . '" title="' . get_label('update', 'Update') . '">' .
    //                     '<i class="bx bx-edit mx-1"></i>' .
    //                     '</a>';
    //         }
    //             if ($canDelete) {
    //                 $actions .= '<button title="' . get_label('delete', 'Delete') . '" type="button" class="btn delete" data-id="' . $client->id . '" data-type="clients">' .
    //                     '<i class="bx bx-trash text-danger mx-1"></i>' .
    //                     '</button>';
    //         }
    //         $actions = $actions ?: '-';
    //         $badge = '';
    //         $badge = $client->status === 1 ? '<span class="badge bg-success">' . get_label('active', 'Active') . '</span>' : '<span class="badge bg-danger">' . get_label('deactive', 'Deactive') . '</span>';
    //         $profileHtml = "<div class='avatar avatar-md pull-up' title='{$client->first_name} {$client->last_name}'><a href='" . route('clients.profile', ['id' => $client->id]) . "'><img src='" . ($client->photo ? asset('storage/' . $client->photo) : asset('storage/photos/no-image.jpg')) .
    //         "' alt='Avatar' class='rounded-circle'></a></div>";
    //             $formattedHtml = '<div class="d-flex mt-2">' .
    //                 $profileHtml .
    //                 '<div class="mx-2">' .
    //                 '<h6 class="mb-1">' .
    //                 $client->first_name . ' ' . $client->last_name . ' ' .
    //                 $badge .
    //                 '</h6>' .
    //         '<span class="text-muted">' . $client->email . '</span>';
    //             if ($client->internal_purpose == 1) {
    //                 $formattedHtml .= '<span class="badge bg-info ms-2">' . get_label('internal_purpose', 'Internal Purpose') . '</span>';
    //         }
    //             $formattedHtml .= '</div>' .
    //         '</div>';
    //         $phone = !empty($client->country_code) ? $client->country_code . ' ' . $client->phone : $client->phone;
    //         return [
    //                 'id' => $client->id,
    //                 'first_name' => $client->first_name,
    //                 'last_name' => $client->last_name,
    //                 'company' => $client->company,
    //                 'email' => $client->email,
    //             'phone' => $phone,
    //             'profile' => $formattedHtml,
    //             'status' => $client->status,
    //             'internal_purpose' => $client->internal_purpose,
    //             'created_at' => format_date($client->created_at, true),
    //             'updated_at' => format_date($client->updated_at, true),
    //             'assigned' => '<div class="d-flex justify-content-start align-items-center">' .
    //             '<div class="text-center mx-4">' .
    //             '<a href="javascript:void(0);" class="viewAssigned" data-type="projects" data-id="' . 'client_' . $client->id . '" data-client="' . $client->first_name . ' ' . $client->last_name . '">' .
    //             '<span class="badge rounded-pill bg-primary">' . (isAdminOrHasAllDataAccess('client', $client->id) ? count($workspace->projects) : count($client->projects)) . '</span>' .
    //             '</a>' .
    //             '<div>' . get_label('projects', 'Projects') . '</div>' .
    //             '</div>' .
    //             '<div class="text-center">' .
    //             '<a href="javascript:void(0);" class="viewAssigned" data-type="tasks" data-id="' . 'client_' . $client->id . '" data-client="' . $client->first_name . ' ' . $client->last_name . '">' .
    //             '<span class="badge rounded-pill bg-primary">' . (isAdminOrHasAllDataAccess('client', $client->id) ? count($workspace->tasks) : $client->tasks()->count()) . '</span>' .
    //             '</a>' .
    //             '<div>' . get_label('tasks', 'Tasks') . '</div>' .
    //             '</div>' .
    //             '</div>',
    //             'actions' => $actions
    //             ];
    //         });
    //     return response()->json([
    //         'rows' => $clients->items(),
    //         'total' => $totalclients,
    //     ]);
    // }

    public function list()
    {
        $search = request('search');
        $sort = request('sort') ?: 'id';
        $order = request('order') ?: 'DESC';
        $type = request('type');
        $typeId = request('typeId');
        $status = isset($_REQUEST['status']) && $_REQUEST['status'] !== '' ? $_REQUEST['status'] : "";
        $clients = Client::when($search, function ($query) use ($search) {
            return $query->where(function ($query) use ($search) {
                $query->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('company', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        });
        if ($status != '') {
            $clients = $clients->where('status', $status);
        }
        $totalclients = $clients->count();
        $clients = $clients->select('clients.*')
            ->distinct()
            ->orderBy($sort, $order)
            ->paginate(request('limit'))
            ->through(function ($client) {
                $permissions = session()->get('permissions');
                $actions = '';
                // $actions .= '<a href="' . route('clients.edit', ['id' => $client->id]) . '" title="' . get_label('update', 'Update') . '">' .
                //     '<i class="bx bx-edit mx-1"></i>' .
                //     '</a>';
                // $actions .= '<button title="' . get_label('delete', 'Delete') . '" type="button" class="btn delete" data-id="' . $client->id . '" data-type="clients">' .
                //     '<i class="bx bx-trash text-danger mx-1"></i>' .
                //     '</button>';
                // $actions = $actions ?: '-';
                $badge = '';
                $badge = $client->status === 1 ? '<span class="badge bg-success">' . get_label('active', 'Active') . '</span>' : '<span class="badge bg-danger">' . get_label('deactive', 'Deactive') . '</span>';
                $profileHtml = "<div class='avatar avatar-md pull-up' title='{$client->first_name} {$client->last_name}'><img src='" . ($client->photo ? asset('storage/' . $client->photo) : asset('storage/photos/no-image.jpg')) .
                    "' alt='Avatar' class='rounded-circle'></div>";
                $formattedHtml = '<div class="d-flex mt-2">' .
                    $profileHtml .
                    '<div class="mx-2">' .
                    '<h6 class="mb-1">' .
                    $client->first_name . ' ' . $client->last_name . ' ' .
                    $badge .
                    '</h6>' .
                    '<span class="text-muted">' . $client->email . '</span>';
                if ($client->internal_purpose == 1) {
                    $formattedHtml .= '<span class="badge bg-info ms-2">' . get_label('internal_purpose', 'Internal Purpose') . '</span>';
                }
                $formattedHtml .= '</div>' .
                    '</div>';
                $phone = !empty($client->country_code) ? $client->country_code . ' ' . $client->phone : $client->phone;
                return [
                    'id' => $client->id,
                    'first_name' => $client->first_name,
                    'last_name' => $client->last_name,
                    'company' => $client->company,
                    'email' => $client->email,
                    'phone' => $phone,
                    'profile' => $formattedHtml,
                    'status' => $client->status,
                    'actions' => (in_array('edit_clients', $permissions) ?
                        '<a href="' . route('clients.edit', ['id' => $client->id]) . '" title="update">' .
                        '<i class="bx bx-edit mx-1"></i>' .
                        '</a>' : "") .

                        (in_array('delete_clients', $permissions) ?
                            '<button title="' . get_label('delete', 'Delete') . '" type="button" class="btn delete" data-id="' . $client->id . '" data-type="clients">' .
                            '<i class="bx bx-trash text-danger mx-1"></i>' .
                            '</button>' : ""),

                    'created_at' => format_date($client->created_at, true),
                    'updated_at' => format_date($client->updated_at, true),
                ];
            });
        return response()->json([
            'rows' => $clients->items(),
            'total' => $totalclients,
        ]);
    }

    public function verify_email(EmailVerificationRequest $request)
    {
        $request->fulfill();
        return redirect(route('home.index'))->with('message', 'Email verified successfully.');
    }
    public function get($id)
    {
        $client = Client::findOrFail($id);
        return response()->json(['client' => $client]);
    }
    public function permissions(Request $request, Client $client)
    {
        $clientId = $client->id;
        $role = $client->roles[0]['name'];
        $role = Role::where('name', $role)->first();
        $mergedPermissions = collect();
        // Loop through each role to merge its permissions
        $mergedPermissions = $mergedPermissions->merge($role->permissions);
        // If you also want to include permissions directly assigned to the user
        $mergedPermissions = $mergedPermissions->merge($client->permissions);
        return view('clients.permissions', ['client' => $client, 'mergedPermissions' => $mergedPermissions, 'role' => $role]);
    }
    public function update_permissions(Request $request, Client $client)
    {
        $client->syncPermissions($request->permissions);
        return redirect()->back()->with(['message' => 'Permissions updated successfully']);
    }
}
