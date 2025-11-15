<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'key',
        'value',
        'type',
        'group',
        'description'
    ];

    /**
     * Get a setting value by key
     */
    public static function getValue($key, $default = null, $adminId = null)
    {
        if ($adminId === null) {
            $adminId = getAdminIdByUserRole();
        }
        
        $setting = self::where('admin_id', $adminId)->where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        switch ($setting->type) {
            case 'boolean':
                return (bool) $setting->value;
            case 'integer':
                return (int) $setting->value;
            case 'json':
                return json_decode($setting->value, true);
            default:
                return $setting->value;
        }
    }

    /**
     * Set a setting value
     */
    public static function setValue($key, $value, $type = 'string', $group = 'general', $description = null, $adminId = null)
    {
        if ($adminId === null) {
            $adminId = getAdminIdByUserRole();
        }
        
        $setting = self::updateOrCreate(
            ['admin_id' => $adminId, 'key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : (string) $value,
                'type' => $type,
                'group' => $group,
                'description' => $description
            ]
        );

        return $setting;
    }

    /**
     * Check if a setting is enabled (for boolean settings)
     */
    public static function isEnabled($key, $default = false, $adminId = null)
    {
        return self::getValue($key, $default, $adminId);
    }
}
