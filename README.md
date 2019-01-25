# database
The Jetea Database component. 

The following database vendors are currently supported:

- MySQL
- PostgreSQL

## Overview

* [Installation](#installation)
* [Usage](#usage)
  * [get connection](#get-connection)
  * [insert and get insert id](#insert-and-get-insert-id)
  * [insert and get the number of rows affected](#insert-and-get-the-number-of-rows-affected)
  * [update and get the number of rows affected](#update-and-get-the-number-of-rows-affected)
  * [select](#select)
  * [delete and get the number of rows affected](#delete-and-get-the-number-of-rows-affected)
  * [transaction](#transaction)
  * [get query logs](#get-query-logs)
  * [execute the given callback in "dry run"(空转) mode](#execute-the-given-callback-in-dry-run空转-mode)

## Installation

```
composer require jetea/database=~2.0 -vvv
```

## Usage

### get connection

* MySql

```
$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', '127.0.0.1', 3306, 'es_demo', 'utf8mb4');
$conn = new MySqlConnection($dsn, 'root', 123123);
```

* Postgres

```
$dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', '127.0.0.1', 3306, 'es_demo');
$conn = new PostgresConnection($dsn, 'root', 123123);
```

### insert and get insert id

```
$insertId = $conn->table('profile')->insertGetId([
    'name'      => 'test-name',
    'gender'    => 1,
    'birthday'  => '1988-12-01 01:00:01', //DATETIME
    'memo'      => 'this is a test memo',
    'lat'       => '30.54916000', //DECIMAL(10,8)
    'lng'       => '104.06761000' //DECIMAL(11,8)
]);
```

### insert and get the number of rows affected

```
$affectNum = $conn->table('profile')->insert([
    [
        'name'      => 'test-name',
        'gender'    => 1,
        'birthday'  => '1988-12-01 01:00:01',
        'memo'      => 'this is a test memo',
        'lat'       => '30.54916000',
        'lng'       => '104.06761000'
    ],
    [
        'name'      => 'test-name-1',
        'gender'    => 1,
        'birthday'  => '2010-12-01 01:00:01',
        'memo'      => 'this is another test memo',
        'lat'       => '30.54916000',
        'lng'       => '104.06761000'
    ],
]);
```

### update and get the number of rows affected

```
affectNum = $conn->update('update profile set name = :name, memo = :memo where id = :id', [
    ':name'     => 'test-name',
    ':memo'     => 'this is another memo',
    ':id'       => $id,
]);
```

### select

```
$records = $conn->select('select * from profile where id = :id', [
    ':id'   => $id,
]);
```

### delete and get the number of rows affected

```
$affectNum = $conn->delete('delete from profile where id = :id', [
    ':id'       => $id,
]);
```

### transaction
```
$conn->transaction(function ($conn) {
    //do something...
});
```

### get query logs

```
$queryLogs = $conn->getQueryLog();
```

### execute the given callback in "dry run"(空转) mode

```
$conn->pretend(function ($conn) {
    //do something...
});
```

