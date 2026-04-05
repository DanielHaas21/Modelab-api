<?php

namespace App\Configuration\Validators\Base;

/**
 * Structure of each crated validator
 *
 * Use it in conjunction with BaseValidator
 */
interface ValidatorStructure
{
    /**
     * @var $isValid Should always be utilized to return bool, Can be found in BaseValidator
     * @return bool
     *
     */
    public function Validate(): bool;
    public function Build(): void;
    public function Run(): void;
}
