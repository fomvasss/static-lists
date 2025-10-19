<?php

use Fomvasss\StaticLists\StaticListBuilder;
use PHPUnit\Framework\TestCase;

class StaticListBuilderTest extends TestCase
{
    public function test_only_and_map()
    {
        $records = [
            ['value' => 'new', 'label' => 'Новий', 'priority' => 30],
            ['value' => 'paid', 'label' => 'Оплачено', 'priority' => 10],
            ['value' => 'done', 'label' => 'Завершено', 'priority' => 40],
        ];

        $out = StaticListBuilder::build(
            $records,
            columnKey: 'label',
            indexKey: 'value',
            options: ['only' => ['paid', 'new']]
        );

        $this->assertSame(['paid' => 'Оплачено', 'new' => 'Новий'], $out);
    }
}