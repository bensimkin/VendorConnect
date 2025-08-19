<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PreferenceController extends Controller
{
    public function index()
    {
        return view('settings.preferences');
    }
    public function saveColumnVisibility(Request $request)
{
    // Validate incoming request data
    $validator = Validator::make($request->all(), [
        'type' => 'required|string|max:255',
        'visible_columns' => 'required|json' // assuming visible_columns is a JSON string
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => true, 'message' => $validator->errors()->first()], 422);
    }

    try {
        // Get the authenticated user's ID
        $userId = getAuthenticatedUser(true,true);

        // Get the table type and visible columns from the request
        $type = $request->input('type');
        $visibleColumns = $request->input('visible_columns');

        // Update or insert the column visibility preferences
        DB::table('user_client_preferences')
            ->updateOrInsert(
                ['user_id' => $userId, 'table_name' => $type],
                ['visible_columns' => $visibleColumns]
            );

        return response()->json(['error' => false, 'message' => 'Column visibility saved successfully.']);

    } catch (\Exception $e) {
        return response()->json(['error' => true, 'message' => 'An error occurred while saving column visibility.'], 500);
    }
}

    public function getPreferences(Request $request)
    {
        $userId = getAuthenticatedUser()->id;
        $tableName = $request->input('table_name');
        $prefix = isClient() ? 'c_' : 'u_';

        // Fetch preferences from database
        $fields = DB::table('preferences')
            ->where('user_id', $prefix . $userId)
            ->where('table_name', $tableName)
            ->value('fields');

        return response()->json(['fields' => json_decode($fields)]);
    }
    public function saveNotificationPreferences(Request $request)
    {
        try {
            // Get the authenticated user's ID
            $userId = getAuthenticatedUser(true, true);
            $enabledNotifications = $request->has('enabled_notifications') ? json_encode($request->input('enabled_notifications')) : NULL;
            DB::table('user_client_preferences')
                ->updateOrInsert(
                    ['user_id' => $userId, 'table_name' => 'notification_preference'],
                    ['enabled_notifications' => $enabledNotifications]
                );

            return response()->json(['error' => false, 'message' => 'Notification preference saved successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => 'An error occurred while saving notification preference:' . $e->getMessage()], 500);
        }
    }

}
