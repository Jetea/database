<?php

namespace Jetea\Database\Query\Grammars;

class MySqlGrammar extends Grammar
{
    protected function wrap($value)
    {
        if ($value !== '*') {
            return "`$value`";
        }

        return $value;
    }
}
