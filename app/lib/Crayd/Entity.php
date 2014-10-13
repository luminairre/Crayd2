<?

class Crayd_Entity {

    /**
     *
     * @var Crayd_Database
     */
    var $db;
    protected $_data = array();
    protected $_update = array();
    protected $_table = '';

    public function __construct($table, $data) {
        $this->db = Crayd_Database::factory();
        
        if (strpos($table, '`') === false) {
            $table = "`{$table}`";
        }

        $this->_data = $data;
        $this->_table = $table;

        $this->_initEntity($data);
    }

    public function __set($name, $value) {
        $this->_data[$name] = $value;
        $this->_update[$name] = $value;
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
        $this->_data = array();
        $this->_update = array();
    }

    public function save() {
        if (count($this->_update) > 0) {
            $this->_update($this->_update);
        }
    }

    public function _load($data) {
        $data = (int) $data;
        $sql = "SELECT * FROM {$this->_table} WHERE id = {$data}";
        $this->_data = $this->db->fetchRow($sql);
    }

    public function _new($data) {
        $id = $this->db->insert($this->_table, $data);
        $this->_load($id);
    }

    public function _update($array) {
        if ($this->_table == '') {
            return false;
        }

        if ((int) $this->id == 0) {
            return false;
        }

        $this->db->update($this->_table, $array, "id = {$this->id}");
    }

    public function delete() {
        $this->db->delete($this->_table, "id = {$this->id}");
    }

}
