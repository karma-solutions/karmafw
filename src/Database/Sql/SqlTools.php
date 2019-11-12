<?php

namespace KarmaFW\Database\Sql;


class SqlTools
{
    protected $db;


    public function __construct($db)
    {
        $this->db = $db;
    }



    public function parseDSN($dsn)
    {
        // PARSE A DSN LIKE mysql://user:password@host:port/database
        // AND RETURNS driver,host,port,user,passwd,db

        if (empty($dsn)) {
            return [
                'driver' => '',
                'host' => '',
                'port' => '',
                'user' => '',
                'passwd' => '',
                'db' => '',
            ];
        }

        $parts0 = explode(':', $dsn);
        $driver = $parts0[0];

        $parts1 = explode('/', $dsn);
        $user_passwd_host_port = $parts1[2];
        $db = ! empty($parts1[3]) ? $parts1[3] : null;

        $parts2 = explode('@', $user_passwd_host_port);
        if (count($parts2) > 1) {
            // USER (AND OPTIONNALY PASSWORD) IS DEFINED
            // mysql://user@host/database
            // mysql://user@host:port/database
            // mysql://user:password@host/database
            // mysql://user:password@host:port/database
            $user_password = $parts2[0];
            $host_port = $parts2[1];
        } else {
            // USER AND PASSWORD ARE NOT DEFINED
            // mysql://host/database
            // mysql://host:port/database
            $user_password = '';
            $host_port = $parts2[0];
        }

        $parts3 = explode(':', $host_port);
        $host = $parts3[0];
        if (count($parts3) > 1) {
            // HOST AND PORT ARE DEFINED
            // mysql://user@host:port/database
            // mysql://user:password@host:port/database
            $port = $parts3[1];
        } else {
            // HOST IS DEFINED. PORT IS NOT DEFINED
            // mysql://user@host/database
            // mysql://user:password@host/database
            $port = 3306;
        }

        $parts4 = explode(':', $user_password);
        $user = $parts4[0];
        if (count($parts4) > 1) {
            // USER AND PASSWORD ARE DEFINED
            // mysql://user:password@host/database
            // mysql://user:password@host:port/database
            $passwd = $parts4[1];
        } else {
            // USER IS DEFINED. PASSWORD IS NOT DEFINED
            // mysql://user@host/database
            // mysql://user@host:port/database
            $passwd = '';
        }

        return [
            'driver' => $driver,
            'host' => $host,
            'port' => $port,
            'user' => $user,
            'passwd' => $passwd,
            'db' => $db,
        ];
    }


	public function escape($var)
	{
		if (is_null($var)) {
			return 'NULL';
		}
		if (is_bool($var)) {
			return intval($var);
		}
		if (is_int($var)) {
			return intval($var);
		}
		if (is_float($var)) {
			return floatval($var);
		}
		return "'" . $this->db->getDriver()->getConn()->real_escape_string($var) . "'";
	}


	public function buildSqlWhere($where)
	{
        $where_sql = array("1" => "1");
        if (! empty($where)) {
            foreach ($where as $key => $value) {
                if (is_null($value)) {
                    $where_sql[] = $key . ' is null';

                }else if (is_bool($value) || is_int($value)) {
                    $where_sql[] = $key . ' = ' . intval($value);

                }else if (is_float($value)) {
                    $where_sql[] = $key . ' = ' . floatval($value);

                }else if (is_string($value)) {
                    $where_sql[] = $key . ' = ' . $this->escape($value);
/*
                }else if ($value instanceof DB\DbWhere) {
                    $where_sql[] = (string) $value;

                }else if ($value instanceof DB\DbExpr) {
                    $where_sql[] = $key . ' = ' . (string) $value;
*/
                    
                }else{
                    $where_sql[] = $key . ' = ' . $this->escape($value);
                    //$where_sql[] = $key . ' = ' . (string) $value;
                }
            }
        }
        //print_r($where_sql);
        return implode(" and ", $where_sql);

	}

	public function buildSqlUpdateValues($values)
	{
        $values_sql = array();

        if (is_object($values)) {
            $values = get_object_vars($values);
        }

        foreach ($values as $key => $value) {
            if (is_null($value)) {
                $values_sql[] = $key . ' = NULL';
            }else if (gettype($value) === 'string') {
                $values_sql[] = $key . ' = ' . $this->escape($value);
            }else if (gettype($value) === 'boolean') {
                $values_sql[] = $key . ' = ' . intval($value);
            }else{
                $values_sql[] = $key . ' = ' . $value;
            }
        }
        return implode(", ", $values_sql);
	}

	public function buildSqlInsertValues($values)
	{
        $fields_sql = array();
        $values_sql = array();

        if (is_object($values)) {
            $values = get_object_vars($values);
        }

        foreach ($values as $key => $value) {
            if (is_null($value)) {
                $values_sql[] = 'NULL';
            }else if (gettype($value) === 'string') {
                $values_sql[] = $this->escape($value);
            }else if (gettype($value) === 'boolean') {
                $values_sql[] = intval($value);
            }else{
                $values_sql[] = $value;
            }
            $fields_sql[] = $key;
        }
        return array(
        	'fields' => implode(', ', $fields_sql),
        	'values' => implode(', ', $values_sql),
        );
	}

}

