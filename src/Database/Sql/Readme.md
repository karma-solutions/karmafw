
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
$update = ['my_int' => '11111', 'my_text' => 'ok ok'];
$where = ['id' => 1];
$db->getTable('TEST')->update($update, $where);
```
=> return affected_rows


## FETCH DATA

### GET 1 ROW
```
$test = $db->getTable('TEST')->one($where, $options);
//$test = $db->getTable('TEST')->getOne($where, $options);
```
=> returns array


### GET MULTIPLE ROWS
```
$test = $db->getTable('TEST')->all($where, $options);
//$test = $db->getTable('TEST')->select($where, $options);
//$test = $db->getTable('TEST')->getAll($where, $options);

$test = $db->getTable('TEST')->getAllWithFoundRows($where, $options);

$test = $db->getTable('TEST')->getAllPagination($where, $nb_per_page, $page_idx, $options);
```
=> returns array (2 dimensions)


# Models


## Use the getTable method

```
$user = $db->getTable('users')->one($where, $options);
//$user = $db->getTable('users')->getOne($where, $options);
//$user = $db->getTable('users')->selectOne($where, $options);

$users = $db->getTable('users')->all($where, $options);
//$users = $db->getTable('users')->getAll($where, $options);
//$users = $db->getTable('users')->selectAll($where, $options);
//$users = $db->getTable('users')->select($where, $options);

$users_count = $db->getTable('users')->count($where, $options);
//$users_count = $db->getTable('users')->selectCount($where, $options);

$tuple = $db->getTable('users')->getAllWithFoundRows($where, $options);
$tuple = $db->getTable('users')->getAllPagination($where, $nb_per_page, $page_idx, $options);
```

```
$nb_rows_affected = $db->getTable('users')->update($update_data, $where, $options);
$user_id = $db->getTable('users')->insert($insert_data, $options);
$nb_rows_affected = $db->getTable('users')->delete($where, $options);
```


## Use the model object

### Requirement: Create model file

```
nano src/Models/User.php
```

```
<?php

namespace MyApp\Models;

use \KarmaFW\App;
use \KarmaFW\Database\Sql\SqlTableModel;


class Utilisateur extends SqlTableModel
{
    public static $table_name = 'users';
    public static $primary_key = ['user_id'];
}
```

Then, use the model object :
```
use \App\Models\User.php


$user = User::load($user_id, $where, $options);

$user = User::one($where, $options);
//$user = User::getOne($where, $options);
//$user = User::selectOne($where, $options);

$users = User::all($where, $options);
//$users = User::getAll($where, $options);
//$users = User::selectAll($where, $options);
//$users = User::select($where, $options);

$users_count = User::count($where, $options);
//$users_count = User::selectCount($where, $options);

$tuple = User::getAllWithFoundRows($where, $options);
$tuple = User::getAllPagination($where, $nb_per_page, $page_idx, $options);
```

```
$user_id = User::insert($insert_data, $options);
User::insertAll($rows_of_data, $options);
User::insertAll($rows_of_data, ['ignore' => true]);
User::insertAll($rows_of_data, ['on duplicate key' => 'user_name = values(user_name)']);

$nb_rows_affected = User::update($update_data, $where, $options);
$nb_rows_affected = User::delete($where, $options);
```


# Tools

```
$db->escape($var);
# Escape a string for a SQL query
```

```
$db->buildSqlWhere($var);
# Build a SQL where clause from an array
```

```
User::getEmpty();
$db->getTable('users')->getEmpty();
# Return an object with all expected keys and empty values
```
