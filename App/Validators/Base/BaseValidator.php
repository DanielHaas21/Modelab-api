<?php

namespace App\Validators\Base;

/**
 * Base Class for validators, it doesnt include public methods, since its only meant to be extended
 * - All paths are relative to the root directory
 */
class BaseValidator
{
    /**
     * Provided constant
     * @var array
     */
    protected $const;
    protected static $isValid = true;

    protected function __construct($constant)
    {
        $this->const = $constant;
    }

    /**
     * Validates the structure againts the provided constant
     * @param mixed $expectedStructure Expected array structure, Instead of values place empty placeholders ''
     * @return bool returns false if the arrays dont match
     */
    protected function ValidateStructure(array $expectedStructure, array $actualStructure = null): bool
    {
        $actualStructure = $actualStructure ?? $this->const;
    
        foreach ($expectedStructure as $key => $expectedValue) {
            if (!array_key_exists($key, $actualStructure)) {
                echo "Key: $key doesn't match the structure\n";
                return false;
            }
    
            if (is_array($expectedValue) && is_array($actualStructure[$key])) {
                if (!$this->ValidateStructure($expectedValue, $actualStructure[$key])) {
                    return false;
                }
            }
        }
        return true;
    }
    /**
     * Checks a value of an array with a custom callable logic
     * @param string $keyPath Path of the key-value pair, use dot notation
     * @param (callable(mixed $val): void)[] $validator - Validation logic
     * @return bool
     */
    protected function CheckProperty(string $keyPath, callable $validator): bool
    {
        $keys = explode('.', $keyPath);
        $value = $this->const;

        // Traverse the nested structure
        foreach ($keys as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return false;
            }
            $value = $value[$key];
        }

        // Apply the validator
        return $validator($value);
    }

    /**
     * Checks if file exists in a given path, returns false if not
     * @param mixed $p fileapth
     * @return bool
     */
    protected function FileExists($p): bool
    {
        if (!file_exists($p)) {
            return false;
        } else {
            return true;
        }
    }
    /**
     * Generates a php file in a given path
     * @param mixed $filePath
     * @param mixed $fileContent
     * @return void
     */
    protected function GenerateFile($filePath, $fileName): void
    {
        $file = file_get_contents($filePath);
 
        file_put_contents($fileName, $file);
    }
}