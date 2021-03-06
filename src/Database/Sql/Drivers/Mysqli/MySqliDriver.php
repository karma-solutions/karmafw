<?php

namespace KarmaFW\Database\Sql\Drivers\Mysqli;

use \KarmaFW\Database\Sql\SqlQuery;
use \KarmaFW\Database\Sql\SqlDriver;
use \KarmaFW\Database\Sql\SqlDriverInterface;
use \KarmaFW\Database\Sql\SqlResultSetError;


class MySqliDriver extends SqlDriver implements SqlDriverInterface
{
	protected $current_recordset = null;


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


	public function execute($query, $mode_use_result=false)
	{
		if (! $this->connected) {
			if ($this->db->throwOnConnectionError) {
				throw new \Exception("Cannot execute query (reason: Not connected to the database)", 1);
			}
			return null;
		}

		//$mode_use_result = false; // TODO: ce mode permet de ne pas stocker tout le resultat en local (ideal pour les gros resultats qui depassent la RAM)
		if ($mode_use_result) {
			if ($this->current_recordset) {
				mysqli_free_result($this->current_recordset);
				$this->current_recordset = null;
			}

			$rs = mysqli_query($this->conn, $query, MYSQLI_USE_RESULT);

		} else {
			$rs = mysqli_query($this->conn, $query);
		}


		if (is_bool($rs)) {
			$this->current_recordset = null;

		} else {
			$this->current_recordset = $rs;
		}

		$error_code = $this->getConn()->errno;
		if ($error_code) {
			$error_msg = $this->getConn()->error;
			return new SqlResultSetError($query, $error_code, $error_msg);
		}

		if (strpos($query, " SQL_CALC_FOUND_ROWS ")) {
			$tmp_query = new SqlQuery($this->db);
	        $found_rows = $tmp_query->execute('SELECT FOUND_ROWS() AS found_rows')->fetchColumn('found_rows');
		} else {
			$found_rows = null;
		}

		return new MysqliResultset($rs, $found_rows);
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
