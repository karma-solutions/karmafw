<?php

namespace KarmaFW\Database\Redis;


class RedisQueue
{
	protected $redis;
	protected $queue_name;


	function __construct(Redis $redis, $queue_name)
	{
		$this->redis = $redis;
		$this->queue_name = $queue_name;
	}


	function getRedis()
	{
		return $this->redis;
	}


	function getClient()
	{
		return $this->redis->getClient();
	}


	function push($data)
	{
		$data = serialize($data);
		$client = $this->redis->getClient();
		$client->rPush($this->queue_name, $data);
	}


	function unshift($data)
	{
		$data = serialize($data);
		$client = $this->redis->getClient();
		$client->lPush($this->queue_name, $data);
	}


	function pop($timeout=0)
	{
		$ts_start = microtime(true);
		$client = $this->redis->getClient();
		$data = null;

		while (! $data) {
			$data_raw = $client->rPop($this->queue_name);
			if ($data_raw) {
				$data = unserialize($data_raw);
				break;
			}

			if(connection_status() != CONNECTION_NORMAL || connection_aborted()) {
				break;
			}

			$ts_end = microtime(true);
			$duration = $ts_end - $ts_start;
			$remaining_max = $timeout - $duration;

			if ($remaining_max <= 0) {
				break;
			}

			usleep( 1000 * 1000 * min($remaining_max, 0.05) ); // 0.05 second
		}
		
		return $data;
	}


	function shift($timeout=0)
	{
		$ts_start = microtime(true);
		$client = $this->redis->getClient();
		$data = null;

		while (! $data) {
			$data_raw = $client->lPop($this->queue_name);

			if ($data_raw) {
				$data = unserialize($data_raw);
				break;
			}

			if(connection_status() != CONNECTION_NORMAL || connection_aborted()) {
				break;
			}

			$ts_end = microtime(true);
			$duration = $ts_end - $ts_start;
			$remaining_max = $timeout - $duration;

			if ($remaining_max <= 0) {
				break;
			}

			usleep( 1000 * 1000 * min($remaining_max, 0.05) ); // 0.05 second
		}

		return $data;
	}

}
