<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
    ];

    /**
     * Get a setting value by key with optional default
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        // Handle JSON type
        if ($setting->type === 'json') {
            return json_decode($setting->value, true) ?? $default;
        }

        return $setting->value ?? $default;
    }

    /**
     * Set a setting value
     * 
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @param string $group
     * @return bool
     */
    public static function set(string $key, $value, string $type = 'string', string $group = 'general')
    {
        // Handle JSON encoding
        if ($type === 'json') {
            $value = json_encode($value);
        }

        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'group' => $group,
            ]
        );
    }

    /**
     * Delete a setting by key
     * 
     * @param string $key
     * @return bool
     */
    public static function remove(string $key)
    {
        return static::where('key', $key)->delete();
    }
}
