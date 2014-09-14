<?

class Crayd_Entity {

    var $db;
    protected $_data = array();
    protected $update = array();
    protected $table = '';
    protected $status = array();

    public function __construct($table, $data) {
        $this->db = Crayd_Database::factory();

        if (strpos($table, '`') === false) {
            $table = "`{$table}`";
        }

        $this->_data = $data;
        $this->table = $table;

        $this->_initEntity($data);
    }

    public function __set($name, $value) {
        $this->_data[$name] = $value;
        $this->update[$name] = $value;
    }

    public function __get($name) {
        if (!empty($this->_data[$name])) {
            return $this->_data[$name];
        } else {
            return false;
        }
    }

    protected function _initEntity($data) {
        if (!is_array($data) && (int) $data > 0) {
            $this->_load($data);
        } else if (is_array($data) && (int) $data['id'] > 0) {
            $this->_data = $data;
        } else if (is_array($data) && (int) $data['id'] == 0) {
            $this->_new($data);
        } else {
            $temp['id'] = 0;
            $temp['data'] = $data;
            $this->_data = $temp;
        }
    }

    public function toArray() {
        return $this->_data;
    }

    public function reset() {
        $this->where = array();
        $this->_data = array();
        $this->update = array();
    }

    public function save() {
        if (count($this->update) > 0) {
            $this->_update($this->update);
        }
    }

    public function _load($data) {
        $data = (int) $data;
        $sql = "SELECT * FROM {$this->table} WHERE id = {$data}";
        $this->_data = $this->db->fetchRow($sql);
    }

    public function _new($data) {
        $id = $this->db->insert($this->table, $data);
        $this->_load($id);
    }

    public function _update($array) {
        if ($this->table == '') {
            return false;
        }

        if ((int) $this->id == 0) {
            return false;
        }

        $this->db->update($this->table, $array, "id = {$this->id}");
    }

    public function delete() {
        $this->db->delete($this->table, "id = {$this->id}");
    }

}
