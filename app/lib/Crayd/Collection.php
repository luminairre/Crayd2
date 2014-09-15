<?

class Crayd_Collection {

    var $db;
    protected $data = null;
    protected $table = '';
    protected $where = array();
    protected $join = array();
    protected $columns = "*";
    protected $order = "";
    protected $limit = "";
    protected $group = "";

    public function __construct($table) {
        $this->db = Crayd_Database::factory();

        if (strpos($table, '`') === false) {
            $table = "`{$table}`";
        }
        
        $this->table = $table;
    }

    public function load($idAsKey = false, $columnKey = null, $multiple = false) {
        if (count($this->where) > 0) {
            $where = " WHERE (" . implode(") AND (", $this->where) . ") ";
        }

        if (count($this->join) > 0) {
            $join = implode(" ", $this->join);
        }

        $sql = "SELECT {$this->columns} FROM {$this->table} {$join} {$where} {$this->order} {$this->group} {$this->limit}";

        $this->data = $this->db->fetchAll($sql, $idAsKey, $columnKey, $multiple);

        return $this->data;
    }

    public function join($join) {
        $this->join[] = $join;
    }

    public function order($order) {
        $this->order = " ORDER BY {$order} ";

        return $this;
    }

    public function table($table) {
        $this->table = $table;

        return $this;
    }

    public function group($group) {
        $this->group = "GROUP BY {$group}";

        return $this;
    }

    public function limit($limit) {
        $this->limit = " LIMIT $limit ";

        return $this;
    }

    public function where($condition) {
        if (is_array($condition)) {
            foreach ($condition as $val) {
                if ($val != '') {
                    $this->where[] = $val;
                }
            }
        } else {
            if ($condition != '') {
                $this->where[] = $condition;
            }
        }
        return $this;
    }

    public function reset() {
        $this->columns = "*";
        $this->data = null;
        $this->where = array();
        $this->order = "";
        $this->join = array();
    }

    public function columns($columns) {
        $this->columns = $columns;

        return $this;
    }

    public function delete() {
        if (count($this->where) > 0) {
            $where = " (" . implode(") AND (", $this->where) . ") ";
        }

        $this->db->delete($this->table, $where);
    }

}
