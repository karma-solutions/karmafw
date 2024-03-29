<?php

namespace KarmaFW\Database\Sql;

use \KarmaFW\App;


class SqlTable
{
	protected $db;
	protected $table_name;
	protected $columns = null;


	public function __construct($table_name, $db=null)
	{
		if (is_null($db)) {
			$db = App::getDb();
		}
		$this->table_name = $table_name;
		$this->db = $db;
	}


	public function getTableName()
	{
		return $this->table_name;
	}


	public function __toString()
	{
		return $this->getTableName();
	}


	public function exists($force_refresh=false)
	{
		static $tables = [];

		if (empty($tables[$this->table_name]) || $force_refresh) {
			$schema = new SqlSchema($this->db);
			$tables[$this->table_name] = $schema->tableExists($this->table_name);
		}
		return $tables[$this->table_name];
	}


	public function listColumns($force_refresh=false)
	{
		static $tables = [];

		if (empty($tables[$this->table_name]) || $force_refresh) {
			$schema = new SqlSchema($this->db);
			$tables[$this->table_name] = $schema->listTableColumns($this->table_name, $column=null);
		}

		$this->columns = $tables[$this->table_name];
		return $tables[$this->table_name];
	}


	public function getEmpty()
	{
		$columns = $this->listColumns();
		return array_map(function ($v) {return '';}, $columns);
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

		if (! empty($options['on duplicate key updates'])) {
			$options['on_duplicate_key_updates'] = $options['on duplicate key updates'];
		}
		if (! empty($options['on_duplicate_key_update'])) {
			$options['on_duplicate_key_updates'] = $options['on_duplicate_key_update'];
		}
		if (! empty($options['on duplicate key update'])) {
			$options['on_duplicate_key_updates'] = $options['on duplicate key update'];
		}
		if (! empty($options['on duplicate key'])) {
			$options['on_duplicate_key_updates'] = $options['on duplicate key'];
		}
		if (! empty($options['on_duplicate_key'])) {
			$options['on_duplicate_key_updates'] = $options['on_duplicate_key'];
		}
		if (! empty($options['on_duplicate'])) {
			$options['on_duplicate_key_updates'] = $options['on_duplicate'];
		}
		if (! empty($options['on duplicate'])) {
			$options['on_duplicate_key_updates'] = $options['on duplicate'];
		}


		$ignore_sql = empty($options['ignore']) ? '' : 'ignore';
		$on_duplicate_key_updates_sql = empty($options['on_duplicate_key_updates']) ? "" : ("on duplicate key update " . $options['on_duplicate_key_updates']);


		$chunks = [];
		if (empty($options['chunk'])) {
			$chunks = [$values_array];

		} else {
			$chunks = array_chunk($values_array, $options['chunk']);
		}

		$ok = false;

		foreach ($chunks as $chunk) {
			$inserts_sql = implode(', ', $chunk);

			// TODO: gerer on_duplicate_key_updates comme le where, dans un tableau et non dans un string

			$query = "insert " . $ignore_sql . " into " . $this->table_name . " (" . $fields_sql . ") values " . $inserts_sql . " " . $on_duplicate_key_updates_sql;

			if (! empty($options['debug'])) {
				echo "<pre>" .preg_replace('/\s+/', ' ', $query) . "</pre>";
			}

			if (isset($options['dry'])) {
				$ok = $options['dry'];
			} else {
				$ok = $this->db->createQuery()->executeInsertAll($query);
			}
		}

		return $ok;
	}


	public function insertSelect($insert_table, $insert_keys=null, $where=null, $options=[]) 
	{
		$options_select = array_slice($options, 0);
		unset($options_select['debug']);

		$ignore_sql = empty($options['ignore']) ? '' : 'ignore';
		
		$on_duplicate_key_updates_sql = empty($options['on_duplicate_key_updates']) ? "" : ("on duplicate key update " . $options['on_duplicate_key_updates']);

		$sql_keys = $insert_keys ? ("(" . $insert_keys . ")") : "";

		$insert = "insert " . $ignore_sql . " into " . $insert_table . " " . $sql_keys . PHP_EOL;

		$query = $this->buildQuery($where, $options_select);
		$query = $insert . " " . $query . " " . $on_duplicate_key_updates_sql;

		if (! empty($options['debug'])) {
			echo "<pre>" .preg_replace('/\s+/', ' ', $query) . "</pre>";
		}

		if (! empty($options['dry'])) {
			return true;
		}

		return $this->db->createQuery()->execute($query);
	}


