<?php

namespace KarmaFW\Database\Sql;

use \KarmaFW\App;


class SqlTableModel
{
	public static $table_name = '';
	public static $primary_key = [];
	protected static $default_item = [];


	public static function getDb()
	{
		return App::getDb();
	}


	public static function checkTable()
	{
		if (empty(static::$table_name)) {
			throw new \Exception("no table_name defined in " . get_called_class(), 1);
		}
	}


	public static function all($where=[], $options=[])
	{
		// Alias of getAll
		return static::getAll($where, $options);
	}

	public static function select($where=[], $options=[])
	{
		// Alias of getAll
		return static::getAll($where, $options);
	}

	public static function selectAll($where=[], $options=[])
	{
		// Alias of getAll
		return static::getAll($where, $options);
	}

	public static function get($where=[], $options=[])
	{
		// Alias of getAll
		return static::getAll($where, $options);
	}

	public static function getAll($where=[], $options=[])
	{
		$db = static::getDb();
		static::checkTable();

		return $db->getTable(static::$table_name)->select($where, $options);
	}


	public static function selectCount($where=[], $options=[])
	{
		$db = static::getDb();
		static::checkTable();

		return $db->getTable(static::$table_name)->selectCount($where, $options);
	}


	public static function getAllPagination($where=null, $options=[])
	{
		$db = static::getDb();
		static::checkTable();
		
		$tuple = $db->getTable(static::$table_name)->getAllWithFoundRows($where, $options);
		//list($found_rows, $data) = $tuple;
		return $tuple;
	}



	public static function one($where=[], $options=[])
	{
		// Alias of getOne
		return static::getOne($where, $options);
	}

	public static function selectOne($where=[], $options=[])
	{
		// Alias of getOne
		return static::getOne($where, $options);
	}

	public static function getOne($where=[], $options=[])
	{
		$db = static::getDb();
		static::checkTable();
		return $db->getTable(static::$table_name)->selectOne($where, $options);
	}


	
	public static function insert($values=[], $options=[])
	{
		$db = static::getDb();
		static::checkTable();
		return $db->getTable(static::$table_name)->insert($values, $options);
	}
	
	public static function insertAll($rows=[], $options=[])
	{
		$db = static::getDb();
		static::checkTable();
		return $db->getTable(static::$table_name)->insertAll($rows, $options);
	}

	public static function insertSelect($insert_table, $insert_keys=null, $rows=[], $options=[])
	{
		$db = static::getDb();
		static::checkTable();
		return $db->getTable(static::$table_name)->insertSelect($insert_table, $insert_keys, $rows, $options);
	}

	public static function update($updates=[], $where=[], $options=[])
	{
		$db = static::getDb();
		static::checkTable();
		return $db->getTable(static::$table_name)->update($updates, $where, $options);
	}
		
	public static function delete($where=[], $options=[])
	{
		$db = static::getDb();
		static::checkTable();
		return $db->getTable(static::$table_name)->delete($where, $options);
	}



	// load a table row by his primary key. (usage: `$user = User::load($user_id);` )
	public static function load($pk_where=[], $where=[], $options=[])
	{
		if (empty($pk_where)) {
			return null;
		}

		if (count(static::$primary_key) == 1 && is_scalar($pk_where)) {
			$pk_where = [ static::$primary_key[0] => $pk_where ];
		}

		if (empty(static::$primary_key)) {
			throw new \Exception("no primary_key defined in " . get_called_class(), 1);
		}

		if (empty($where)) {
			$where = [];
		}

		foreach (static::$primary_key as $key) {
			if (! isset($pk_where[$key])) {
				throw new \Exception("missing value for primary_key " . $key . " in " . get_called_class(), 1);
			}
			$value = $pk_where[$key];

			$where[$key] = $value;
		}

		return static::getOne($where, $options);

	}



	public static function getDefaultItem()
	{
		return array_slice(static::$default_item, 0);
	}

}
