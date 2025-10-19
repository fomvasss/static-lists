<?php

namespace Fomvasss\StaticLists\Support;

final class Arr
{
    public static function wrap(mixed $value): array
    {
        if ($value === null) return [];
        return is_array($value) ? $value : [$value];
    }
}