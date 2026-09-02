<?php

namespace App\Support;

use Illuminate\Http\Request;

class AdminPagination
{
    public const PER_PAGE_OPTIONS = [10, 25, 50, 100];

    public static function perPage(Request $request): int
    {
        $perPage = (int) $request->query('per_page', 10);

        return in_array($perPage, self::PER_PAGE_OPTIONS, true) ? $perPage : 10;
    }
}
