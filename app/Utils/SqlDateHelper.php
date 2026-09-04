<?php

namespace App\Utils;

use Illuminate\Support\Facades\DB;

class SqlDateHelper
{
    /**
     * Build a driver-agnostic "year" expression for a date column.
     * SQLite has no YEAR()/MONTH() functions.
     */
    public static function year(string $column): string
    {
        return self::expression('YEAR', '%Y', $column);
    }

    /**
     * Build a driver-agnostic "month" expression for a date column.
     */
    public static function month(string $column): string
    {
        return self::expression('MONTH', '%m', $column);
    }

    private static function expression(string $mysqlFunction, string $format, string $column): string
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            return "CAST(strftime('{$format}', {$column}) AS INTEGER)";
        }

        return "{$mysqlFunction}({$column})";
    }
}
