<?php

namespace KarmaFW\Database\Sql;


class SqlDb
{
	protected $driver = null;
	protected $last_query = null;
	protected $schema = null;
	protected $tools = null;
	public $throwOnSqlError = 1;
	public $throwOnConnectionError = true;


	public function __construct($dsn=null, $driver_class=null)
	{
		$this->schema = new SqlSchema($this);
		$this->tools = new SqlTools($this);

		$credentials = $this->parseDSN($dsn);

		if (empty($driver_class)) {
			$driver_name = $credentials['driver'];
			$driver_class = $this->getDriverClass($driver_name);
		}

		if (empty($driver_class)) {
			throw new \Exception("No SQL driver specified. Please define DB_DSN with something like 'mysql://root:pass@localhost/my_app'", 1);
		}


		$this->driver = new $driver_class($this, $credentials);

		if (! empty($credentials['charset'])) {
			$charset = $credentials['charset'];
			$this->execute("set names " . $charset);
		}

	}


    public function __debugInfo() {
        return [
            'driver:protected' => get_class($this->driver) ." Object",
        ];
    }


	/* #### */


	protected function getDriverClass($driver_name)
	{
		$available_drivers = [
			'mysql' => \KarmaFW\Database\Sql\Drivers\Mysqli\MySqliDriver::class,
			'sqlite' => \KarmaFW\Database\Sql\Drivers\Mysqli\SqliteDriver::class,
		];
		return (! empty($driver_name) && isset($available_drivers[$driver_name])) ? $available_drivers[$driver_name] : null;
	}


	public function getDriver()
	{
		return $this->driver;
	}


	public function setLastQuery(SqlQuery $last_query)
	{
		return $this->last_query = $last_query;
	}

	public function getLastQuery()
	{
		return $this->last_query;
	}



	public function createQuery($sql=null)
	{
		return new SqlQuery($this, $sql);
	}



	public function getTable($table_name) /* : SqlTable */
	{
		return new SqlTable($table_name, $this);
	}


	public function createOrmItem($table_name, $primary_key_values=[]) /* : SqlOrmModel */
	{
		return new SqlOrmModel($table_name, $primary_key_values, $this);
	}


	/* CONNECTION */


	public function connect()
	{
		return $this->getDriver()->connect();
	}


	public function disconnect()
	{
		return $this->getDriver()->disconnect();
	}


	public function isConnected()
	{
		return $this->getDriver()->isConnected();
	}


	/* #### */


	public function execute($sql, $params=[])
	{
		return $this->createQuery()->execute($sql, $params);
	}

	public function query($sql, $params=[])
	{
		// Alias of executeSelectAll
		return $this->executeSelectAll($sql, $params);
	}
	
	public function executeSelect($sql, $params=[])
	{
		// Alias of executeSelectAll
		return $this->executeSelectAll($sql, $params);
	}

	public function executeSelectAll($sql, $params=[])
	{
		return $this->createQuery()->executeSelectAll($sql, $params);
	}

	public function executeSelectAllWithFoundRows($query, $params=[])
	{
		return $this->createQuery()->executeSelectAllWithFoundRows($query, $params);
	}

	public function executeSelectAllPagination($query, $nb_per_page=10, $page_idx=1, $params=[])
	{
		return $this->createQuery()->executeSelectAllPagination($query, $nb_per_page, $page_idx, $params);
	}

	public function executeSelectOne($sql, $params=[])
	{
		return $this->createQuery()->executeSelectOne($sql, $params);
	}

	public function executeSelectValue($sql, $column_name, $params=[])
	{
		return $this->createQuery()->executeSelectValue($sql, $column_name, $params);
	}

	public function executeInsert($sql, $params=[])
	{
		return $this->createQuery()->executeInsert($sql, $params);
	}

	public function executeUpdate($sql, $params=[])
	{
		return $this->createQuery()->executeUpdate($sql, $params);
	}

	public function executeDelete($sql, $params=[])
	{
		return $this->createQuery()->executeDelete($sql, $params);
	}


	public function getInsertId()
	{
		return $this->getDriver()->getInsertId();
	}


	public function getAffectedRowsCount()
	{
		return $this->getDriver()->getAffectedRowsCount();
	}


	/* SCHEMA */

	// DATABASE

	public function useDatabase($database_name)
	{
		return $this->schema->useDatabase($database_name);
	}

	public function dropDatabase($database_name, $if_exists=false)
	{
		return $this->schema->dropDatabase($database_name, $if_exists);
	}

	public function createDatabase($database_name, $if_not_exists=false)
	{
		return $this->schema->createDatabase($database_name, $if_not_exists);
	}

	public function listDatabases($database_name=null)
	{
		return $this->schema->listDatabases($database_name);
	}

	// TABLE

	public function createTable($table_name, array $columns, array $indexes=[], $if_not_exists=false)
	{
		return $this->schema->createTable($table_name, $columns, $indexes, $if_not_exists);
	}

	public function dropTable($table_name, $if_exists=false)
	{
		return $this->schema->dropTable($table_name, $if_exists);
	}

	public function listTables($table_name=null, $database_name=null)
	{
		return $this->schema->listTables($table_name, $database_name);
	}

	/*
	public function getTable($table_name)
	{
		return $this->schema->getTable($table_name);
	}
	*/


	// COLUMN

	public function listTableColumns($table_name, $column_name=null)
	{
		return $this->schema->listTableColumns($table_name, $column_name);
	}

	public function listTableIndexes($table_name)
	{
		return $this->schema->listTableIndexes($table_name);
	}



	/* TOOLS */


	public function escape($var)
	{
		return $this->tools->escape($var);
	}


	public function buildSqlWhere($var)
	{
		return $this->tools->buildSqlWhere($var);
	}


	public function buildSqlUpdateValues($var)
	{
		return $this->tools->buildSqlUpdateValues($var);
	}


	public function buildSqlInsertValues($var)
	{
		return $this->tools->buildSqlInsertValues($var);
	}


	public function buildSqlWhereSearch($q='', $search_fields=[], $min_str_length=1, $max_words=10, $all_words_required=false)
	{
		return $this->tools->buildSqlWhereSearch($q, $search_fields, $min_str_length, $max_words, $all_words_required);
	}

	public function buildSqlWhereSearch_beta($q='', $search_fields=[], $min_str_length=1, $max_words=10, $all_words_required=false, $use_soundex=false, $use_levenshtein=false)
	{
		return $this->tools->buildSqlWhereSearch_beta($q, $search_fields, $min_str_length, $max_words, $all_words_required, $use_soundex, $use_levenshtein);
	}


	public function parseDSN($var)
	{
		return $this->tools->parseDSN($var);
	}

}
