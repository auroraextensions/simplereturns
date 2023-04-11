<?php
/**
 * LabelManager.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Model\Display
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\Display;

use AuroraExtensions\ModuleComponents\Model\Utils\ArrayUtils;
use Iterator;
use IteratorAggregate;
use Magento\Framework\Phrase;
use Traversable;

use function __;
use function array_filter;
use function array_keys;
use function array_map;
use function array_values;
use function is_array;
use function iterator_to_array;

use const ARRAY_FILTER_USE_KEY;

class LabelManager
{
    /** @var ArrayUtils $arrayUtils */
    private $arrayUtils;

    /** @var mixed[] $data */
    private $data;

    /** @var mixed[] $meta */
    private $meta;

    /**
     * @param ArrayUtils $arrayUtils
     * @param mixed[] $meta
     * @param mixed[] $data
     * @return void
     */
    public function __construct(
        ArrayUtils $arrayUtils,
        array $meta = [],
        array $data = []
    ) {
        $this->arrayUtils = $arrayUtils;
        $this->meta = $meta;
        $this->data = $data;
        $this->initialize();
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    private function initialize(): void
    {
        /** @var array $result */
        $result = [];

        /** @var string $field */
        /** @var mixed $value */
        foreach ($this->data as $field => &$value) {
            $result[$field] = [];

            if ($value instanceof Traversable) {
                /** @var Iterator $iterator */
                $iterator = $value instanceof IteratorAggregate
                    ? $value->getIterator() : $value;
                $value = iterator_to_array($iterator);
            }

            /** @var array $values */
            $values = is_array($value) ? $value : [];

            /** @var string|Phrase $key */
            /** @var mixed $val */
            foreach ($values as [
                'label' => $key,
                'value' => $val,
            ]) {
                $key = (string) $key;

                if (!is_array($val)) {
                    $val = (string) $val;
                    $result[$field][$val] = $key;
                    continue;
                }

                /** @var array $squash */
                $squash = array_values(
                    $this->arrayUtils->squash($val, $key)
                );
                $result[$field] += $this->arrayUtils->umerge(
                    null,
                    ...array_map(
                        function ($k, $v) {
                            return [$k => $v];
                        },
                        array_filter(
                            $squash,
                            function ($k) {
                                return (bool)($k & 1);
                            },
                            ARRAY_FILTER_USE_KEY
                        ),
                        array_filter(
                            $squash,
                            function ($k) {
                                return !($k % 2);
                            },
                            ARRAY_FILTER_USE_KEY
                        )
                    )
                );
            }
        }

        $this->data = $result;
    }

    /**
     * @return array
     */
    private function getColumns(): array
    {
        /** @var string[] $keys */
        $keys = array_keys($this->data);

        /** @var int $index */
        /** @var int|string $value */
        foreach ($keys as $index => $value) {
            /** @var string $field */
            $field = $this->meta['alias_to_field'][$value] ?? $value;
            $keys[$index] = $this->meta['field_to_column'][$field] ?? $field;
        }

        return $keys;
    }

    /**
     * @param string $group
     * @param string $value
     * @return string|null
     */
    public function getLabel(string $group, string $value): ?string
    {
        /** @var string $field */
        $field = $this->meta['alias_to_field'][$group] ?? $group;

        /** @var mixed $label */
        $label = $this->data[$field][$value] ?? null;
        return $label !== null
            ? (string) __($label) : null;
    }

    /**
     * @param array $item
     * @return array
     */
    public function replace(array $item): array
    {
        /** @var array $values */
        $values = $this->arrayUtils->kslice(
            $item,
            $this->getColumns()
        );

        /** @var string $column */
        /** @var mixed $value */
        foreach ($values as $column => $value) {
            /** @var string $field */
            $field = $this->meta['column_to_field'][$column] ?? $column;

            /** @var string|Phrase|null $label */
            $label = $this->data[$field][$value] ?? $value;
            $item[$column] = $label ?? '-';
        }

        return $item;
    }
}
