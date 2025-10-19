# fomvasss/static-lists

[![License](https://img.shields.io/packagist/l/fomvasss/static-lists.svg?style=for-the-badge)](https://packagist.org/packages/fomvasss/static-lists)
[![Build Status](https://img.shields.io/github/stars/fomvasss/static-lists.svg?style=for-the-badge)](https://github.com/fomvasss/static-lists)
[![Latest Stable Version](https://img.shields.io/packagist/v/fomvasss/static-lists.svg?style=for-the-badge)](https://packagist.org/packages/fomvasss/static-lists)
[![Total Downloads](https://img.shields.io/packagist/dt/fomvasss/static-lists.svg?style=for-the-badge)](https://packagist.org/packages/fomvasss/static-lists)

Deterministic list builder for arrays/iterables.

```php
use Fomvasss\StaticLists\StaticListBuilder;

$records = [
    ['value' => 'new', 'label' => 'New', 'priority' => 30],
    ['value' => 'paid', 'label' => 'Paid', 'priority' => 10],
    ['value' => 'done', 'label' => 'Done', 'priority' => 40],
];

$map = StaticListBuilder::build($records, 'label', 'value', [
  'only' => ['paid','new'],
  'sort' => 'priority',
  'order'=> 'asc',
]);