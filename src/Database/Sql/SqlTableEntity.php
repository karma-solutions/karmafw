<?php

namespace KarmaFW\Database\Sql;

use \KarmaFW\App;


class SqlTableEntity /* extends \ArrayObject */
{
	protected static $table_name;
	protected static $primary_keys = null;  // example:  ['id_product']       ... or ... ['id_product', 'id_category']
	protected static $model = null;         // exemple: "\App\Models\Platforms"
	protected static $fields = null;

	protected $db;
	protected $primary_key_values = null; 	// example:  ['id_product' => 14] ... or ... ['id_product' => 123, 'id_category' => 14]
	protected $data = [];


    public function __construct($primary_key_values=null)
    {
        if ($primary_key_values !== null) {
            $this->primary_key_values = $primary_key_values;
            $this->load();

        } else {
            $this->data = (static::$model)::getEmpty();
        }
    }


    public function load()
    {
        if (empty($this->primary_key_values)) {
            throw new \Exception('missing primary_key_values');
        }

        $this->data = (static::$model)::load($this->primary_key_values);
        return $this;
    }


    public function save()
    {
        if (empty($this->primary_key_values)) {
            // insert
            $id = (static::$model)::insert($this->data);

            if (! empty($id)) {
				if (count($this->primary_keys) > 1) {
					// NOTE: on ne sait pas gérer ici les indexes multiples

				} else {
					// on affecte le insert_id a sa variable equivalente en php
					$primary_key_name = $this->primary_keys[0];
					$this->primary_key_values = [ $primary_key_name => $id ];
					$this->data[$primary_key_name] = $id;
				}
			}

        } else {
            (static::$model)::update($this->data, $this->primary_key_values);
        }

    }



	public function __set($name, $value)
	{
		$this->data[$name] = $value;
	}


	public function __get($name)
	{
        if (array_key_exists($name, $this->data)) {
			return $this->data[$name];
		}

        if (empty(static::$fields)) {
            static::$fields = array_keys((static::$model)::getEmpty());
        }

        if (! in_array($name, static::$fields)) {
            throw new \Exception("'" . $name . "' field not found in '" . static::$table_name . "' table");
        }

		return null;
	}


	/**  Depuis PHP 5.1.0  */
	public function __isset($name)
	{
		return isset($this->data[$name]);
	}


	/**  Depuis PHP 5.1.0  */
	public function __unset($name)
	{
		unset($this->data[$name]);
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
		return $this->data;
	}


	public function toJSON() 
	{
		return json_encode($this->data);
	}


	public function toCsv() 
	{
		$csv = fopen('php://temp/maxmemory:'. (5*1024*1024), 'r+');
        fputcsv($csv, $this->data);
        rewind($csv);
        return stream_get_contents($csv);
	}


	public function toCsvHeaders() 
	{
		$csv = fopen('php://temp/maxmemory:'. (5*1024*1024), 'r+');
        fputcsv($csv, array_keys($this->data));
        rewind($csv);
        return stream_get_contents($csv);
	}

}
