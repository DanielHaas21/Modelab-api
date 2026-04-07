<?php

namespace App\Services\Settings;

use App\Middleware\Clearance;
use App\Models\Config\Setting;
use App\Services\Database\DateUtils;
use Exception;

class SettingsService
{
    /**
     * The email domain whitelist, empty means anything
     */
    public const ALLOWED_EMAIL_DOMAINS = 'allowed_email_domains';

    /**
    * Settings schema
    * - type: string, integer, double, array (array of strings)
    * - default: the default value
    * - read_clearance: min read clearance
    * - write_clearance: min write clearance
    * @var array
    */
    public const SCHEMA = [
        self::ALLOWED_EMAIL_DOMAINS => ['type' => 'array',  'default' => [], 'read_clearance' => Clearance::ADMIN, 'write_clearance' => Clearance::ADMIN],
    ];

    private const ARRAY_SEPARATOR = '$SEP$';

    public function __construct()
    {
        $this->FixSettings();
    }

    private function FixSettings(): void
    {
        /**
         * @var Setting[]
         */
        $all_settings = Setting::SelectAllModels();
        $used_keys = [];

        foreach ($all_settings as $setting) {
            if (!isset(self::SCHEMA[$setting->key])) {
                continue;
            }
            $used_keys[] = $setting->key;
        }

        foreach (self::SCHEMA as $setting_key => $schema) {
            if (in_array($setting_key, $used_keys)) {
                continue;
            }

            $setting = new Setting();
            $setting->key = $setting_key;
            $setting->value = $this->ToSavedValue($schema['default'], $schema['type']);
            $setting->read_clearance = $schema['read_clearance'];
            $setting->write_clearance = $schema['write_clearance'];
            $setting->updated = DateUtils::Now();

            Setting::InsertModel($setting);
        }
    }

    /**
     * @return mixed
     */
    private function FromSavedValue(string $value, string $type)
    {
        switch ($type) {
            case 'string':
                return $value;
            case 'integer':
                return intval($value);
            case 'double':
                return doubleval($value);
            case 'array':
                return explode(self::ARRAY_SEPARATOR, $value);
            default:
                throw new Exception('Tried parsing from value of unknown type: \'' . $type . '\'');
        }
    }

    /**
     * @param mixed $value
     */
    private function ToSavedValue($value, string $type): string
    {
        switch ($type) {
            case 'string':
            case 'integer':
            case 'double':
                return strval($value);
            case 'array':
                return implode(self::ARRAY_SEPARATOR, $value);
            default:
                throw new Exception('Tried parsing to value of unknown type: \'' . $type . '\'');
        }
    }

    private function SelectSetting(string $key): Setting
    {
        if (!isset(self::SCHEMA[$key])) {
            throw new Exception('Unknown setting key used: \'' . $key . '\'');
        }

        $settings = Setting::SelectWhereModels('key = :key', [
            ':key' => $key
        ]);

        if (count($settings) == 0) {
            throw new Exception('Setting \'' . $key . '\' is missing.');
        }

        return $settings[0];
    }

    /**
     * @param mixed $value
     */
    public function SetSetting(string $key, $value, int $clearance = -1): void
    {
        $setting = $this->SelectSetting($key);
        $expectedType = self::SCHEMA[$key]['type'];

        if ($setting->write_clearance > $clearance) {
            throw new Exception('Insufficient clearance to write setting: ' . $key);
        }

        $setting->value = $this->ToSavedValue($value, $expectedType);
        $setting->updated = DateUtils::Now();

        Setting::UpdateModel($setting);
    }

    public function GetSetting(string $key, ?int $clearance = null): array
    {
        $setting = $this->SelectSetting($key);

        if ($clearance != null && $setting->read_clearance > $clearance) {
            throw new Exception('Insufficient clearance to read setting: ' . $key);
        }

        return [
            'value' => $this->FromSavedValue($setting->value, self::SCHEMA[$setting->key]['type']),
            'read_clearance' => $setting->read_clearance,
            'write_clearance' => $setting->write_clearance,
        ];
    }
}
