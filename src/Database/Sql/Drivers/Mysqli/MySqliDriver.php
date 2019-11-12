<?php

namespace KarmaFW\Database\Sql\Drivers\Mysqli;

use \KarmaFW\Database\Sql\SqlDriver;
use \KarmaFW\Database\Sql\SqlDriverInterface;


class MySqliDriver extends SqlDriver implements SqlDriverInterface
{

	public function connect()
	{
		extract($this->credentials);

		$this->conn = \mysqli_init();

		if (@\mysqli_real_connect($this->conn, $host, $user, $passwd, $db, $port) && ! mysqli_connect_errno()) {
			$this->connected = true;

		} else {
			$this->connected = false;

			if ($this->db->throwOnConnectionError) {
				throw new \Exception("Cannot connect to the database. " . mysqli_connect_error(), 1);
			}
		}

		return $this->connected;
	}


	public function disconnect()
	{
		mysqli_close($this->conn);
		parent::disconnect();
	}


	public function execute($query)
	{
		if (! $this->connected) {
			if ($this->db->throwOnConnectionError) {
				throw new \Exception("Cannot execute query (reason: Not connected to the database)", 1);
			}
			return null;
		}

		$rs = mysqli_query($this->conn, $query);
		return new MysqliResultset($rs);
	}


	public function getInsertId()
	{
		return $this->getConn()->insert_id;
	}


	public function getAffectedRowsCount()
	{
		return $this->getConn()->affected_rows;
	}


}
