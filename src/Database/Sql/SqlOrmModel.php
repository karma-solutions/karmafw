<?php

namespace KarmaFW\Database\Sql;

use \KarmaFW\App;


class SqlOrmModel
{
	protected $db;
	protected $table_row = [];
	protected $status = 'ready';
	protected $primary_keys = null; 		// example:  ['id_product']       ... or ... ['id_product', 'id_category']
	protected $primary_key_values = null; 	// example:  ['id_product' => 14] ... or ... ['id_product' => 123, 'id_category' => 14]
	public $table_name;
	public $autosave = false;


	public function __construct($table_name=null, $primary_key_values=[], $db=null)
	{
		if (is_null($db)) {
			$db = App::getDb();
		}
		$this->db = $db;
		$this->table_name = $table_name;
		$this->primary_key_values = $primary_key_values;

		if (! empty($primary_key_values)) {
			$this->load($primary_key_values);
		}
	}



	public function __set($name, $value)
	{
		$this->table_row[$name] = $value;
		$this->status = 'unsaved';

		if ($this->autosave) {
			$this->save();
		}
	}

	public function __get($name)
	{
		if (array_key_exists($name, $this->table_row)) {
			return $this->table_row[$name];
		}

		$trace = debug_backtrace();
		trigger_error(
			'Propriété non-définie via __get() : ' . $name .
			' dans ' . $trace[0]['file'] .
			' à la ligne ' . $trace[0]['line'],
			E_USER_NOTICE);
		return null;
	}

	/**  Depuis PHP 5.1.0  */
	public function __isset($name)
	{
		return isset($this->table_row[$name]);
	}

	/**  Depuis PHP 5.1.0  */
	public function __unset($name)
	{
		unset($this->table_row[$name]);
	}



	public function clear()
	{
		return $this->loadFromArray([]);
	}
	

	public function asArray()
	{
		// Alias of toArray
		return $this->toArray();	
	}

	public function getArray()
	{
		// Alias of toArray
		return $this->toArray();	
	}

	public function toArray()
	{
		return $this->table_row;
	}

	public function toJSON() 
	{
		return json_encode($this->table_row);
	}


	public function fetchPrimaryKeys()
	{
		if (empty($this->table_name)) {
			return false;
		}

		$this->primary_keys = [];

		$columns = $this->db->listTableColumns($this->table_name);
		foreach ($columns as $column) {
			if ($column['Key'] == 'PRI') {
				$this->primary_keys[] = $column['Field'];
			}
		}

		return $this->primary_keys;
	}
	

	public function setPrimaryKeysValues(array $primary_key_values=null)
	{
		$this->primary_key_values = $primary_key_values;
	}


	public function loadFromArray($data, $primary_key_values=null)
	{
		$this->primary_key_values = $primary_key_values;
		$this->table_row = array_slice($data, 0);
		//pre($this->table_row);

		if (! empty($this->primary_keys)) {
			foreach ($this->primary_keys as $column_name) {
				if (isset($this->table_row[$column_name])) {
					$this->primary_key_values[$column_name] = $this->table_row[$column_name];
				}
			}
		}

		return $this;
	}


	public function load(array $primary_key_values)
	{
		$this->primary_key_values = $primary_key_values;

		$data = $this->db->createQuery()->tableSelectOne($this->table_name, $this->primary_key_values, ['limit' => 1]);
		
		if (is_null($data)) {
			$this->primary_key_values = [];
			$this->table_row = [];
			return null;
		}

		$result = $this->loadFromArray($data, $primary_key_values);

		return $result;
	}


	public function save($force = false)
	{
		if (empty($this->primary_keys)) {
			// on recupere les primary_keys depuis le schema de ma table sql
			$this->fetchPrimaryKeys();

			if (empty($this->primary_keys)) {
				// cannot update because no primary key found
				return false;
			}
		}

		if ($this->status == 'saved' && ! $force) {
			return true;
		}

		if (empty($this->primary_key_values)) {
			// INSERT 

			$id = $this->db->createQuery()->tableInsert($this->table_name, $this->table_row);

			if (! empty($id)) {
				if (count($this->primary_keys) > 1) {
					// TODO: gerer correctement les index multiples (normalement un seul des champ est en autoincrement. TODO...)
					$this->primary_key_values = [];
					foreach ($this->primary_keys as $column_name) {
						if (! isset($this->table_row[$column_name])) {
							$this->table_row[$column_name] = null; // TODO: trouver mieux que null
						}
						$this->primary_key_values = [ $column_name => $this->table_row[$column_name] ];
					}

				} else {
					$column_name = $this->primary_keys[0];
					$this->primary_key_values = [ $column_name => $id ];

					// on affecte le insert_id a sa variable equivalente en php
					$this->table_row[$column_name] = $id;
				}
			}

		} else {
			// UPDATE

			$this->db->createQuery()->tableUpdate($this->table_name, $this->table_row, $this->primary_key_values, ['limit' => 1]);
			
		}

		$this->status = 'saved';

		return true;
	}


}
