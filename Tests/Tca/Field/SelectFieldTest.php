<?php

namespace Typo3Api\Tca\Field;


class SelectFieldTest extends AbstractFieldTest
{
    const STUB_DB_TYPE = "VARCHAR(1) DEFAULT '' NOT NULL";

    protected function createFieldInstance(string $name, array $options = []): AbstractField
    {
        if (!isset($options['values']) && !isset($options['items'])) {
            $options['values'] = [];
        }

        return new SelectField($name, $options);
    }

    /**
     * @param AbstractField $field
     */
    protected function assertBasicColumns(AbstractField $field)
    {
        $this->assertEquals([
            $field->getName() => [
                'label' => $field->getOption('label'),
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => [
                        ['', '']
                    ]
                ],
                'l10n_mode' => 'exclude',
                'l10n_display' => 'defaultAsReadonly',
            ]
        ], $field->getColumns('stub_table'));
    }

    public function testItems()
    {
        $items = [
            ['label', 'value'],
            ['divider', '--div--'],
            ['label2', 'value2'],
        ];
        $field = $this->createFieldInstance('some_field', [
            'items' => $items
        ]);

        $this->assertEquals(['value', 'value2'], $field->getOption('values'));
    }

    public function testValues()
    {
        $field = $this->createFieldInstance('some_field', [
            'values' => ['value', 'value2']
        ]);

        $this->assertEquals([
            ['Value', 'value'],
            ['Value2', 'value2'],
        ], $field->getOption('items'));

        $this->assertEquals([
            ['Value', 'value'],
            ['Value2', 'value2'],
        ], $field->getColumns('stub_table')['some_field']['config']['items']);
    }

    public function testRequired()
    {
        $field = $this->createFieldInstance('some_field', [
            'values' => ['value', 'value2'],
            'required' => false
        ]);

        $this->assertEquals(['', 'value', 'value2'], $field->getOption('values'));

        $this->assertEquals([
            ['', ''],
            ['Value', 'value'],
            ['Value2', 'value2'],
        ], $field->getOption('items'));

        $this->assertEquals([
            ['', ''],
            ['Value', 'value'],
            ['Value2', 'value2'],
        ], $field->getColumns('stub_table')['some_field']['config']['items']);
    }
}