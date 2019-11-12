<?php

namespace KarmaFW\Database\Sql;


abstract class SqlDriver implements SqlDriverInterface
{
	protected $db = null;
	protected $credentials = [];
	protected $connected = false;
	protected $conn = null;


	public function __construct($db, $credentials=null)
	{
		$this->db = $db;

		if (! empty($credentials)) {
			$this->credentials = $credentials;
		}
	}


    public function __debugInfo() {
        return [
            'driver_name:protected' => $this->credentials['driver'],
            'conn:protected' => get_class($this->conn) ." Object",
        ];
    }


	/* #### */


	public function connect()
	{
		// EXTENDS ME
		$this->conn = new \stdClass;
		$this->connected = true;
	}

	public function disconnect()
	{
		// EXTENDS ME
		$this->conn = null;
		$this->connected = false;
	}


	public function getInsertId()
	{
		// EXTENDS ME
		return null;
	}


	public function getAffectedRowsCount()
	{
		// EXTENDS ME
		return null;
	}


	/* #### */


	public function isConnected()
	{
		return $this->connected;
	}

	public function getConn()
	{
		return $this->conn;
	}


	/* #### */


	public function execute($query)
	{
		// EXTENDS ME
		return new SqlResultset(null);
	}


}

