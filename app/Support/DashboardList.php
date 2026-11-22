<?php

namespace App\Support;

use DateTimeImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DashboardList
{
    public const PER_PAGE = 15;

    public const COURT_STATUSES = ['pending', 'approved', 'rejected', 'inactive'];

    public static function value(Request $request, string $key): string
    {
        $value = $request->query($key);

        return is_string($value) ? mb_substr(trim($value), 0, 120) : '';
    }

    public static function search(Request $request): ?string
    {
        $search = self::value($request, 'search');

        return $search === '' ? null : '%'.$search.'%';
    }

    public static function choice(Request $request, string $key, array $allowed): ?string
    {
        $value = self::value($request, $key);

        return in_array($value, $allowed, true) ? $value : null;
    }

    public static function id(Request $request, string $key): ?int
    {
        $id = filter_var(self::value($request, $key), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id === false ? null : $id;
    }

    public static function dates(Builder $query, Request $request, string $column): Builder
    {
        foreach (['from' => '>=', 'to' => '<='] as $key => $operator) {
            $value = self::value($request, $key);
            if (! preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value)) {
                continue;
            }
            $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
            if ($date && $date->format('Y-m-d') === $value) {
                $query->whereDate($column, $operator, $value);
            }
        }

        return $query;
    }

    public static function labels(array $values): array
    {
        return array_combine($values, array_map(fn (string $value) => Str::headline($value), $values));
    }

    public static function hasFilters(Request $request): bool
    {
        return $request->anyFilled([
            'search', 'status', 'verification', 'usage', 'sport_id', 'publication', 'state',
            'decision', 'action', 'rating', 'type', 'court_id', 'schedule', 'from', 'to',
        ]);
    }
}
