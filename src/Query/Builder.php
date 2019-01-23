<?php

namespace Jetea\Database\Query;

use Jetea\Database\Connection;
use Jetea\Database\Query\Grammars\Grammar;

class Builder
{
    /**
     * @var Connection
     */
    protected $conn;

    /**
     * @var Grammar
     */
    protected $grammar;

    /**
     * @var string
     */
    public $table;

    public function __construct(Connection $conn, Grammar $grammar)
    {
        $this->conn = $conn;
        $this->grammar = $grammar;
    }

    /**
     * @param $table
     * @return $this
     */
    public function table($table)
    {
        $this->table = $table;

        return $this;
    }

//    public function get()
//    {
//        return $this->conn->select($this->toSql(), $this->getBindings());
//    }

    public function insert(array $values)
    {
        if (! is_array(reset($values))) {
            $values = [$values];
        }

        list($query, $bindings) = $this->grammar->compileInsert($this, $values);

        return $this->conn->insert($query, $bindings);
    }

    public function insertGetId(array $values, $primaryKey = 'id')
    {
        list($query, $bindings) = $this->grammar->compileInsertGetId($this, $values);

        $id = $this->conn->insertGetId($query, $bindings, $primaryKey);

        return is_numeric($id) ? (int) $id : $id;
    }
}
