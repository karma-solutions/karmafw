<?php

namespace KarmaFW\Database\Sql;

use \KarmaFW\App;
use \KarmaFW\Database\Sql\SqlResultSetError;


class SqlQuery
{
	protected $db;
	protected $duration = null;
	protected $error = null;
	protected $recordset = null;
	protected $status = 'ready';
	protected $query = null;
	protected $results_rows_count = null;
	protected $affected_rows_count = null;

	public function __construct($db, $query=null)
	{
		$this->db = $db;
		$this->query = $query;
	}


	public function __toString()
	{
		return $this->getQuery();
	}


    public function __debugInfo() {
        return [
            //'db:protected' => get_class($this->db) ." Object",
            'db:protected' => $this->db,
            'status:protected' => $this->status,
            'error:protected' => $this->error,
            'query:protected' => $this->query,
            'duration:protected' => $this->duration,
            //'recordset:protected' => get_class($this->recordset) ." Object",
            'recordset:protected' => $this->recordset,
            'results_rows_count:protected' => $this->results_rows_count,
            'affected_rows_count:protected' => $this->affected_rows_count,
        ];
    }

    /*
	public function fetchColumn($column_name)
	{
		if ($this->status == 'ready') {
			$this->execute();
		}
		return $this->recordset->fetchColumn($column_name);
	}


	public function fetchOne()
	{
		if ($this->status == 'ready') {
			$this->execute();
		}
		return $this->recordset->fetchOne();
	}


	public function fetchAll()
	{
		if ($this->status == 'ready') {
			$this->execute();
		}
		return $this->recordset->fetchAll();
	}
	*/


	public function getQuery()
	{
		return $this->query;
	}


	public function getStatus()
	{
		return $this->status;
	}


	public function execute($query=null, $params=[], $nb_tries=1)
	{
		if (! $this->db->isConnected()) {
			$this->db->connect();
		}

		if (empty($query)) {
			$query = $this->query;
		}

		if (! empty($params)) {
			$parts = explode('?', $query);

			$query = array_shift($parts);
			$i = 1;
			foreach ($params as $param) {
				if (empty($parts)) {
					throw new \Exception('SqlQuery::execute() => params error (1) [QUERY: ' . preg_replace('/\s+/', ' ', $query) . ' ]');
				}
				$param = $this->db->escape($param);
				$query .= $param;
				$query .= array_shift($parts);
				$i++;
			}
			if (! empty($parts)) {
				throw new \Exception('SqlQuery::execute() => params error (2) [QUERY: ' . preg_replace('/\s+/', ' ', $query) . ' ]');
			}
		}

		$this->query = preg_replace('/\s+/', ' ', $query);

		$this->status = 'running';
		$ts_start = microtime(true);
		

		if (! empty($_GET['debug_sql'])) {
			echo $query . "<hr />";
		}

		$_query = $query;
		if (! empty($_GET['dry_sql'])) {
			$_query = "select 1 from ( select 1 ) tmp where 0";
		}

		$mem_start = memory_get_usage();

		$rs = $this->db->getDriver()->execute($_query);

		$mem_end = memory_get_usage();
		$memory_used = $mem_end - $mem_start;
		//pre($rs);

		$ts_end = microtime(true);
		$this->duration = $ts_end - $ts_start;
		
		$this->recordset = $rs;
		$this->db->setLastQuery($this);


		// TODO: voir comment bien injecter cette dependance
		$debugbar = App::getData('debugbar');
		if ($debugbar) {
			//$debugbar['sql']->addMessage( preg_replace('/\s+/', ' ', $query) );
			
			if (isset($debugbar['sql_queries'])) {
				$error_code = 0;
				$error_msg = null;
				$is_success = true;
				if ($rs instanceOf SqlResultSetError) {
					$is_success = false;
					$error_code = $rs->getErrorCode();
					$error_msg = $rs->getErrorMessage();
				}


				$debugbar['sql_queries']->addQuery([
					'sql' => preg_replace('/\s+/', ' ', $query),
					'duration' => $this->duration,
					'duration_str' => formatDuration($this->duration),
					'row_count' => $rs->getRowsCount(),
					//'stmt_id' => null,
					//'prepared_stmt' => null,
					'params' => $params,
					'memory' => $memory_used,
					//'memory_str' => round($memory_used/1000000, 1) . "Mo",
					'memory_str' => formatSize($memory_used),
					'end_memory' => $mem_end,
					//'end_memory_str' => round($mem_end/1000000, 1) . "Mo",
					'end_memory_str' => formatSize($mem_end),
					'is_success' => $is_success,
					'error_code' => $error_code,
					'error_message' => $error_msg,
				]);
			}
			
		}


		if ($rs instanceOf SqlResultSetError) {
			// query error
			$error_code = $rs->getErrorCode();
			$error_msg = $rs->getErrorMessage();
			$this->error = $error_msg;
			$this->status = 'error';

			if ($error_code == 2006) {
				// MySQL server has gone away
				if ($nb_tries > 1) {
					sleep(1);
					return $this->execute($query, $params, $nb_tries-1);
				}
			}

			if ($this->db->throwOnSqlError) {
				throw new \Exception('SqlQuery::execute() => DB error [' . $error_code . '] : ' . $error_msg . PHP_EOL . '[QUERY: ' . preg_replace('/\s+/', ' ', $query) . ' ]');
			}
			//return null;
		
		} else {
			$this->status = 'success';
		}

		$this->results_rows_count = $rs->getRowsCount();
		$this->affected_rows_count = $this->db->getDriver()->getAffectedRowsCount();

		//return $this;
		return $rs;
	}

	

