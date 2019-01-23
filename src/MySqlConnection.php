<?php

namespace Jetea\Database;

use PDO;
use Jetea\Database\Query\Builder;
use Jetea\Database\Query\Grammars\MySqlGrammar;

/**
 * 框架MySql数据库辅助类
 * 每个实例代表一次数据库连接
 */
class MySqlConnection extends Connection
{
    /**
     * Bind values to their parameters in the given statement.
     *
     * @param  \PDOStatement $statement
     * @param  array  $bindings
     * @return void
     */
    public function bindValues($statement, $bindings)
    {
        foreach ($bindings as $key => $value) {
            $statement->bindValue(
                is_string($key) ? $key : $key + 1, $value,
                is_int($value) || is_float($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
    }

    /**
     * @param $table
     *
     * @return Builder
     */
    public function table($table)
    {
        return (new Builder($this, new MySqlGrammar()))->table($table);
    }
}
