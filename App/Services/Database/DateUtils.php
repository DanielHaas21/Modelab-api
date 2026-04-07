<?php

namespace App\Services\Database;

use DateTime;

class DateUtils
{
    private const DB_FORMAT = 'Y-m-d H:i:s';

    public static function ToDatabase(DateTime $dateTime): string
    {
        return $dateTime->format(self::DB_FORMAT);
    }

    public static function FromDatabase(string $dateString): ?DateTime
    {
        $date = DateTime::createFromFormat(self::DB_FORMAT, $dateString);

        if ($date !== false) {
            return $date;
        }

        try {
            return new DateTime($dateString);
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function Now(): string
    {
        return (new DateTime())->format(self::DB_FORMAT);
    }
}
