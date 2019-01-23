# database
The Jetea Database component. 

The following database vendors are currently supported:

- MySQL
- PostgreSQL

## Installation

```
composer require jetea/database=~2.0 -vvv
```

## Usage

### getting a connection

```
$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', 'mysql', 3306, 'es_demo', 'utf8mb4');
$conn = new MySqlConnection($dsn, 'root', 123123);
```

### insert and get insert id

```
$insertId = $conn->table('profile')->insertGetId([
    'name'      => '张三',
    'gender'    => 1,
    'birthday'  => '1988-12-01 01:00:01',
    'memo'      => '一段简单的信息' . rand(1, 10),
    'lat'       => '30.54916000',
    'lng'       => '104.06761000'
]);
```

### insert and get the number of rows affected

```
$affectNum = $conn->table('profile')->insert([
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
```

### update and get the number of rows affected

```
affectNum = $conn->update('update profile set name = :name, memo = :memo where id = :id', [
    ':name'     => '王五',
    ':memo'     => '修改后的独白',
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

### execute the given callback in "dry run"(空转) mode.

```
$conn->pretend(function ($conn) {
    //do something...
});
```

