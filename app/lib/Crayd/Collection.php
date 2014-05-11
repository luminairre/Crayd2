<?

class Crayd_Collection {

    var $db;

    protected $data = null;
    protected $table = '';
    protected $where = array();
    protected $columns = "*";
    protected $order = "";
    protected $limit = "";

    public function __construct() {
        $this->db = Crayd_Database::factory();
    }

    public function load($idAsKey = false, $columnKey = null, $multiple = false) {
        if (count($this->where) > 0) {
            $where = " WHERE (" . implode(") AND (", $this->where) . ") ";
        }

        $sql = "SELECT {$this->columns} FROM {$this->table} {$where} {$this->order} {$this->limit}";

        $this->data = $this->db->fetchAll($sql, $idAsKey, $columnKey, $multiple);

        return $this->data;
    }

    public function order($order) {
        $this->order = " ORDER BY {$order} ";

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
