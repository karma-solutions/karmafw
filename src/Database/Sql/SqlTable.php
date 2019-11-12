<?php

namespace KarmaFW\Database\Sql;


class SqlTable
{
	protected $db;
	protected $table_name;
	protected $columns = null;


	public function __construct($db, $table_name)
	{
		$this->db = $db;
		$this->table_name = $table_name;
	}


	public function getTableName()
	{
		return $this->table_name;
	}


	public function __toString()
	{
		return $this->getTableName();
	}


	public function listColumns() /* : array */
	{
		$schema = new SqlSchema($this->db);
		$this->columns = $schema->listTableColumns($table, $column=null);
		return $this->columns;
	}


	public function insert($values=[], $options=[]) /* : int */
	{
		$this->insertAll([$values], $options);
		return $this->db->getInsertId();
	}


	public function insertAll($rows=[], $options=[]) 
	{
		/*
		$rows = [
			['id' => 1, 'name' => 'foo'],
			['id' => 2, 'name' => 'bar'],
		];
		*/
		$values_array = [];
		$fields_sql = '';
		foreach ($rows as $values) {
			$insert = $this->db->buildSqlInsertValues($values);
			$values_array[] = "(" . $insert['values'] . ")";

			if (empty($fields_sql)) {
				$fields_sql = $insert['fields'];
			}
		}
		if (empty($values_array)) {
			return null;
		}

		$inserts_sql = implode(', ', $values_array);

		$query = "insert into " . $this->table_name . " (" . $fields_sql . ") values " . $inserts_sql;
		return $this->db->createQuery()->executeInsertAll($query);
	}


	public function update(array $updates=[], array $where=[], $options=[]) /* : int */
	{
		$limit_sql = (isset($options['limit']) && ! is_null($options['limit'])) ? ("limit " . $options['limit']) : "";

		$query = "update " . $this->table_name . "
					set " . $this->db->buildSqlUpdateValues($updates) . "
					where " . $this->db->buildSqlWhere($where) . "
					" . $limit_sql;
		return $this->db->createQuery()->executeUpdate($query);
	}


	public function delete(array $where=[], $options=[]) /* : int */
	{
		$limit_sql = isset($options['limit']) ? ("limit " . $options['limit']) : "";

		$query = "delete from " . $this->table_name . "
					where " . $this->db->buildSqlWhere($where) . "
					" . $limit_sql;
		return $this->db->createQuery()->executeDelete($query);
	}


	public function select($where=null, $options=[])
	{
		// Alias of getAll
		return $this->getAll($where, $options);
	}

	public function selectAll($where=null, $options=[])
	{
		// Alias of getAll
		return $this->getAll($where, $options);
	}

	public function getAll($where=null, $options=[]) /* : array */
	{
		//return $this->db->createQuery()->tableSelect($this->table_name, $where, $options)->fetchAll();

		$query = $this->buildQuery($where, $options);
		return $this->db->createQuery()->executeSelectAll($query);
	}


	public function selectOne($where=null, $options=[])
	{
		// Alias of getOne
		return $this->getOne($where, $options);
	}

	public function getOne($where=null, $options=[]) /* : array */
	{
		$options['limit'] = 1;
		return $this->getAll($where, $options)->fetchOne();
	}


	public function buildQuery($where=null, $options=[]) /* : string */
	{
		$limit_sql = isset($options['limit']) ? ("limit " . $options['limit']) : "";
		$order_by_sql = isset($options['order_by']) ? ("order by " . $options['order_by']) : "";
		$table_name = isset($options['from']) ? $options['from'] : $this->table_name;

		$select_sql = '*';
		if (! empty($options['select'])) {
			$options['select'] = is_array($options['select']) ? $options['select'] : [$options['select']];
			$select_sql = implode(', ', $options['select']);
		}
		if (! empty($options['CALC_FOUND_ROWS'])) {
			$select_sql = 'SQL_CALC_FOUND_ROWS ' . $select_sql;
		}

		$joins_sql = '';
		if (! empty($options['join'])) {
			$options['join'] = is_array($options['join']) ? $options['join'] : [$options['join']];
			$joins_sql = implode(' ', $options['join']);
		}

		$query = "select " . $select_sql . "
					from " . $this->table_name . "
					" . $joins_sql . "
					where " . $this->db->buildSqlWhere($where) . "
					" . $order_by_sql . "
					" . $limit_sql;

		if (! empty($options['debug'])) {
			echo "<pre>" .preg_replace('/\s+/', '', $query) . "</pre>";
		}

		return $query;
	}

}
