<?php

namespace KarmaFW\Database\Sql;


interface SqlDriverInterface
{

	public function connect();

	public function disconnect();

	public function isConnected();

	public function execute($query);

	public function getInsertId();
	
	public function getAffectedRowsCount();

}

