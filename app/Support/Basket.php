<?php

namespace App\Support;

use App\Models\Course;
use Illuminate\Support\Collection;

/**
 * Session-backed list of course ids, used for both the shopping cart and the
 * wishlist so that guests can collect courses before logging in.
 */
class Basket
{
    public const CART = 'cart';

    public const WISHLIST = 'wishlist';

    /** @return array<int, int> */
    public static function ids(string $key): array
    {
        return array_values(array_unique(array_map('intval', (array) session($key, []))));
    }

    public static function has(string $key, int $courseId): bool
    {
        return in_array($courseId, self::ids($key), true);
    }

    public static function add(string $key, int $courseId): void
    {
        $ids = self::ids($key);

        if (! in_array($courseId, $ids, true)) {
            $ids[] = $courseId;
        }

        session([$key => $ids]);
    }

    public static function remove(string $key, int $courseId): void
    {
        session([$key => array_values(array_diff(self::ids($key), [$courseId]))]);
    }

    public static function clear(string $key): void
    {
        session()->forget($key);
    }

    public static function count(string $key): int
    {
        return count(self::ids($key));
    }

    /**
     * Published courses currently in the basket (stale ids are dropped).
     *
     * @return Collection<int, Course>
     */
    public static function courses(string $key): Collection
    {
        $ids = self::ids($key);

        if ($ids === []) {
            return collect();
        }

        $courses = Course::query()
            ->with(['instructor', 'category'])
            ->withCount(['chapters', 'contents'])
            ->where('status', 'published')
            ->whereIn('id', $ids)
            ->get();

        if ($courses->count() !== count($ids)) {
            session([$key => $courses->pluck('id')->all()]);
        }

        return $courses;
    }
}
