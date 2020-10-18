<?php

namespace KarmaFW\Database\Redis;

use \Predis;


class Redis
{
	protected $dsn = null;
	protected $client = null;
	
	public function __construct($redis_dsn=null)
	{
		if (empty($redis_dsn) && defined('REDIS_DSN')) {
			$redis_dsn = REDIS_DSN;
		}

		if (empty($redis_dsn)) {
			$redis_dsn = 'tcp://localhost:6379';
		}

		$this->dsn = $redis_dsn;

		$this->setClient(new Predis\Client($redis_dsn));
	}

	public function getClient()
	{
		return $this->client;
	}

	public function setClient($client)
	{
		$this->client = $client;
	}

	public function get($key)
	{
		return $this->client->get($key);
	}

	public function set($key, $value)
	{
		return $this->client->set($key, $value);
	}
}

