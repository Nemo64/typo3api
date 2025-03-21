<?php

declare(strict_types=1);

namespace Typo3Api\Tca\Field;

use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Typo3Api\Builder\Context\TcaBuilderContext;

class SelectField extends AbstractField
{
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired('items');
        $resolver->setAllowedTypes('items', 'array');

        $resolver->setDefaults([
            // values is just a list of possible values
            // you can use it instead of items if you don't want/need to define labels for your options
            'values' => [],
            // items is the normal typo3 compatible item list
            // if not defined, it will be generated from the value list
            'items' => fn(Options $options) => array_map(static function ($value) {
                $label = preg_replace(['/([A-Z])/', '/[_\s]+/'], ['_$1', ' '], (string) $value);
                $label = ucfirst(strtolower(trim($label)));
                return [$label, $value];
            }, $options['values']),
            'itemsProcFunc' => null,

            'dbType' => function (Options $options) {
                $possibleValues = self::getValuesFromItems($options['items']);
                $defaultValue = addslashes((string) reset($possibleValues));

                $minimumChars = $options['itemsProcFunc'] ? 30 : 1;
                $maxChars = max($minimumChars, ...array_map('mb_strlen', $possibleValues));

                if ($maxChars > 191) {
                    // Why 191 characters?
                    // Because mysql indexes can only store 767 bytes and I want to enforce a usefull limit.
                    // https://mathiasbynens.be/notes/mysql-utf8mb4#column-index-length
                    // Why are you reading this anyways? Did you really try to select a value that has more than 30 chars?
                    $msg = "The value in an select shouldn't be longer than 191 characters.";
                    $msg .= " The longest value has $maxChars characters.";
                    $msg .= " If you absolutely need to save longer values, define the dbType manually.";
                    throw new InvalidOptionsException($msg);
                }

                return "VARCHAR($maxChars) DEFAULT '$defaultValue' NOT NULL";
            },

            'default' => '',

            // it doesn't make sense to localize selects (most of the time)
            'localize' => false
        ]);

        $resolver->setAllowedTypes('values', 'array');
        $resolver->setAllowedTypes('items', 'array');
        $resolver->setAllowedTypes('itemsProcFunc', ['null', 'string']);
        $resolver->setAllowedTypes('required', 'bool');
        $resolver->setAllowedTypes('default', 'string');

        $resolver->setNormalizer('items', function (Options $options, $items) {
            // ensure at least one value, or an empty value if not required
            if (empty($items) || ($options['required'] === false && ($items[0][1] ?? null !== '' || $items[0]['value'] ?? null !== ''))) {
                array_unshift($items, ['', '']);
            }

            foreach ($items as $value) {
                $dbValue = $value[1] ?? $value['value'];
                // the documentation says these chars are invalid
                // https://docs.typo3.org/typo3cms/TCAReference/ColumnsConfig/Type/Select.html#items
                if (preg_match('/[|,;]/', (string) $dbValue)) {
                    throw new InvalidOptionsException("The value in an select must not contain the chars '|,;'.");
                }
            }

            // Migrate from ['<label>', '<value>'] syntax to new ['label' => <label>, 'value' => <value>]
            $items = array_map(function ($item) {
                if (isset($item['label']) && isset($item['value'])) {
                    return $item;
                }

                $label = $item[0];
                $value = $item[1];
                $icon = $item[2] ?? '';

                $returnValue = [
                    'label' => $label,
                    'value' => $value,
                ];

                if ($icon) {
                    $returnValue['icon'] = $icon;
                }

                return $returnValue;
            }, $items);


            return $items;
        });
    }

    private static function getValuesFromItems(array $items): array
    {
        $values = [];

        foreach ($items as $item) {
            if (!isset($item['value'])) {
                continue;
            }

            $values[] = $item['value'];
        }

        if (empty($values)) {
            $values[] = '';
        }

        return $values;
    }

    public function getFieldTcaConfig(TcaBuilderContext $tcaBuilder): array
    {
        $config = [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => $this->getOption('items'),
            'default' => $this->getOption('default'),
        ];

        if ($this->getOption('itemsProcFunc') !== null) {
            $config['itemsProcFunc'] = $this->getOption('itemsProcFunc');
        }

        return $config;
    }
}
