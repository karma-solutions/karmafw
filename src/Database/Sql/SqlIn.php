<?php

namespace KarmaFW\Database\Sql;

use \KarmaFW\App;


class SqlIn
{
	protected $expr = null;

    public function __construct($expr)
    {
    	if (is_array($expr)) {
    		$db = App::getDb();

    		$arr = $expr;
    		$arr = array_map(function ($p) use ($db) {return $db->escape($p);}, $arr);
    		$expr = implode(', ', $arr);
    	}
        $this->expr = $expr;
    }

    public function __toString()
    {
        return $this->expr;
    }

}
