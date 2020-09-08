<?php

class DbHandler {

    private $conn;

    function __construct() {
        require_once 'dbConnect.php';
        // opening db connection
        $db = new dbConnect();
        $this->conn = $db->connect();
    }
    /**
     * Fetching single record
     */
    public function runQuery($query) {
        $r = $this->conn->query($query) or die($this->conn->error.__LINE__);
        return $result = $this->conn->affected_rows;  
    }

    /**
     * Fetching single record
     */
    public function getOneRecord($query) {
        $r = $this->conn->query($query.' LIMIT 1') or die($this->conn->error.__LINE__);
        return $result = $r->fetch_assoc();    
    }

    /**
     * Fetching multiple records
     */
    public function getRecordset($query) {
        $r = $this->conn->query($query) or die($this->conn->error.__LINE__);

        if($r->num_rows > 0){
            $result = array();
            while($row = $r->fetch_assoc()){
                $result[] = $row;
            }
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Fetching number of records matching a condition
     */
    public function getCount($table, $where) {
        $w = "";
        foreach ($where as $key => $value) {
            $w .= " AND " .$key. " = '".$value."'";
        }
        $r = $this->conn->query("SELECT COUNT(*) AS dcount FROM $table WHERE 1=1 $w ");

        if ($r) {
            $result = $r->fetch_assoc();
            return $result ? $result['dcount'] : 0;
        } else {
            return $this->conn->error;
        }
    }

    /**
     * Delete record(s)
     */
    public function deleteFromTable($table, $idcol, $value) {
        $r = $this->conn->query("DELETE FROM $table WHERE $idcol = '$value'") or die($this->conn->error.__LINE__);
        return $result = $this->conn->affected_rows;    
    }
    
    public function deleteAllFromTable($table) {
        $r = $this->conn->query("DELETE FROM $table") or die($this->conn->error.__LINE__);
        return $result = $this->conn->affected_rows;    
    }

    public function deleteFromTableWhere($table, $idcols) {
        $col_name = "";
        foreach ($idcols as $key => $value) {
            $col_name .= "`".$key."` = '".$value ."' AND";
        }
        $z = trim($col_name,'AND');
        $r = $this->conn->query("DELETE FROM `$table` WHERE ($z) ") or die($this->conn->error.__LINE__);
        return $result = $this->conn->affected_rows;    
    }

    /**
     * Creating new record using array (instead of object)
     */
    public function insertToTable($supplied_values, $column_names, $table_name) {

        $columns = '';
        $values = '';
        //column names
        foreach ($column_names as $col) {
            $columns .= "`".$col . "`,";
        }
        //values
        foreach ($supplied_values as $val) {
            $values .= ($val == '') ? "NULL," : "'".$val."',";
        }

        $query = "INSERT INTO `".$table_name."` (".trim($columns,',').") VALUES(".trim($values,',').")";
        // die($query);
        $r = $this->conn->query($query) or die($this->conn->error.__LINE__);

        if ($r) {
            return $this->conn->insert_id;
            } else {
            return NULL;
        }
    }
    /**
     * Creating new record using columns array (key-value pairs) (like edit)
     */
    public function insertColumnsToTable($table, $columnsArray) {

        $columns = '';
        $values = '';
        //loop thru columns array
        foreach ($columnsArray as $column => $value) {
            $columns .= "`".$column . "`,";
            if($value === 0 || $value === 0.00) {
                $values .= "0,";
            } else {
                if($value == '') {
                    $values .= "NULL,";
                } else {
                    $values .= "'".$value."',";
                }
            }
        }

        $query = "INSERT INTO `".$table."` (".trim($columns,',').") VALUES(".trim($values,',').")";
        // die($query);
        $r = $this->conn->query($query) or die($this->conn->error.__LINE__);

        if ($r) {
            return $this->conn->insert_id;
            } else {
            return NULL;
        }
    }
    /*function updates a set of columns in a table using a where condition*/
    public function updateInTable($table, $columnsArray, $where){ 
        $a = array();
        $w = "";
        $c = "";
        //where clause
        foreach ($where as $key => $value) {
            $w .= " AND " .$key. " = '".$value."'";
        }
        //set columns
        foreach ($columnsArray as $key => $value) {
            // echo "$key -> $value becomes ";
            $value = ($value!='') ? "'".$value."'" : "NULL" ; //. ($value == '') ? "NULL" : "'" . $value."', ";
            $c .= " $key = $value,";
            // $c .= $cline;
        }
        $c = rtrim($c,", ");
        // die($c);

        //run update query
        $query = "UPDATE `$table` SET $c WHERE 1=1 ".$w;
        //return ($query);
        $r = $this->conn->query($query) or die($this->conn->error.__LINE__);

        if ($r) {
            //u can try to get affected rows, not so necessary
            $affected_rows = $this->conn->affected_rows;
            return $affected_rows;
            //return "OK";
        } else {
            return $this->conn->error;
        }

    }

    public function updateColumnsInTable($table, $available_columns, $values, $where){ 
        $a = array();
        $w = "";
        $c = "";
        //where clause
        foreach ($where as $key => $value) {
            $w .= " AND " .$key. " = '".$value."'";
        }
        //set columns
        foreach ($available_columns as $i => $col) {
            $c .= $col. " = '".$values[$i]."', ";
        }
        $c = rtrim($c,", ");

        //run update query
        $query = "UPDATE `$table` SET $c WHERE 1=1 ".$w;
        //return ($query);
        $r = $this->conn->query($query); //or die($this->conn->error.__LINE__);

        if ($r) {
            //u can try to get affected rows, not so necessary
            $affected_rows = $this->conn->affected_rows;
            return $affected_rows;
            //return "OK";
            } else {
            return $this->conn->error;
        }

    }


    /*function updates a column to null using a where condition*/
    public function updateToNull($table, $column, $where){ 
        $a = array();
        $w = "";
        //where clause
        foreach ($where as $key => $value) {
            $w .= " AND " .$key. " = '".$value."'";
        }

        //run update query
        $query = "UPDATE `$table` SET $column = NULL WHERE 1=1 ".$w;
        //return ($query);
        $r = $this->conn->query($query); //or die($this->conn->error.__LINE__);

        if ($r) {
            //u can try to get affected rows, not so necessary
            $affected_rows = $this->conn->affected_rows;
            return $affected_rows;
            //return "OK";
            } else {
            return $this->conn->error;
        }

    }

    // function cleans up input for db
    public function purify($raw_value) {
        return isset($raw_value) ? $this->conn->real_escape_string($raw_value) : '';
    }

    // function cleans up an object atrribute for db IF it exists for db
    public function purifyIfSet($obj, $raw_value) {
        return isset($obj->$raw_value) ? $this->conn->real_escape_string($obj->$raw_value) : '';
    }

    // function cleans up input for db
    public function purifyObj($obj) {
        foreach ($obj as $key => $value) {
            $obj->$key = $this->purify($value);
        }
        return $obj;
    }

    public function getAvailableColumns ($object, $required_fields, $optional_fields) {
        $columns = [];

        $columns = $required_fields;

        foreach ($optional_fields as $field) {
            if(isset($object->$field)) {
                $columns[] = $field;
            }
        }

        return $columns;
    }

    public function getInsertValuesFromObject ($object, $available_columns) {
        $values = [];

        foreach ($available_columns as $col) {
            $values[] = $this->purify($object->$col);
        }

        return $values;
    }

}

