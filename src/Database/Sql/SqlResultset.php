<?php

namespace KarmaFW\Database\Sql;


class SqlResultset implements SqlResultsetInterface
{
	protected $rs = null;


	function __construct($rs)
	{
		$this->rs = $rs;
	}



    public function __debugInfo() {
        return [
            'rs:protected' => is_object($this->rs) ?  (get_class($this->rs) . ' Object') : (gettype($this->rs)),
        ];
    }



	public function fetchColumn($column_name)
	{
		$row = $this->fetchOne();
		if ($row) {
			return $row[$column_name];
		}
		return null;
	}

	public function one()
	{
		// Alias of fetchOne
		return $this->fetchOne();
	}
	
	public function fetchOne()
	{
		// extend me
		return null;
	}


	public function all()
	{
		// Alias of fetchAll
		return $this->fetchAll();
	}
	
	public function fetchAll()
	{
		$rows = [];
		while ($row = $this->fetchOne()) {
			$rows[] = $row;
		}

		return $rows;
	}


	public function getRowsCount()
	{
		// extend me
		return 0;
	}

}
