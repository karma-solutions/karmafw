
```
# Create connection
$db = App::getDb('default', 'mysql://user:password@localhost/db_name');

# Call known connection
$db = App::getDb('default');

# Call default connection (if known)
$db = App::getDb();
```

# MANAGE DATABASES

## DROP DATABASE
```
$db->dropDatabase('test', true);
```
=> returns boolean

## CREATE DATABASE
```
$db->createDatabase('test', true);
```
=> returns boolean

## USE DATABASE
```
$db->use('test');
```
=> returns boolean


# MANAGE TABLES

## LIST TABLES
```
$db->listTables($table_name=null, $database_name=null);
```

## CREATE TABLE
```
$db->createTable('TEST', ['id' => 'int(11) not null auto_increment', 'my_int' => 'int(11) null', 'my_text' => "varchar(32) not null default ''"], ['primary key (id)'], true);
```
=> returns boolean



# MANAGE DATA

## INSERT ROWS

```
$test_id = $db->executeInsert("insert into TEST (result) values ('ok')");
```

### INSERT ROW FROM ARRAY
```
$test = [
    'my_int' => '111',
    'my_text' => 'ok',
];
$db->getTable('TEST')->insert($test);
```
=> returns insert_id

### INSERT ROW FROM OBJECT
```
$test = new \StdClass;
$test->my_int = '111';
$test->my_text = 'ok';
$db->getTable('TEST')->insert($test);
```
=> returns insert_id


## UPDATE ROWS
```
$nb_rows_affected = $db->executeUpdate("update TEST set result = 'ok' where test_id = 1");
```

```
$update = ['my_int' => '11111', 'my_text' => 'ok ok'];
$where = ['id' => 1];
$db->getTable('TEST')->update($update, $where);
```
=> return affected_rows


## DELETE ROWS

```
$nb_rows_affected = $db->executeDelete("delete from TEST where test_id = 1");
```

```
$where = ['test_id' => 1];
$db->getTable('TEST')->delete($where);
```


## FETCH DATA

```
$rows = $db->executeSelect("select * from TEST");
```

### GET 1 ROW
```
$test = $db->getTable('TEST')->one($where, $options);
//$test = $db->getTable('TEST')->getOne($where, $options);
//$test = $db->getTable('TEST')->selectOne($where, $options);
```
=> returns array


### GET MULTIPLE ROWS
```
$tests = $db->getTable('TEST')->all($where, $options);
//$tests = $db->getTable('TEST')->getAll($where, $options);
//$tests = $db->getTable('TEST')->selectAll($where, $options);
//$tests = $db->getTable('TEST')->select($where, $options);

$tests_count = $db->getTable('TEST')->count($where, $options);
//$tests_count = $db->getTable('TEST')->selectCount($where, $options);

$tuple = $db->getTable('TEST')->getAllWithFoundRows($where, $options);

$tuple = $db->getTable('TEST')->getAllPagination($where, $nb_per_page, $page_idx, $options);
```
=> returns array (2 dimensions)


# Models


## Use the getTable method

```
$test = $db->getTable('tests')->one($where, $options);
//$test = $db->getTable('tests')->getOne($where, $options);
//$test = $db->getTable('tests')->selectOne($where, $options);

$tests = $db->getTable('tests')->all($where, $options);
//$tests = $db->getTable('tests')->getAll($where, $options);
//$tests = $db->getTable('tests')->selectAll($where, $options);
//$tests = $db->getTable('tests')->select($where, $options);

$tests_count = $db->getTable('tests')->count($where, $options);
//$tests_count = $db->getTable('tests')->selectCount($where, $options);

$tuple = $db->getTable('tests')->getAllWithFoundRows($where, $options);

$tuple = $db->getTable('tests')->getAllPagination($where, $nb_per_page, $page_idx, $options);
```

```
$nb_rows_affected = $db->getTable('tests')->update($update_data, $where, $options);
$test_id = $db->getTable('tests')->insert($insert_data, $options);
$nb_rows_affected = $db->getTable('tests')->delete($where, $options);
```


## Use the model object

### Requirement: Create model file

```
nano src/Models/Test.php
```

```
<?php

namespace MyApp\Models;

use \KarmaFW\App;
use \KarmaFW\Database\Sql\SqlTableModel;


class Utilisateur extends SqlTableModel
{
    public static $table_name = 'tests';
    public static $primary_key = ['test_id'];
}
```

Then, use the model object :
```
use \App\Models\Test.php


$test = Test::load($test_id, $where, $options);

$test = Test::one($where, $options);
//$test = Test::getOne($where, $options);
//$test = Test::selectOne($where, $options);

$tests = Test::all($where, $options);
//$tests = Test::getAll($where, $options);
//$tests = Test::selectAll($where, $options);
//$tests = Test::select($where, $options);

$tests_count = Test::count($where, $options);
//$tests_count = Test::selectCount($where, $options);

$tuple = Test::getAllWithFoundRows($where, $options);
$tuple = Test::getAllPagination($where, $nb_per_page, $page_idx, $options);
```

```
$test_id = Test::insert($insert_data, $options);
Test::insertAll($rows_of_data, $options);
Test::insertAll($rows_of_data, ['ignore' => true]);
Test::insertAll($rows_of_data, ['on duplicate key' => 'test_name = values(test_name)']);

$nb_rows_affected = Test::update($update_data, $where, $options);
$nb_rows_affected = Test::delete($where, $options);
```


# Tools

```
$db->escape($var);
# Returns an escaped string of $var
```

```
$db->buildSqlWhere($var);
# Builds a SQL where clause from an array
```

```
Test::getEmpty();
$db->getTable('tests')->getEmpty();
# Returns an object with all expected keys and empty values
```