	public function update(array $updates=[], array $where=[], $options=[]) /* : int */
	{
		$limit_sql = (isset($options['limit']) && ! is_null($options['limit'])) ? ("limit " . $options['limit']) : "";

		$join_sql = isset($options['join']) ? implode(" ", $options['join']) : "";

		$chunk_size = !empty($options['chunk']) ? $options['chunk'] : null; // delete per chunks

		if ($chunk_size) {
			$limit_sql = "limit " . $chunk_size;
		}

		/*
		if (isset($options['group by']) && empty($options['group_by'])) {
			$options['group_by'] = $options['group by'];
		}
		$group_by_sql = isset($options['group_by']) ? ("group by " . $options['group_by']) : "";
		*/

		$query = "update " . $this->table_name . "
					" . $join_sql . "
					set " . $this->db->buildSqlUpdateValues($updates) . "
					where " . $this->db->buildSqlWhere($where) . "
					" . $limit_sql;

		if (! empty($options['debug'])) {
			echo "<pre>" .preg_replace('/\s+/', ' ', $query) . "</pre>";
		}

		if (! empty($options['dry'])) {
			return true;
		}

		$affected_rows = 0;
		$loop_limit = 99999;

		while ($loop_limit--) {
			$chunk_affected_rows = $this->db->createQuery()->executeUpdate($query);
			$affected_rows += $chunk_affected_rows;

			/*
			/!\/!\/!\ ATTENTION AVEC LE UPDATE CHUNK /!\/!\/!\
			
			1) ca peut faire une boucle infinie si on faire ce genre d'update : 
			 update users set field = value where 1;
			=> si on a 1000 users et qu'on fait un chunk de 100, ca bouclera sur les 100 premiers users, sans fin

			2) si on utilise les offset alors ce genre d'update ne fonctionnera pas bien :
			 update users set field = value where field <> value;
			=> l'offset ne sera plus valide au prochains update car le where change la donne à chaque update
			*/

			if (! $chunk_size) {
				break;
			}

			if (! $chunk_affected_rows) {
				break;
			}
		}

		return $affected_rows;
	}


	public function delete(array $where=[], $options=[]) /* : int */
	{
		$limit_sql = isset($options['limit']) ? ("limit " . $options['limit']) : "";
		
		$chunk_size = !empty($options['chunk']) ? $options['chunk'] : null;

		if ($chunk_size) {
			$limit_sql = "limit " . $chunk_size;
		}


		$query = "delete from " . $this->table_name . "
					where " . $this->db->buildSqlWhere($where) . "
					" . $limit_sql;

		if (! empty($options['debug'])) {
			echo "<pre>" .preg_replace('/\s+/', ' ', $query) . "</pre>";
		}

		if (! empty($options['dry'])) {
			return true;
		}

		$affected_rows = 0;

		while (true) {
			$chunk_affected_rows = $this->db->createQuery()->executeDelete($query);
			$affected_rows += $chunk_affected_rows;

			if (! $chunk_size) {
				break;
			}

			if ($chunk_affected_rows < $chunk_size) {
				break;
			}
		}

		return $affected_rows;
	}


	public function all($where=null, $options=[])
	{
		// Alias of getAll
		return $this->getAll($where, $options);
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
		$query = $this->buildQuery($where, $options);

		if (! empty($options['dry'])) {
			return [];
		}

		$params = [];
		$returns_generator = !empty($options['generator']);

		return $this->db->createQuery()->executeSelectAll($query, $params, $returns_generator);
	}

	public function getAllWithFoundRows($where=null, $options=[]) /* : array */
	{
		$options['CALC_FOUND_ROWS'] = true;
		$query = $this->buildQuery($where, $options);

		if (! empty($options['dry'])) {
			$data = [];
			$found_rows = 0;

		} else {
			$rs = $this->db->createQuery()->execute($query);
			$data = $rs->fetchAll();
			$found_rows = $rs->getfoundRowsCount();

		}
		return ['found_rows' => $found_rows, 'data' => $data];
	}


