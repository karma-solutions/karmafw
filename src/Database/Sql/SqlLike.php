<?php

namespace KarmaFW\Database\Sql;


class SqlLike
{
	protected $expr = null;

    public function __construct($expr)
    {
        $this->expr = $expr;
    }

    public function __toString()
    {
        return $this->expr;
    }

}
