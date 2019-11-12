<?php

namespace KarmaFW\Database\Sql\Drivers\Mysqli;

use \KarmaFW\Database\Sql\SqlResultset;
use \KarmaFW\Database\Sql\SqlResultsetInterface;


class MysqliResultset extends SqlResultset implements SqlResultsetInterface
{
	protected $found_rows = null;


	function __construct($rs, $found_rows=null)
	{
		$this->rs = $rs;
		$this->found_rows = $found_rows;
	}


	public function fetchColumn($column_name)
	{
		$row = $this->fetchOne();
		return isset($row[$column_name]) ? $row[$column_name] : null;
	}


	public function fetchOne()
	{
		if (empty($this->rs)) {
			return [];
		}
		return mysqli_fetch_assoc($this->rs);
	}


	public function fetchAll()
	{
		$rows = parent::fetchAll();

		if (! is_null($this->found_rows)) {
			$rows = array(
				'FOUND_ROWS' => $this->found_rows,
				'ROWS' => $rows,
			);
		}

		return $rows;
	}


	public function getRowsCount()
	{
		if (empty($this->rs)) {
			return null;
		}
		if (is_bool($this->rs)) {
			return null;
		}
		return $this->rs->num_rows;
	}


	public function getfoundRowsCount()
	{
		return $this->found_rows;
	}

}