	public function getAllPagination($where=null, $nb_per_page=10, $page_idx=1, $options=[])
	{
		if (! is_array($options)) {
			$options = [];
		}

		if (! empty($options['output'])) {
			$page_idx = 1;
			$nb_per_page = null;
			$offset = 0;
			unset($options['limit']);

		} else {
			$page_idx = max(1, intval($page_idx));
			$nb_per_page = max(1, intval($nb_per_page));

			$offset = ($page_idx - 1) * $nb_per_page;
			$options['limit'] = $offset . ', ' . $nb_per_page;
		}

		
		$result = $this->getAllWithFoundRows($where, $options);
		$found_rows = $result['found_rows'];
		$data = $result['data'];

		$pagination = [
			'page' => $page_idx,
			'limit' => $nb_per_page,
			'offset' => $offset,
			'page_rows' => count($data),
			'total_rows' => $found_rows,
			'nb_pages' => empty($nb_per_page) ? 1 : ceil($found_rows / $nb_per_page),
		];

		return [
			'pagination' => $pagination,
			'data' => $data,
		];
	}


	public function selectCount($where=null, $options=[])
	{
		$options['select'] = 'count(*) as nb';
		$row = $this->getOne($where, $options);
		return empty($row['nb']) ? 0 : $row['nb'];
	}


	public function one($where=null, $options=[])
	{
		// Alias of getOne
		return $this->getOne($where, $options);
	}

	public function selectOne($where=null, $options=[])
	{
		// Alias of getOne
		return $this->getOne($where, $options);
	}

	public function getOne($where=null, $options=[]) /* : array */
	{
		$options['limit'] = 1;
		//return $this->getAll($where, $options)->fetchOne();

		$query = $this->buildQuery($where, $options);

		if (! empty($options['dry'])) {
			return [];
		}

		return $this->db->createQuery()->executeSelectOne($query);
	}


	public function buildQuery($where=null, $options=[]) /* : string */
	{
		if (empty($options['order_by']) && ! empty($options['order by'])) {
			// alias "order by" to "order_by"
			$options['order_by'] = $options['order by'];
		}
		if (empty($options['group_by']) && ! empty($options['group by'])) {
			// alias "group by" to "group_by"
			$options['group_by'] = $options['group by'];
		}

		if (isset($options['group_by']) && is_array($options['group_by'])) {
			$options['group_by'] = implode(', ', $options['group_by']);
		}

		if (isset($options['order_by']) && is_array($options['order_by'])) {
			$options['order_by'] = implode(', ', $options['order_by']);
		}

		$limit_sql = isset($options['limit']) ? ("limit " . $options['limit']) : "";
		$offset_sql = isset($options['offset']) ? ("offset " . $options['offset']) : "";
		$group_by_sql = isset($options['group_by']) ? ("group by " . $options['group_by']) : "";
		$order_by_sql = isset($options['order_by']) ? ("order by " . $options['order_by']) : "";
		$having_sql = isset($options['having']) ? ("having " . $options['having']) : "";
		$table_name = isset($options['from']) ? $options['from'] : $this->table_name;

		$select_sql = '*';
		if (! empty($options['select'])) {
			$options['select'] = is_array($options['select']) ? $options['select'] : [$options['select']];
			$select_sql = implode(', ', $options['select']);
		}
		if (! empty($options['CALC_FOUND_ROWS']) && stripos($select_sql, 'SQL_CALC_FOUND_ROWS') === false) {
			$select_sql = 'SQL_CALC_FOUND_ROWS ' . $select_sql;
		}
		if (! empty($options['where'])) {
			if (empty($where)) {
				$where = $options['where'];
			} else {
				$where = array_merge($where, $options['where']);
			}
		}

		$table_alias = empty($options['alias']) ? "" : $options['alias'];

		$joins_sql = '';
		if (! empty($options['join'])) {
			$options['join'] = is_array($options['join']) ? $options['join'] : [$options['join']];
			$joins_sql = implode(' ', $options['join']);
		}


		$query = "select " . $select_sql . "
					from " . $this->table_name . " " . $table_alias . "
					" . $joins_sql . "
					where " . $this->db->buildSqlWhere($where) . "
					" . $group_by_sql . "
					" . $having_sql . "
					" . $order_by_sql . "
					" . $limit_sql . "
					" . $offset_sql;

		if (! empty($options['debug'])) {
			echo "<pre>" .preg_replace('/\s+/', ' ', $query) . "</pre>";
		}

		return $query;
	}


	public function where($where=[])
	{
		return (new WhereQuery($this->table_name, $this->db))->where($where);
	}

}