	public function executeSelect($query, $params=[])
	{
		// Alias of executeSelectAll
		return $this->executeSelectAll($query, $params);
	}

	public function executeSelectOne($query, $params=[])
	{
		return $this->execute($query, $params)->fetchOne();
	}

	public function executeSelectValue($query, $column_name, $params=[])
	{
		return $this->execute($query, $params)->fetchColumn($column_name);
	}

	public function executeSelectAll($query, $params=[])
	{
		return $this->execute($query, $params)->fetchAll();
	}


	public function executeSelectAllWithFoundRows($query, $params=[])
	{
		if (strpos($query, 'SQL_CALC_FOUND_ROWS') === false) {
			$query = str_replace("select ", "select SQL_CALC_FOUND_ROWS ", $query);
		}

		$rs = $this->execute($query, $params);
		$data = $rs->fetchAll();
		$found_rows = $rs->getfoundRowsCount();

		return ['found_rows' => $found_rows, 'data' => $data];
	}


	public function executeSelectAllPagination($query, $nb_per_page=10, $page_idx=1, $params=[])
	{
		if (true) {
			$page_idx = max(1, intval($page_idx));
			$nb_per_page = max(1, intval($nb_per_page));

			$offset = ($page_idx - 1) * $nb_per_page;
			$limit = $offset . ', ' . $nb_per_page;

			$query .= " limit " . $limit;
		}

		$result = $this->executeSelectAllWithFoundRows($query, $params);
		$found_rows = $result['found_rows'];
		$data = $result['data'];

		$pagination = [
			'page' => $page_idx,
			'limit' => $nb_per_page,
			'offset' => $offset,
			'page_rows' => count($data),
			'total_rows' => $found_rows,
			'nb_pages' => empty($nb_per_page) ? null : ceil($found_rows / $nb_per_page),
		];

		return [
			'pagination' => $pagination,
			'data' => $data,
		];
	}


	public function executeInsert($query, $params=[])
	{
		$this->execute($query, $params);
		return $this->db->getInsertId();
	}
	

	public function executeInsertAll($query, $params=[])
	{
		return $this->execute($query, $params);
	}


	public function executeUpdate($query, $params=[])
	{
		$this->execute($query, $params);
		return $this->affected_rows_count;
	}


	public function executeDelete($query, $params=[])
	{
		$this->execute($query, $params);
		return $this->affected_rows_count;
	}


	/* ### */




	public function tableSelect($table_name, $where=[], $options=[])
	{
		// Alias of tableSelectAll
		return $this->tableSelectAll($table_name, $where, $options);
	}

	public function tableSelectAll($table_name, $where=[], $options=[])
	{
		$table = new SqlTable($table_name, $this->db);
		$query = $table->buildQuery($where, $options);
		return $this->executeSelectAll($query);
	}

	public function tableSelectOne($table_name, $where=[], $options=[])
	{
		if (empty($options)) {
			$options = [];
		}
		$options['limit'] = 1;

		$table = new SqlTable($table_name, $this->db);
		$query = $table->buildQuery($where, $options);
		return $this->executeSelectOne($query);
	}





	public function tableInsert($table_name, $values=[], $options=[])
	{
		$this->tableInsertAll($table_name, [$values], $options);
		return $this->db->getInsertId();
	}


	public function tableInsertAll($table_name, $inserts=[], $options=[])
	{
		$table = new SqlTable($table_name, $this->db);
		return $table->insertAll($inserts, $options);		
	}


	public function tableUpdate($table_name, $updates=[], $where=[], $options=[])
	{
		$table = new SqlTable($table_name, $this->db);
		return $table->update($updates, $where, $options);
	}


	public function tableDelete($table_name, $where=[], $options=[])
	{
		$table = new SqlTable($table_name, $this->db);
		return $table->delete($where, $options);
	}

}
