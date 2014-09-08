<?

/**
 * Crayd Framework Database class 
 * 
 * 
 * @author vee@gate00.net
 */
class Crayd_Database {

    public $config;
    private static $instance;
    var $lastId;
    var $result;
    var $conn;

    /**
     * Instance getter
     * @return Crayd_Database
     */
    public static function factory() {
        if (self::$instance === NULL) {
            self::$instance = new Crayd_Database();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    public function __construct($cfg = null) {
        // Delegate config
        if ($cfg != null) {
            $this->config = $cfg;
        } else {
            $this->config = Crayd_Registry::get('config')->db;
        }
        // Generate connection
        if ($this->conn == null) {
            $this->conn = new mysqli($this->config->host, $this->config->username, $this->config->password, $this->config->database);
            $this->conn->query('SET NAMES utf8');
        }
    }

    /**
     * Fetch all from SQL Statement.
     * ID As Key returns row ID as array keys
     * @param string $sql
     * @param bool $idAsKey
     * @return array
     */
    public function fetchAll($sql, $idAsKey = false, $columnKey = null, $multiple = false) {
        //$sql = $this->clean($sql);
        // queries
        $query = $this->_query($sql);
        if ($query) {
            $results = array();
            while ($row = $query->fetch_assoc()) {
                if ($idAsKey) {
                    if ($columnKey != null && $row[$columnKey] != null) {
                        $key = $row[$columnKey];
                    } else {
                        $key = $row['id'];
                    }
                    if ($multiple) {
                        $results[$key][] = $row;
                    } else {
                        $results[$key] = $row;
                    }
                } else {
                    $results[] = $row;
                }
            }
            // put results to var for more processing
            $this->result = $results;
            $query->free_result();
            return $results;
        } else {
            return false;
        }
    }

    /**
     * Fetch single row
     * @param string $sql
     */
    public function fetchRow($sql) {
        //$sql = $this->clean($sql);
        $query = $this->_query($sql);
        $row = $query->fetch_assoc();
        $query->free_result();
        return $row;
    }

    /**
     * Automatically parses data according to selected table (auto select)
     * @param string $table
     * @param array $array 
     */
    public function parsedInsert($table, $array, $allowed = array()) {
        if (count($allowed) > 0) {
            foreach ($allowed as $value) {
                if (isset($array[$value])) {
                    $data[$value] = $array[$value];
                }
            }
        } else {
            $columns = $this->fetchAll("SHOW COLUMNS FROM $table");
            foreach ($columns as $value) {
                if (isset($array[$value['Field']])) {
                    $data[$value['Field']] = $array[$value['Field']];
                }
            }
        }
        $data = $this->clean($data);
        return $this->insert($table, $data);
    }

    /**
     * parses data before update (select data according to fields)
     * @param string $table
     * @param array $array
     * @param string $where 
     */
    public function parsedUpdate($table, $array, $where = '', $allowed = array()) {
        if (count($allowed) > 0) {
            foreach ($allowed as $value) {
                if (isset($array[$value])) {
                    $data[$value] = $array[$value];
                }
            }
        } else {
            $columns = $this->fetchAll("SHOW COLUMNS FROM $table");
            foreach ($columns as $value) {
                if (isset($array[$value['Field']])) {
                    $data[$value['Field']] = $array[$value['Field']];
                }
            }
        }

        //protect
        $data = $this->clean($data);
        $this->update($table, $data, $where);
    }

    /**
     * Inserts into table
     * @param string $table
     * @param array $data
     * @param array $allowed
     */
    public function insert($table, $data, $allowed = array()) {
        if (count($data) > 0) {
            $table = $this->clean($table);
            // get column names
            $columns = $this->getColumns($table);
            if (count($allowed) > 0 && is_array($allowed)) {
                $validateAllow = true;
                $allowed = array_flip($allowed);
            }

            foreach ($columns as $key => $value) {
                if (isset($data[$key])) {
                    if ($validateAllow) {
                        if (isset($allowed[$key])) {
                            $set[] = $key;
                            $values[] = $data[$key];
                            switch ($value) {
                                case 'int':
                                    $types .= 'i';
                                    break;
                                case 'float':
                                case 'double':
                                    $types .= 'd';
                                    break;
                                default:
                                    $types .= 's';
                            }
                            $count[] = '?';
                        }
                    } else {
                        $set[] = $key;
                        $values[] = $data[$key];
                        switch ($value) {
                            case 'int':
                                $types .= 'i';
                                break;
                            case 'float':
                            case 'double':
                                $types .= 'd';
                                break;
                            default:
                                $types .= 's';
                        }
                        $count[] = '?';
                    }
                }
            }

            $sql = "INSERT INTO {$table} (`" . implode('`,`', $set) . "`) VALUES (" . implode(',', $count) . ")";

            $stmt = $this->conn->prepare($sql);
            if ($stmt) {
                $param[] = $stmt;
                $param[] = $types;

                foreach ($values as $key => $value) {
                    $param[] = &$values[$key];
                }

                call_user_func_array('mysqli_stmt_bind_param', $param);

                $stmt->execute();
                $stmt->close();
                $id = $this->conn->insert_id;
                return $id;
            } else {
                $this->error();
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Update table
     * @param string $table
     * @param array $data
     * @param array $allowed
     */
    public function update($table, $data, $where, $allowed = array()) {
        if (count($data) > 0) {
            $table = $this->clean($table);
            // get column names
            $columns = $this->getColumns($table);
            if (count($allowed) > 0 && is_array($allowed)) {
                $validateAllow = true;
                $allowed = array_flip($allowed);
            }

            foreach ($columns as $key => $value) {
                if (isset($data[$key])) {
                    if ($validateAllow) {
                        if (isset($allowed[$key])) {
                            $set[] = "`$key` = ?";
                            $values[] = $data[$key];
                            switch ($value) {
                                case 'int':
                                    $types .= 'i';
                                    break;
                                case 'float':
                                case 'double':
                                    $types .= 'd';
                                    break;
                                default:
                                    $types .= 's';
                            }
                            $count[] = '?';
                        }
                    } else {
                        $set[] = "`$key` = ?";
                        $values[] = $data[$key];
                        switch ($value) {
                            case 'int':
                                $types .= 'i';
                                break;
                            case 'float':
                            case 'double':
                                $types .= 'd';
                                break;
                            default:
                                $types .= 's';
                        }
                    }
                }
            }
            if (count($set) > 0 && is_array($set)) {
                $sql = "UPDATE {$table} SET " . implode(',', $set);
                if ($where != '')
                    $sql .= " WHERE $where";

                $stmt = $this->conn->prepare($sql);
                if ($stmt) {
                    $param[] = $stmt;
                    $param[] = $types;

                    foreach ($values as $key => $value) {
                        $param[] = &$values[$key];
                    }

                    call_user_func_array('mysqli_stmt_bind_param', $param);

                    $stmt->execute();
                    $stmt->close();

                    return true;
                } else {
                    $this->error();
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

	/**
	 * Show available columns, cached
	 */
	public function getColumns($table) {
        if ($this->columns[$table] == null) {
            $columns = $this->fetchAll("SHOW COLUMNS FROM $table");
            foreach ($columns as $value) {
                $tmp = explode('(', $value['Type']);
                $column[$value['Field']] = $tmp[0];
            }
            $this->columns[$table] = $column;
            return $column;
        } else {
            return $this->columns[$table];
        }
    }
    
    /**
     * Delete from $table where $where
     * @param string $table
     * @param string $where
     */
    public function delete($table, $where) {
        if ($where != '')
            $where = " WHERE $where";
        $sql = "
            DELETE FROM " . $this->clean($table) . "  $where
            ";
        $this->_query($sql);
    }

    public function mysqlDate() {
        return date('Y-m-d H:i:s');
    }

    /**
     * Count rows
     * @param <type> $sql
     */
    public function count($sql) {
        $query = $this->conn->query($sql);
        return $query->num_rows;
    }

    /**
     * Free Queries
     * @param string $sql
     */
    public function query($sql) {
        return $this->_query($sql);
    }

    /**
     * Handler
     */
    private function _query($sql) {
        // Query it
        $result = $this->conn->query($sql);
        // validate
        if ($result) {
            // is valid then..
            return $result;
        } else if ($this->config->debug) {
            $this->error();
            return false;
        }
    }

    /**
     * Cleans text... D=
     * @param string $text
     * @return string
     */
    public function clean($text) {
        if (is_array($text)) {
            foreach ($text as $key => $value) {
                $return[$key] = $this->clean($value);
            }
            return $return;
        } else {
            return $this->conn->real_escape_string($text);
        }
    }

    public function error() {
        if ($this->config->debug) {
            $debug = debug_backtrace();
            $output = "
                " . mysqli_error($this->conn) . "<br>
                Caller:<br>
                File: " . $debug[0]['file'] . "<br>
                Line: " . $debug[0]['line'] . "<br>
                Function: " . $debug[0]['function'] . "<br>
                SQL: " . $debug[0]['args'][0] . "<br><hr>
            ";

            echo '<pre>';
            echo $output;
            echo '<pre>';
            exit;
        }
    }

    function __destruct() {
        $this->conn->close();
    }

}
