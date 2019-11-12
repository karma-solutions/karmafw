<?php

namespace KarmaFW\Database\Sql;


interface SqlResultsetInterface
{

	public function fetchOne();
	
	public function fetchAll();

	public function getRowsCount();

}
