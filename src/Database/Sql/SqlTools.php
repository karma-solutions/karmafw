<?php

namespace KarmaFW\Database\Sql;


// TODO: a transformer en une classe trait de SqlDb

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
                'charset' => '',
            ];
        }


        $parts0 = explode('?', $dsn);
        $dsn_url = $parts0[0];
        $querystring = isset($parts0[1]) ? $parts0[1] : '';
        parse_str($querystring, $GET);
        $charset = isset($GET['charset']) ? $GET['charset'] : '';


        $parts1 = explode(':', $dsn_url);
        $driver = $parts1[0];

        $parts2 = explode('/', $dsn_url);
        $user_passwd_host_port = $parts2[2];
        $db = ! empty($parts2[3]) ? $parts2[3] : null;

        $parts3 = explode('@', $user_passwd_host_port);
        if (count($parts3) > 1) {
            // USER (AND OPTIONNALY PASSWORD) IS DEFINED
            // mysql://user@host/database
            // mysql://user@host:port/database
            // mysql://user:password@host/database
            // mysql://user:password@host:port/database
            $user_password = $parts3[0];
            $host_port = $parts3[1];
        } else {
            // USER AND PASSWORD ARE NOT DEFINED
            // mysql://host/database
            // mysql://host:port/database
            $user_password = '';
            $host_port = $parts3[0];
        }

        $parts4 = explode(':', $host_port);
        $host = $parts4[0];
        if (count($parts4) > 1) {
            // HOST AND PORT ARE DEFINED
            // mysql://user@host:port/database
            // mysql://user:password@host:port/database
            $port = $parts4[1];
        } else {
            // HOST IS DEFINED. PORT IS NOT DEFINED
            // mysql://user@host/database
            // mysql://user:password@host/database
            $port = 3306;
        }

        $parts5 = explode(':', $user_password);
        $user = $parts5[0];
        if (count($parts5) > 1) {
            // USER AND PASSWORD ARE DEFINED
            // mysql://user:password@host/database
            // mysql://user:password@host:port/database
            $passwd = $parts5[1];
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
            'charset' => $charset,
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
                if (is_numeric($key)) {
                    $where_sql[] = (string) $value;
                
                } else if (is_null($value)) {
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
            if (is_numeric($key)) {
                $values_sql[] = $value;

            } else if (is_null($value)) {
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


    public function buildSqlWhereSearch($q='', $search_fields=[], $min_str_length=1, $max_words=10, $all_words_required=false)
    {
        $db = $this->db;
        
        $select_sum = "(0";

        if ($all_words_required) {
            $search_where = "(1";
        } else {
            $search_where = "(0";
        }

        $q = trim($q);

        $pow_pos_word = 0.5;             // correspond à l'index du mot parmi tous les mots de la recherche
        $pow_pos_field = 0.5;            // correspond à l'index du champ (SQL) de recherche parmi tous les champs de recherche
        $pow_pos_match = 0.5;            // correspond à la position strpos de la chaine recherchée trouvée dans un des champs de recherche
        $pow_length_field_value = 0.3;   // correspond à la taille (du texte) de la valeur du champ dans lequel on a trouvé le match

        $coef_pos_word = 1;
        $coef_pos_field = 1;
        $coef_pos_match = 1;
        $coef_length_field_value = 1;
        
        if ($search_fields && strlen($q) >= $min_str_length) {
            $words = explode(" ", $q);

            foreach ($words as $word_idx => $word) {
                $word_idx_score = pow(max(1, 10 - $word_idx) * $coef_pos_word, $pow_pos_word); // au dela de 10 mots, on compte comme le 10e mot

                $w = $db->escape($word);
                $w2 = $db->escape("%" . $word . "%");

                $conditions_or = [];
                $select_sums = [0];
                foreach ($search_fields as $field_idx => $field) {
                    $conditions_or[] = $field . " like " . $w2;

                    $field_idx_score = pow(max(1, 10 - $field_idx) * $coef_pos_field, $pow_pos_field); // au dela de 10 fields, on compte comme le 10e field
                    $select_sums[] = "( if( locate(" . $w . ", ifnull(" . $field . ",'') ) > 0, 1, 0 ) * pow(1 / length(" . $field . ") * " . $coef_length_field_value . ", " . floatval($pow_length_field_value) . ") * " . $word_idx_score . " * " . $field_idx_score . " * pow(greatest( 100 - locate(" . $w . ", ifnull(" . $field . ", '')), 1) * " . floatval($coef_pos_match) . ", " . floatval($pow_pos_match) . ") )";
                    //$select_sums[] = "( if( locate(" . $w . ", ifnull(" . $field . ",'') ) > 0, 1, 0 ) * (1 / length(" . $field . ")) * " . $word_idx_score . " * " . $field_idx_score . "  )";
                }

                $word_condition = "(" . implode(" or ", $conditions_or) . ")";

                if ($all_words_required) {
                    // TODO
                    $search_where .= " and (" . $word_condition . ")";

                } else {
                    $search_where .= " or " . $word_condition;

                }


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



    public function buildSqlWhereSearch_beta($q='', $search_fields=[], $min_str_length=1, $max_words=10, $all_words_required=false, $use_soundex=false, $use_levenshtein=false)
    {
        $db = $this->db;
        
        $q = str_replace(['.', ',', '-', '_', ';', ':', '(', ')', '[', ']'], ' ', $q);
        //$q_one = str_replace(' ', '', $q);
        $q_len = strlen($q);

        /*
        $search_fields = [
            'name',
        ];
        */
        $fields_count = count($search_fields);


        $words = explode(" ", $q);
        $words_count = count($words);


        if ($all_words_required) {
            $search_where = "(1";
        } else {
            $search_where = "(0";
        }

        $words_scores = [];
        $w_soundexes = [];

        // pour chaque champ sql dans lequel on recherche...
        foreach ($words as $word_idx => $word) {
            //$word_pos = $word_idx + 1;
            $word_len = strlen($word);

            $w = $db->escape($word);
            $w_like = $db->escape("%" . $word . "%");
            $w_regex = $db->escape('\b' . preg_quote($word) . '\b');

            // score de position parmi les mots de recherche
            //$word_idx_score = 1 / $word_pos;
            $word_idx_score = ($words_count - $word_idx) / $words_count;

            // score de longueur du mot par rapport à la longueur total de l'expression de recherche
            $word_search_len_score = $word_len / $q_len;


            $fields_scores = [];
            $word_conditions_or = [];

            // pour chaque mot de l'expression recherchée...
            foreach ($search_fields as $field_idx => $field) {
                //$field_pos = $field_idx + 1;

                // score de position du champ sql parmis tous les champs où on va rechercher
                //$field_idx_score = 1 / $field_pos;
                $field_idx_score = ($fields_count - $field_idx) / $fields_count;
               
                // score de longueur du mot (de recherche) par rapport a la longueur du champ sql
                $word_field_len_score = 1;
                $weight = "0.9 + 0.1 *";
                $word_field_len_score = "( $weight (least($word_len / length($field), length($field) / $word_len)) )";
                 // TODO: algo et poids à revoir

                // score de position du mot dans la valeur du champ sql
                //$locate_max = "(greatest(1, length($field) - $word_len) )";
                //$word_match_pos_score = "( ($locate_max - locate($w, $field)) / $locate_max)";
                $word_match_pos_score = 1;
                $weight = "0.5 + 0.5 *";  // TODO: poids à revoir
                $word_match_pos_score = "( $weight (1 + length($field) - locate($w, $field)) / length($field) )";

                // score distance levenshtein
                $word_levenshtein_score = 1;
                if ($use_levenshtein) {
                    //$word_levenshtein_score = "( if(length($field) < 32, levenshtein_ratio($field, $w) / 100, 0.01) )";
                }

                // score soundex
                $word_soundex_score = 1;
                if ($use_soundex) {
                    //$word_soundex_score = "( greatest( if(length($field) < 32, least(mid(soundex(" . $field . "), 2) / mid(soundex(" . $w_like . "), 2), mid(soundex(" . $w_like . "), 2) / mid(soundex(" . $field . "), 2)), 0), 0.01 ) )";
                }

                // matching
                $extra_rules = "";
                if ($use_soundex) {
                    $w_soundex = "soundex($w)";
                    if (isset($w_soundexes[$word])) {
                        $w_soundex = $w_soundexes[$word];
                    }
                    $extra_rules .= " when length($field) < 32 and soundex($field) = $w_soundex then 0.1
                                        when length($field) < 32 and (abs(mid(soundex($field), 2) - mid($w_soundex, 2)) <= 5 and left(soundex($field),1) = left($w_soundex,1) ) then 0.05
                                        ";
                }
                if ($use_levenshtein) {
                    // very slow...
                    //$extra_rules .= " when length($field) < 32 and levenshtein_ratio($field, $w) > 80 then 0.05 when length($field) < 32 and levenshtein_ratio($field, $w) > 50 then 0.01 ";
                }
                $word_matching_score = "(case when $field = '' then 0
                                         when $field = $w then 1
                                         when $field regexp $w_regex then 0.5
                                         when $field like $w_like then 0.1
                                         $extra_rules
                                         else 0
                                    end )";
                //$word_conditions_or[] = $word_matching_score;

                //$word_score = "( if(length($field)=0, 0, $field_idx_score * $word_idx_score * $word_search_len_score * $word_field_len_score * $word_soundex_score * $word_levenshtein_score * $word_match_pos_score * $word_matching_score) )";
                $word_score = "( $word_matching_score * ifnull($field_idx_score + $word_idx_score + $word_search_len_score + $word_field_len_score + $word_soundex_score + $word_levenshtein_score + $word_match_pos_score, 0) / 7 )";
                $word_conditions_or[] = $word_score;

                $fields_scores[] = $word_score;
            }
            
            $word_condition = "(" . implode(" or ", $word_conditions_or) . ")";


            if ($all_words_required) {
                // TODO
                $search_where .= " and (" . $word_condition . ")";

            } else {
                $search_where .= " or " . $word_condition;

            }

            //$words_scores[] = "(" . implode(" + ", $fields_scores) . ")";
            $words_scores[] = "( (" . implode(" + ", $fields_scores) . ") / $fields_count )";
            //$words_scores[] = "( greatest(" . implode(", ", $fields_scores) . ") )";

        }

        $search_where .= ")";
        //$search_score = "(" . implode(' + ', $words_scores) . ")";
        $search_score = "( (" . implode(' + ', $words_scores) . ") / $words_count )";

        
        if (! empty($_GET['debug_search'])) {
            echo "SCORE:" . PHP_EOL;
            $nb_scores = count($words_scores);
            print_r($search_score);

            echo PHP_EOL;

            echo "WHERE:" . PHP_EOL;
            print_r($search_where);

            echo PHP_EOL;
            exit;
        }


        return [
            'select' => $search_score,
            'where' => $search_where,
        ];

    }


}

