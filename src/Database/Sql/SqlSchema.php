<?php

namespace KarmaFW\Database\Sql;


class SqlSchema
{
	protected $db;

	public function __construct($db)
	{
		$this->db = $db;
	}


	/* DATABASES */

	public function useDatabase($database_name) /* : bool */
	{
		$sql = "use " . $database_name;
		$ret = $this->db->createQuery()->execute($sql);
		return ($ret->getStatus() == 'success');
	}


	public function dropDatabase($database_name, $if_exists=false) /* : bool */
	{
		if ($if_exists) {
			$sql = "drop database if exists " . $database_name;

		} else {
			$sql = "drop database " . $database_name;
		}
		$ret = $this->db->createQuery()->execute($sql);
		return ($ret->getStatus() == 'success');
	}


	public function createDatabase($database_name, $if_not_exists=false) /* : bool */
	{
		if ($if_not_exists) {
			$sql = "create database if not exists " . $database_name;

		} else {
			$sql = "create database " . $database_name;
		}
		$ret = $this->db->createQuery()->execute($sql);
		return ($ret->getStatus() == 'success');
	}


	public function listDatabases($database=null) /* : array */
	{
		$sql = "show databases";

		if (! empty($database)) {
			$sql .= " like '%" . str_replace("'", "\\'", $database) . "%'";
		}
		
		$rs = $this->db->createQuery()->execute($sql);
		$rows = $rs->fetchAll();

		$databases = array_map(function ($row) {return array_values($row)[0];}, $rows);

		return $databases;
	}


	/* TABLES */

	public function createTable($table_name, array $columns, array $indexes=[], $if_not_exists=false) /* : bool */
	{
		if ($if_not_exists) {
			$sql = "create table if not exists " . $table_name . " (" . PHP_EOL;

		} else {
			$sql = "create table " . $table_name . " (" . PHP_EOL;
		}

		$sql_table_columns = [];
		foreach ($columns as $column_name => $column_type) {
			$sql_table_columns[] = "`" . $column_name . "` " . $column_type;
		}
		
		foreach ($indexes as $index_def) {
			$sql_table_columns[] = $index_def;
		}

		$sql .= implode(',' . PHP_EOL, $sql_table_columns) . PHP_EOL;

		$sql .= ")";

		$query = $this->db->createQuery();
		$ret = $query->execute($sql);
		//pre($query);
		return ($ret->getStatus() == 'success');

	}



	public function dropTable($table_name, $if_exists=false) /* : bool */
	{
		if ($if_exists) {
			$sql = "drop table if exists " . $table_name;

		} else {
			$sql = "drop table " . $table_name;
		}
		$ret = $this->db->createQuery()->execute($sql);
		return ($ret->getStatus() == 'success');
	}


	public function listTables($table=null, $database=null) /* : array */
	{
		$sql = "show tables";

		if (! empty($database)) {
			$sql .= " from `" . $database . "`";
		}

		if (! empty($table)) {
			$sql .= " like '%" . str_replace("'", "\\'", $table) . "%'";
		}
		
		$rs = $this->db->execute($sql);
		$rows = $rs->fetchAll();

		$tables = array_map(function ($row) {return array_values($row)[0];}, $rows);

		return $tables;
	}


	/* COLUMNS */

	public function listTableColumns($table, $column=null) /* : array */
	{
		$sql = "show columns from " . $table;

		if (! empty($column)) {
			$sql .= " like '%" . str_replace("'", "\\'", $column) . "%'";
		}
		
		$rs = $this->db->execute($sql);
		$rows = $rs->fetchAll();

		$columns = arrayAddKeyFromColumn($rows, 'Field');

		return $columns;
	}


	public function listTableIndexes($table) /* : array */
	{
		$sql = "show indexes from " . $table;

		$rs = $this->db->execute($sql);
		$rows = $rs->fetchAll();

		//$indexes = arrayAddKeyFromColumn($rows, 'Field');

		return $rows;
	}

}

