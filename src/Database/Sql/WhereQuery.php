<?php

namespace KarmaFW\Database\Sql;

use \KarmaFW\App;
//use \KarmaFW\Database\Sql\SqlResultSetError;


class WhereQuery
{
	protected $table_name;
	protected $db;
	protected $where = [];
	protected $alias = null;
	protected $selects = [];
	protected $join = [];
	protected $sets = [];
	protected $orders = [];
	protected $groups = [];
	protected $options = [];


	public function __construct($table_name, $db=null)
	{
		$this->table_name = $table_name;

		if (empty($db)) {
			$db = App::getDb();
		}
		$this->db = $db;
	}


	public function where($where)
	{
		$this->where = $where + $this->where;

		return $this;
	}

	public function alias($alias)
	{
		$this->alias = $alias;
		$this->options['alias'] = $this->alias;

		return $this;
	}



	public function select($select)
	{
		if (! is_array($select)) {
			$select = [ (string) $select ];
		}
		
		$this->selects = $select + $this->selects;
		$this->options['select'] = $this->selects;

		return $this;
	}

	public function set(array $set)
	{
		$this->sets = $set + $this->sets;

		return $this;
	}

	public function join($join)
	{
		if (! is_array($join)) {
			$join = [ (string) $join ];
		}

		$this->join = $join + $this->join;
		$this->options['join'] = $this->join;

		return $this;
	}

	public function group($group_by)
	{
		if (! is_array($group_by)) {
			$group_by = [ (string) $group_by ];
		}
		
		$this->groups = $group_by + $this->groups;
		$this->options['order by'] = $this->groups;

		return $this;
	}

	public function order($order_by)
	{
		if (! is_array($order_by)) {
			$order_by = [ (string) $order_by ];
		}
		
		$this->orders = $order_by + $this->orders;
		$this->options['order by'] = $this->orders;

		return $this;
	}



	public function insert($options=[])
	{
		return $this->db->getTable($this->table_name)->insert($this->sets, $this->options + $options);
	}

	public function update($options=[])
	{
		return $this->db->getTable($this->table_name)->update($this->sets, $this->where, $this->options + $options);
	}

	public function delete($options=[])
	{
		return $this->db->getTable($this->table_name)->delete($this->where, $this->options + $options);
	}


	public function get($options=[])
	{
		if (empty($options['select'])) {
			$options['select'] = empty($this->selects) ? '*' : $this->selects;
		}
		return $this->db->getTable($this->table_name)->all($this->where, $this->options + $options);
	}

	public function getAll($options=[])
	{
		return $this->get($options);
	}


	public function getOne($options=[])
	{
		$select = empty($this->selects) ? '*' : $this->selects;
		if (empty($options['select'])) {
			$options['select'] = $select;
		}
		return $this->db->getTable($this->table_name)->one($this->where, $this->options + $options);
	}

	public function getCount($options=[])
	{
		$select = empty($this->selects) ? '*' : $this->selects;
		if (empty($options['select'])) {
			$options['select'] = $select;
		}
		return $this->db->getTable($this->table_name)->count($this->where, $this->options + $options);
	}

	public function getAllWithFoundRows($options=[])
	{
		$select = empty($this->selects) ? '*' : $this->selects;
		if (empty($options['select'])) {
			$options['select'] = $select;
		}
		return $this->db->getTable($this->table_name)->getAllWithFoundRows($this->where, $this->options + $options);
	
	}

	public function getAllPagination($nb_per_page=10, $page_idx=1, $options=[])
	{
		$select = empty($this->selects) ? '*' : $this->selects;
		if (empty($options['select'])) {
			$options['select'] = $select;
		}
		return $this->db->getTable($this->table_name)->getAllPagination($this->where, $nb_per_page, $page_idx, $this->options + $options);
	}

}
