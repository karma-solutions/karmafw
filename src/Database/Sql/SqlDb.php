<?php

namespace KarmaFW\Database\Sql;


class SqlDb
{
	protected $driver = null;
	protected $last_query = null;
	protected $schema = null;
	protected $tools = null;
	public $throwOnSqlError = false;
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
		return new SqlTable($this, $table_name);
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

	public function executeSelectOne($sql, $params=[])
	{
		return $this->createQuery()->executeSelectOne($sql, $params);
	}

	public function executeInsert($query, $params=[])
	{
		return $this->createQuery()->executeInsert($sql, $params);
	}

	public function executeUpdate($query, $params=[])
	{
		return $this->createQuery()->executeUpdate($sql, $params);
	}
	
	public function executeDelete($query, $params=[])
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


	public function parseDSN($var)
	{
		return $this->tools->parseDSN($var);
	}

}
