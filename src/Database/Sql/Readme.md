
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

## CREATE TABLE
```
$db->createTable('TEST', ['id' => 'int(11) not null auto_increment', 'my_int' => 'int(11) null', 'my_text' => "varchar(32) not null default ''"], ['primary key (id)'], true);
```
=> returns boolean



# INSERT ROWS

## INSERT ROW FROM ARRAY
```
$test = [
    'my_int' => '111',
    'my_text' => 'ok',
];
$db->getTable('TEST')->insert($test);
```
=> returns insert_id

## INSERT ROW FROM OBJECT
```
$test = new \StdClass;
$test->my_int = '111';
$test->my_text = 'ok';
$db->getTable('TEST')->insert($test);
```
=> returns insert_id


# UPDATE ROWS
```
$update = ['my_int' => '11111', 'my_text' => 'ok ok'];
$where = ['id' => 1];
$db->getTable('TEST')->update($update, $where);
```
=> return affected_rows

## GET 1 ROW
```
$test = $db->getTable('TEST')->one($where, $options);
//$test = $db->getTable('TEST')->getOne($where, $options);
```
=> returns array

## GET MULTIPLE ROWS
```
$test = $db->getTable('TEST')->all($where, $options);
//$test = $db->getTable('TEST')->select($where, $options);
//$test = $db->getTable('TEST')->getAll($where, $options);

$test = $db->getTable('TEST')->getAllWithFoundRows($where, $options);

$test = $db->getTable('TEST')->getAllPagination($where=null, $nb_per_page=10, $page_idx=1, $options=[]);
```
=> returns array (2 dimensions)
