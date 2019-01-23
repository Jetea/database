<?php

namespace Jetea\Database;

use Jetea\Database\Query\Builder;
use Jetea\Database\Query\Grammars\PostgresGrammar;

/**
 * 框架Pgsql数据库辅助类
 */
class PostgresConnection extends Connection
{
    /**
     * 插入数据获取自增id (只支持单条数据,如果为多条会出现问题)
     *
     * @deprecated 不建议直接使用, 所有 insert 操作建议走 query
     *
     * @param $query
     * @param array $bindings
     * @param string $primaryKey
     *
     * @return string
     */
    public function insertGetId($query, $bindings = [], $primaryKey = 'id')
    {
        return $this->select($query . " returning " . $primaryKey, $bindings)[0][$primaryKey];
    }

    /**
     * @param $table
     *
     * @return Builder
     */
    public function table($table)
    {
        return (new Builder($this, new PostgresGrammar()))->table($table);
    }
}
