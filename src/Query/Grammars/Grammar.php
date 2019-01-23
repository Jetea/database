<?php

namespace Jetea\Database\Query\Grammars;

use Jetea\Database\Query\Builder;

class Grammar
{
    public function compileInsert(Builder $query, $values)
    {
        $columnArr = array_keys(reset($values));

        $bindings = [];
        foreach ($values as $row) {
            $bindings = array_merge($bindings, array_values($row));
        }

        $placeholders = $this->getInsertPlaceholders(count($columnArr), count($values));

        return [
            sprintf(
                'insert into %s (%s) values %s',
                $this->wrap($query->table), //table
                implode(', ', $this->wrapArray($columnArr)), //columns
                $placeholders //value placeholders
            ),

            $bindings,
        ];
    }

    public function compileInsertGetId(Builder $query, $values)
    {
        $columnArr = array_keys($values);

        $bindings = array_values($values);

        $placeholders = $this->getInsertPlaceholders(count($columnArr), 1);

        return [
            sprintf(
                'insert into %s (%s) values %s',
                $this->wrap($query->table), //table
                implode(', ', $this->wrapArray($columnArr)), //columns
                $placeholders //value placeholders
            ),

            $bindings,
        ];
    }

    protected function getInsertPlaceholders($columnCount, $rowCount)
    {
        $rowPlaceholderArr = array_fill(0, $columnCount, '?');
        $rowPlaceholder = '( ' . implode(', ', $rowPlaceholderArr) . ' )';

        $placeholderArr = array_fill(0, $rowCount, $rowPlaceholder);

        return implode(', ', $placeholderArr);
    }

    /**
     * Wrap an array of values.
     *
     * @param  array  $values
     * @return array
     */
    protected function wrapArray(array $values)
    {
        return array_map([$this, 'wrapSegments'], $values);
    }

    protected function wrap($value)
    {
        if ($value !== '*') {
            return "\"$value\"";
        }

        return $value;
    }

    protected function wrapSegments($value)
    {
        $segments = explode('.', $value);
        $ret = [];
        foreach ($segments as $segment) {
            $ret[] = $this->wrap($segment);
        }

        return implode('.', $ret);
    }
}
