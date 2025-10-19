<?php

namespace Fomvasss\StaticLists;

use Fomvasss\StaticLists\Support\Arr;

/**
 * Побудова масивів-списків з фільтрами/сортуванням.
 *
 * @param iterable<array> $records
 * @param ?string $columnKey  Поле значення або '*' для цілого запису
 * @param ?string $indexKey   Поле-ключ результату
 * @param array{
 *   only?: array|string,
 *   except?: array|string,
 *   added?: array|string,      // ['*'] — додати всі доступні з $records (all)
 *   override?: bool,           // true — added заміщує існуючі по $indexKey
 *   sort?: string|array{field:string,flag?:int}|callable, // callable($a,$b):int
 *   order?: 'asc'|'desc',
 *   unique?: bool
 * } $options
 */
final class StaticListBuilder
{
    public static function build(
        iterable $records = [],
        ?string $columnKey = null,
        ?string $indexKey = null,
        array $options = [],
    ): array {
        // normalize to array-of-arrays
        $col = [];
        foreach ($records as $r) {
            $col[] = (array) $r;
        }
        $all = $col;

        // ONLY (збереження порядку only)
        if ($indexKey && ($only = $options['only'] ?? [])) {
            $only = Arr::wrap($only);
            $byIndex = [];
            foreach ($col as $r) {
                $k = $r[$indexKey] ?? null;
                if ($k !== null && !array_key_exists($k, $byIndex)) {
                    $byIndex[$k] = $r;
                }
            }
            $tmp = [];
            foreach ($only as $k) {
                if (array_key_exists($k, $byIndex)) {
                    $tmp[] = $byIndex[$k];
                }
            }
            $col = $tmp;
        }

        // EXCEPT
        if ($without = $options['except'] ?? []) {
            $without = Arr::wrap($without);
            $withoutFlipped = array_fill_keys($without, true);
            $tmp = [];
            foreach ($col as $r) {
                $val = $indexKey ? ($r[$indexKey] ?? null) : null;
                if (!$indexKey || !isset($withoutFlipped[$val])) {
                    $tmp[] = $r;
                }
            }
            $col = $tmp;
        }

        // ADDED
        if ($add = $options['added'] ?? []) {
            $add = Arr::wrap($add);
            $added = ($add === ['*'])
                ? $all
                : array_values(array_filter($all, function ($r) use ($indexKey, $add) {
                    return $indexKey && in_array($r[$indexKey] ?? null, $add, true);
                }));

            if ($indexKey) {
                $existing = [];
                foreach ($col as $r) {
                    $existing[$r[$indexKey] ?? null] = $r;
                }
                $incoming = [];
                foreach ($added as $r) {
                    $incoming[$r[$indexKey] ?? null] = $r;
                }
                $override = (bool)($options['override'] ?? false);
                $merged = $override
                    ? array_replace($existing, $incoming) // incoming заміщує
                    : array_replace($incoming, $existing); // existing має пріоритет
                $col = array_values(array_filter($merged, fn($v, $k) => $k !== null, ARRAY_FILTER_USE_BOTH));
            } else {
                $col = array_values(array_merge($col, $added));
            }
        }

        // UNIQUE by $indexKey
        if (($options['unique'] ?? false) && $indexKey) {
            $seen = [];
            $tmp = [];
            foreach ($col as $r) {
                $k = $r[$indexKey] ?? null;
                if ($k !== null && !isset($seen[$k])) {
                    $seen[$k] = true;
                    $tmp[] = $r;
                }
            }
            $col = $tmp;
        }

        // SORT
        if ($sort = $options['sort'] ?? null) {
            if (is_string($sort)) {
                $desc = strtolower($options['order'] ?? 'asc') === 'desc';
                usort($col, function ($a, $b) use ($sort, $desc) {
                    $va = $a[$sort] ?? null;
                    $vb = $b[$sort] ?? null;
                    $cmp = $va <=> $vb;
                    return $desc ? -$cmp : $cmp;
                });
            } elseif (is_array($sort) && isset($sort['field'])) {
                $field = $sort['field'];
                $desc  = strtolower($options['order'] ?? 'asc') === 'desc';
                $flag  = $sort['flag'] ?? SORT_REGULAR;
                usort($col, function ($a, $b) use ($field, $flag, $desc) {
                    $va = $a[$field] ?? null;
                    $vb = $b[$field] ?? null;
                    $cmp = self::cmp($va, $vb, $flag);
                    return $desc ? -$cmp : $cmp;
                });
            } elseif (is_callable($sort)) {
                usort($col, $sort);
            }
        }

        // OUTPUT FORMS
        if ($indexKey && $columnKey) {
            if ($columnKey === '*' || $columnKey === ['*']) {
                $out = [];
                foreach ($col as $r) {
                    $k = $r[$indexKey] ?? null;
                    if ($k !== null) $out[$k] = $r;
                }
                return $out;
            }
            $out = [];
            foreach ($col as $r) {
                $k = $r[$indexKey] ?? null;
                if ($k !== null) {
                    $out[$k] = $r[$columnKey] ?? null;
                }
            }
            return $out;
        }

        if ($indexKey && !$columnKey) {
            $out = [];
            foreach ($col as $r) {
                $k = $r[$indexKey] ?? null;
                if ($k !== null) $out[$k] = $r;
            }
            return $out;
        }

        if ($columnKey && !$indexKey) {
            return array_values(array_map(fn($r) => $r[$columnKey] ?? null, $col));
        }

        return array_values($col);
    }

    private static function cmp(mixed $a, mixed $b, int $flag = SORT_REGULAR): int
    {
        if ($flag === SORT_STRING) {
            return strcmp((string)$a, (string)$b);
        }
        if ($flag === SORT_NUMERIC) {
            return ((float)$a <=> (float)$b);
        }
        return $a <=> $b;
    }
}
