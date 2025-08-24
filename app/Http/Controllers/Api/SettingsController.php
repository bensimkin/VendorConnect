<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingsController extends BaseController
{
    /**
     * Get all settings
     */
    public function index(Request $request)
    {
        try {
            $query = Setting::query();

            // Filter by group
            if ($request->has('group')) {
                $query->where('group', $request->group);
            }

            // Filter by key
            if ($request->has('key')) {
                $query->where('key', 'like', '%' . $request->key . '%');
            }

            $settings = $query->orderBy('group')->orderBy('key')->get();

            return $this->sendResponse($settings, 'Settings retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving settings: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific setting
     */
    public function show($key)
    {
        try {
            $setting = Setting::where('key', $key)->first();

            if (!$setting) {
                return $this->sendNotFound('Setting not found');
            }

            return $this->sendResponse($setting, 'Setting retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving setting: ' . $e->getMessage());
        }
    }

    /**
     * Update a setting
     */
    public function update(Request $request, $key)
    {
        try {
            $setting = Setting::where('key', $key)->first();

            if (!$setting) {
                return $this->sendNotFound('Setting not found');
            }

            $validator = Validator::make($request->all(), [
                'value' => 'required',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            // Validate value based on setting type
            $value = $request->value;
            switch ($setting->type) {
                case 'boolean':
                    if (!is_bool($value) && !in_array($value, ['0', '1', 'true', 'false'])) {
                        return $this->sendValidationError(['value' => ['Value must be a boolean']]);
                    }
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
                    break;
                case 'integer':
                    if (!is_numeric($value)) {
                        return $this->sendValidationError(['value' => ['Value must be a number']]);
                    }
                    $value = (string) (int) $value;
                    break;
                case 'json':
                    if (!is_array($value)) {
                        return $this->sendValidationError(['value' => ['Value must be an array']]);
                    }
                    $value = json_encode($value);
                    break;
                default:
                    $value = (string) $value;
            }

            $setting->update([
                'value' => $value,
                'description' => $request->description ?? $setting->description
            ]);

            return $this->sendResponse($setting, 'Setting updated successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error updating setting: ' . $e->getMessage());
        }
    }

    /**
     * Get settings by group
     */
    public function getByGroup($group)
    {
        try {
            $settings = Setting::where('group', $group)
                ->orderBy('key')
                ->get();

            return $this->sendResponse($settings, 'Settings retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving settings: ' . $e->getMessage());
        }
    }

    /**
     * Get project settings
     */
    public function getProjectSettings()
    {
        try {
            $settings = Setting::where('group', 'project')
                ->orderBy('key')
                ->get();

            // Convert to key-value pairs for easier frontend consumption
            $formattedSettings = [];
            foreach ($settings as $setting) {
                $formattedSettings[$setting->key] = Setting::getValue($setting->key);
            }

            return $this->sendResponse($formattedSettings, 'Project settings retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving project settings: ' . $e->getMessage());
        }
    }
}
