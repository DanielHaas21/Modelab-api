<?php

namespace App\Services\Router;

class DataValidator
{
    public const REQUIRED = 'required';
    public const NUMERIC = 'numeric';

    /**
     * @param string|array $qualities The qualities that are validated, use consts of this class
     * @param array $data
     * @param ?array $fields Null for all keys in data
     * @throws RequestError
     * @return void
     */
    public static function ValidateFieldsAre($qualities, array $data, ?array $fields = null): void
    {
        if (!is_array($qualities)) {
            $qualities = [$qualities];
        }

        if ($fields == null) {
            $fields = array_keys($data);
        }

        foreach ($fields as $field) {
            if (in_array(self::REQUIRED, $qualities)) {
                self::ValidateRequired($data, $field);
            }
            if (in_array(self::NUMERIC, $qualities)) {
                self::ValidateNumeric($data, $field);
            }
        }
    }

    /**
     * @param array $data
     * @param string $field
     * @throws RequestError
     * @return void
     */
    private static function ValidateRequired(array $data, string $field): void
    {
        if (isset($data[$field])) {
            return;
        }
        throw RequestError::CreateFieldError(400, $field, '%key% is required');
    }

    /**
     * @param array $data
     * @param string $field
     * @throws RequestError
     * @return void
     */
    private static function ValidateNumeric(array $data, string $field): void
    {
        if (is_numeric($data[$field])) {
            return;
        }
        throw RequestError::CreateFieldError(400, $field, '%key% is not numeric');
    }
}
