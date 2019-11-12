
// DROP DATABASE
```
$db->dropDatabase('test', true);
```
// returns boolean


// CREATE DATABASE
```
$db->createDatabase('test', true);
```
// returns boolean

// USE DATABASE
```
$db->use('test');
```
// returns boolean


// CREATE TABLE
```
$db->createTable('TEST', ['id' => 'int(11) not null auto_increment', 'my_int' => 'int(11) null', 'my_text' => "varchar(32) not null default ''"], ['primary key (id)'], true);
```
// returns boolean


// INSERT ROW FROM OBJECT
```
$test = new \StdClass;
$test->my_int = '111';
$test->my_text = 'ok';
$db->getTable('TEST')->insert($test);
```
// returns insert_id

// INSERT ROW FROM ARRAY
```
$test = [
	'my_int' => '111',
	'my_text' => 'ok',
];
$db->getTable('TEST')->insert($test);
```
// returns insert_id


// UPDATE ROW
```
$db->getTable('TEST')->update(['my_int' => '11111', 'my_text' => 'ok ok'], ['id' => 1]);
```
// return affected_rows


// GET ROWS
```
$test = $db->getTable('TEST')->getAll();
```
// returns array (2 dimensions)

// GET ROW
```
$test = $db->getTable('TEST')->getOne();
```
// returns array

