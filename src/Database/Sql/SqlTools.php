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
        if (! $this->db->isConnected()) {
            $this->db->connect();
        }
        
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
                    
                }else if ($value instanceof \KarmaFW\Database\Sql\SqlLike) {
                    $where_sql[] = $key . ' like ' . (string) $this->escape($value);
                    
                }else if ($value instanceof \KarmaFW\Database\Sql\SqlIn) {
                    $value = (string) $value;
                    if (empty($value)) {
                        $where_sql[] = '0';
                    } else {
                        $where_sql[] = $key . ' in (' . $value . ')';
                    }
                    
                }else if ($value instanceof \KarmaFW\Database\Sql\SqlExpr) {
                    $where_sql[] = $key . ' = ' . (string) $value;
                    
                }else if ($value instanceof \KarmaFW\Database\Sql\SqlWhere) {
                    $where_sql[] = (string) $value;
                    
                }else{
                    //pre($where, 1);
                    $where_sql[] = $key . ' = ' . $this->escape($value);
                    //$where_sql[] = $key . ' = ' . (string) $value;
                }
            }
        }
        //pre($where_sql, 1);

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



    public function buildSqlWhereSearch($q='', $search_fields=[], $min_str_length=1, $max_words=10)
    {
        $db = $this->db;
        
        $select_sum = "(0";
        $search_where = "(0";

        $q = trim($q);
        
        if ($search_fields && strlen($q) >= $min_str_length) {
            $words = explode(" ", $q);

            foreach ($words as $word_idx => $word) {
                $word_idx_score = 100 * max(1, 10 - $word_idx); // au dela de 10 mots, on compte comme le 10e mot

                $w = $db->escape($word);
                $w2 = $db->escape("%" . $word . "%");

                $conditions_or = [];
                $select_sums = [0];
                foreach ($search_fields as $term_idx => $field) {
                    $conditions_or[] = $field . " like " . $w2;

                    $term_idx_score = 10 * max(1, 10 - $term_idx); // au dela de 10 fields, on compte comme le 10e field
                    $select_sums[] = "( if( locate(" . $w . ", ifnull(" . $field . ",'') ) > 0, 1, 0 ) * " . $word_idx_score . " * " . $term_idx_score . " * greatest( 100 - locate(" . $w . ", ifnull(" . $field . ", '')), 1) )";
                }

                $word_condition = "(" . implode(" or ", $conditions_or) . ")";
                $search_where .= " or " . $word_condition;


                //$select_sum .= " + if(" . $word_condition . ", " . $word_idx_score . ", 0)";
                $select_sum .= " + (" . implode(" + ", $select_sums) . ")";

                if (! empty($max_words) && $word_idx >= $max_words) {
                    break; // one ne prend plus en compte les mots au dela de $max_words
                }
            }
        }
        $search_where .= ")";
        $select_sum .= ")";
        //pre($select_sum, 1);

        return [
            'select' => $select_sum,
            'where' => $search_where,
        ];
    }

}

