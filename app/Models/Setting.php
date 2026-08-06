<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    protected static $cache = [];
    protected static array $encryptedKeys = ['odoo_password', 'print_hub_api_key'];

    public static function decryptSecret(?string $value): ?string
    {
        if (empty($value)) {
            return $value;
        }
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            // Fallback for existing plaintext secrets in DB
            return $value;
        }
    }

    public static function encryptSecret(?string $value): ?string
    {
        if (empty($value)) {
            return $value;
        }
        try {
            return Crypt::encryptString($value);
        } catch (\Throwable $e) {
            return $value;
        }
    }

    public static function get(string $key, $default = null): ?string
    {
        if (isset(static::$cache[$key])) {
            return static::$cache[$key];
        }

        $setting = static::where('key', $key)->first();
        $value = $setting ? $setting->value : $default;

        if (in_array($key, static::$encryptedKeys) && !empty($value)) {
            $value = static::decryptSecret($value);
        }

        static::$cache[$key] = $value;
        return $value;
    }

    public static function set(string $key, $value): void
    {
        $rawValue = $value;
        if (in_array($key, static::$encryptedKeys) && !empty($value)) {
            $rawValue = static::encryptSecret($value);
        }

        static::updateOrCreate(['key' => $key], ['value' => $rawValue]);
        static::$cache[$key] = $value; // Cache the decrypted value for performance
    }

    public static function getOdooConfig(): array
    {
        return [
            'url' => static::get('odoo_url', ''),
            'db' => static::get('odoo_db', ''),
            'user' => static::get('odoo_user', ''),
            'password' => static::get('odoo_password', ''),
        ];
    }

    public static function getValue(string $key, $default = null): ?string
    {
        return static::get($key, $default);
    }

    public static function setValue(string $key, $value): void
    {
        static::set($key, $value);
    }
}
