<?php

namespace Tests\Jetea\Database;

use Jetea\Database\MySqlConnection;

/**
 * 单元测试
 */
class DatabaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MySqlConnection
     */
    private $conn;

    public function setUp()
    {
        parent::setUp();

        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', 'mysql', 3306, 'es_demo', 'utf8mb4');
        $this->conn = new MySqlConnection($dsn, 'root', 123123);
    }

    private function getLast()
    {
        return $this->conn->select('select * from profile order by id desc limit 1');
    }

    private function insertGetId()
    {
        return $this->conn->table('profile')->insertGetId([
            'name'      => '张三',
            'gender'    => 1,
            'birthday'  => '1988-12-01 01:00:01',
            'memo'      => '一段简单的信息' . rand(1, 10),
            'lat'       => '30.54916000',
            'lng'       => '104.06761000'
        ]);
    }

    public function testInsertGetId()
    {
        $lastRecord = $this->getLast();
        $lastId = empty($lastRecord) ? 0 : $lastRecord[0]['id'];

        $id = $this->insertGetId();

        $this->assertEquals($lastId + 1, (int) $id);
    }

    public function testInsert()
    {
        $affectNum = $this->conn->table('profile')->insert([
            [
                'name'      => '张三',
                'gender'    => 1,
                'birthday'  => '1988-12-01 01:00:01',
                'memo'      => '一段简单的信息' . rand(1, 10),
                'lat'       => '30.54916000',
                'lng'       => '104.06761000'
            ],
            [
                'name'      => '李四',
                'gender'    => 1,
                'birthday'  => '2010-12-01 01:00:01',
                'memo'      => '一段简单的信息 ' . rand(1, 10),
                'lat'       => '30.54916000',
                'lng'       => '104.06761000'
            ],
        ]);

        $this->assertEquals(2, $affectNum);
    }

    public function testSelect()
    {
        $id = $this->insertGetId();

        $lastRecord = $this->getLast();

        $record = $this->conn->select('select * from profile where id = :id', [
            ':id'   => $id,
        ]);

        $this->assertEquals($lastRecord[0]['memo'], $record[0]['memo']);
    }

    public function testUpdate()
    {
        $id = $this->insertGetId();

        $affectNum = $this->conn->update('update profile set name = :name, memo = :memo where id = :id', [
            ':name'     => '王五',
            ':memo'     => '修改后的独白',
            ':id'       => $id,
        ]);

        $this->assertEquals(1, $affectNum);
    }

    public function testDelete()
    {
        $id = $this->insertGetId();

        $affectNum = $this->conn->delete('delete from profile where id = :id', [
            ':id'       => $id,
        ]);

        $this->assertEquals(1, $affectNum);
    }
}
