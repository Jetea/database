<?php

namespace Jetea\Database;

use PDO;
use Exception;
use Throwable;
use Closure;
use Jetea\Database\Query\Builder;
use Jetea\Database\Query\Grammars\Grammar;

/**
 * 框架MySql数据库辅助类
 * 每个实例代表一次数据库连接
 */
class Connection
{
    /**
     * pdo实例
     * exec() | query() | quote() | fetchAll() | fetchColumn() | lastInsertId()
     * beginTransaction() | rollBack() | commit() |
     * errorInfo() | getAttribute(constant('PDO::ATTR_' . $value()
     *
     * @var PDO
     */
    protected $pdo;

    /**
     * 超时时间
     */
    protected $timeout = 3;

    /**
     * 是否空转
     *
     * @var bool
     */
    protected $pretending = false;

    /**
     * All of the queries run against the connection.
     *
     * @var array
     */
    protected $queryLog = [];

    /**
     * Db constructor.
     * @param string $dsn dsn
     *        - mysql
     *          mysql:host=%s;port=%s;dbname=%s;charset={$charset}
     *        - pgsql
     *          pgsql:host=%s;port=%s;dbname=%s
     *        - sqlite3
     *          sqlite:/db_dir/mydb.db  //文件
     *          sqlite::memory: //内存中
     * @param string $user
     * @param string $password
     * @param array $options
     */
    public function __construct($dsn, $user = null, $password = null, array $options = array())
    {
        $options = $this->getDefaultOptions() + $options + array(PDO::ATTR_TIMEOUT => $this->timeout);
        $this->pdo = new PDO($dsn, $user, $password, $options);
    }

    /**
     * @return array
     */
    protected function getDefaultOptions()
    {
        return array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,    //防止本地进行参数转义，不能完全防止sql注入
            // PDO::ATTR_PERSISTENT => TRUE,   //是否使用长连接
            //PDO::ATTR_STRINGIFY_FETCHES => false,   //提取的时候将数值转换为字符串
            // PDO::ATTR_CASE => PDO::CASE_NATURAL,    //强制列名为指定的大小写
            //在DSN中指定charset的作用只是告诉PDO, 本地驱动转义时使用指定的字符集（并不是设定mysql server通信字符集）
            //设置mysql server通信字符集，还得使用set names <charset>指令。
            //PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset}",   //编码
        );
    }

    /**
     * Execute the given callback in "dry run"(空转) mode.
     *
     * @param  \Closure  $callback
     * @return array
     */
    public function pretend(Closure $callback)
    {
        $this->pretending = true;

        // Basically to make the database connection "pretend", we will just return
        // the default values for all the query methods, then we will return an
        // array of queries that were "executed" within the Closure callback.
        $callback($this);

        $this->pretending = false;

        return $this->queryLog;
    }

    public function getQueryLog()
    {
        return $this->queryLog;
    }

    /**
     * Execute a Closure within a transaction.
     * @todo 考虑是否需要在死锁的时候重试，参考laravel
     * 最佳实践: 在事务回调方法中严禁调用其他模块只建议数据库操作
     *
     * 因为方法封装里边不包含了 savepoint，防止在回调函数中对不同模块进行调用
     * 原因在于，可能其他模块对当前连接和另外的连接 开启了事务
     * 从而导致另外的连接没回滚导致数据出错
     *
     * @param  \Closure  $callback
     * @return mixed
     *
     * @throws \Exception|\Throwable
     */
    public function transaction(Closure $callback)
    {
        $this->pdo->beginTransaction();

        // We'll simply execute the given callback within a try / catch block
        // and if we catch any exception we can rollback the transaction
        // so that none of the changes are persisted to the database.
        try {
            $result = $callback($this);

            $this->pdo->commit();
        }

        // If we catch an exception, we will roll back so nothing gets messed
        // up in the database. Then we'll re-throw the exception so it can
        // be handled how the developer sees fit for their applications.
        catch (Exception $e) {
            $this->pdo->rollBack();

            throw $e;
        } catch (Throwable $e) {
            $this->pdo->rollBack();

            throw $e;
        }

        return $result;
    }

    protected function run($query, $bindings, Closure $callback)
    {
        $start = microtime(true);
        $result = $callback($query, $bindings);
        $time = round((microtime(true) - $start) * 1000, 2);
        $this->queryLog[] = compact('query', 'bindings', 'time');

        return $result;
    }

    public function select($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending) {
                return [];
            }

            $stmt = $this->pdo->prepare($query);
            $this->bindValues($stmt, $bindings);

            $stmt->execute();
            return $stmt->fetchAll();
        });
    }

    /**
     * Run an SQL statement and get the number of rows affected.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return int
     */
    protected function affectingStatement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending) {
                return 0;
            }

            $stmt = $this->pdo->prepare($query);
            $this->bindValues($stmt, $bindings);
            $stmt->execute();
            return $stmt->rowCount();  //受影响的行数:1
        });
    }

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
        $this->insert($query, $bindings);

        return $this->pdo->lastInsertId($primaryKey);
    }

    /**
     * @deprecated 不建议直接使用, 所有 insert 操作建议走 query
     *
     * @param $query
     * @param array $bindings
     *
     * @return int
     */
    public function insert($query, $bindings = [])
    {
        return $this->affectingStatement($query, $bindings);
    }

    public function update($query, $bindings)
    {
        return $this->affectingStatement($query, $bindings);
    }

    public function delete($query, $bindings)
    {
        return $this->affectingStatement($query, $bindings);
    }

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
                //PDO::PARAM_BOOL PDO::PARAM_NULL
                //is_int($value) || is_float($value)
                is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
    }

    /**
     * @deprecated 不建议直接使用
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * @param $table
     *
     * @return Builder
     */
    public function table($table)
    {
        return (new Builder($this, new Grammar()))->table($table);
    }

    public function info()
    {
        $output = array(
            'server' => 'SERVER_INFO',
            'driver' => 'DRIVER_NAME',
            'client' => 'CLIENT_VERSION',
            'version' => 'SERVER_VERSION',
            'connection' => 'CONNECTION_STATUS'
        );
        foreach ($output as $key => $value) {
            $output[$key] = $this->pdo->getAttribute(constant('PDO::ATTR_' . $value));
        }
        return $output;
    }
}
