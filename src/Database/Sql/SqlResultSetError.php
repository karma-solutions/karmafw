<?php

namespace KarmaFW\Database\Sql;


class SqlResultSetError
{
	protected $query;
	protected $error_code;
	protected $error_msg;

	public function __construct($query, $error_code, $error_msg)
	{
		$this->query = $query;
		$this->error_code = $error_code;
		$this->error_msg = $error_msg;

	}

	public function getErrorCode()
	{
		return $this->error_code;
	}
	
	public function getErrorMessage()
	{
		return $this->error_msg;
	}

	public function getRowsCount()
	{
		return 0;
	}

	public function getfoundRowsCount()
	{
		return 0;
	}

	public function fetchAll()
	{
		return [];
	}

	public function fetchOne()
	{
		return null;
	}

	public function fetchColumn($column_name)
	{
		return null;
	}
	
}
