<?

/**
 * Part of Crayd Framework
 * User management
 *
 * Tables:
 * - user (needed)
 *   Description: Stores userdata.
 *   Needed columns: id, username, email, password, created
 *
 * - user_data (optional, use data with loadData())
 *   Description: Stores additional user info
 *   Needed columns: id, user_id, created, updated, title, content
 *
 * - user_log (optional, use with loadLog())
 *   Description: Stores user's actions to tables, such as vote etc
 *   Needed columns: id, user_id, table_id, table_name, created, updated, action, content
 * 
 * TODO: Revamp logging system, as well as data system, might use EAV instead
 */
class Crayd_Auth {

    // handles current user's data
    public $data;
    // DB object
    private $db;
    private $config;

    /**
     * Constructor
     * @param database $db
     */
    public function __construct() {
        $this->db = Crayd_Database::factory();
        $this->config = Crayd_Registry::get('config')->auth;

        // this constructor should check current user's status
        // first step, check cookie
        if (empty($_COOKIE['vee_var' . $this->config->uniqueID])) {
            // cookie is empty, set user as guest
            $this->data->user->is_guest = 1;
            $this->data->user->id = 0;
        } else {
            // got cookie.. now parse it
            $data = unserialize(stripslashes($_COOKIE['vee_var' . $this->config->uniqueID]));
            // get the id
            $id = (int) $data['sessid'];
            // try to find the user
            $sql = "
                SELECT *
                FROM " . $this->config->table->member . "
                WHERE id = $id
                    ";
            $result = $this->db->fetchRow($sql);
            if ($result) {
                // user id exist, now check userdata matches
                if ($data['sessname'] == md5($result['username'])
                        && $data['sessdate'] == md5($result['created'])
                ) {
                    // user matches
                    $this->data->user->is_logged = 1;
                    // load user info
                    foreach ($result as $key => $value) {
                        $this->data->user->{$key} = $value;
                    }
                } else {
                    // no user.. kill it with fire
                    $this->data->user->is_guest = 1;
                    $this->logout();
                }
            } else {
                // whoops someone modified the cookie
                $this->data->user->is_guest = 1;
                $this->logout();
            }
        }
    }

    /**
     * Getter
     * @param string $var
     * @return mixed
     */
    public function __get($var) {
        return $this->data->{$var};
    }

    /**
     * Logs user into system
     * @param string $username
     * @param string $password
     */
    public function login($username, $password, $expire = null) {
        // if empty
        if ($username == null || $password == null) {
            return -1;
        }
        if ($expire == null) {
            $expire = 60 * 24 * 30;
        }
        // validate username & password
        $login = $this->validatePassword($username, $password);
        if (is_array($login)) {
            // set cookie data
            $cookie['sessid'] = $login['id'];
            $cookie['sessname'] = md5($login['username']);
            $cookie['sessdate'] = md5($login['created']);
            $cookie = serialize($cookie);
            // expire
            $expire = time() + ( 60 * $expire );
            setcookie('vee_var' . $this->config->uniqueID, $cookie, $expire, '/', $this->config->domain);
            return true;
        } else {
            return -2;
        }
    }

    /**
     * Forces login, used for facebook login.
     * Needs login info from outer source 
     * id, username, and created
     */
    public function forceLogin($login) {
        // set cookie data
        $cookie['sessid'] = $login['id'];
        $cookie['sessname'] = md5($login['username']);
        $cookie['sessdate'] = md5($login['created']);
        $cookie = serialize($cookie);
        // expire
        $expire = time() + ( 60 * $expire );
        setcookie('vee_var' . $this->config->uniqueID, $cookie, $expire, '/', $this->config->domain);
    }

    /**
     * Validates user password
     * @param string $username
     * @param string $password
     */
    public function validatePassword($username, $password) {
        $username = $this->db->clean($username);
        $password = md5($password);

        $sql = "
            SELECT *
            FROM " . $this->config->table->member . "
            WHERE (username = '$username' OR email = '$username')
            AND password = '$password'
        ";
        $result = $this->db->fetchAll($sql);
        if (count($result) > 0) {
            return $result[0];
        } else {
            return 0;
        }
    }

    /**
     * Logs user out of system
     */
    public function logout() {
        setcookie('vee_var' . $this->config->uniqueID, null, time() - 3600, '/');
        unset($_COOKIE['vee_var' . $this->config->uniqueID]);
    }

    /**
     * Loads current user's data to $this->data->user
     */
    public function loadData() {
        // user must logged to use this
        if ($this->data->user->is_guest) {
            return false;
        }
        // check table
        if ($this->config->table->data == null) {
            return false;
        }
        // validate id
        $id = (int) $this->data->user->id;

        // start the sql
        $sql = "
            SELECT *
            FROM $this->config->table->data
            WHERE user_id = $id
                ";
        $result = $this->db->fetchAll($sql);
        if (count($result) > 0) {
            foreach ($result as $value) {
                $this->data->user->{$value['title']} = $value['content'];
            }
        }
    }

    /**
     * Loads current user's log to $this->data['log']
     */
    public function loadLog() {
        // user must logged to use this
        if ($this->data->user->is_guest) {
            return false;
        }
        // check table
        if ($this->config->table->log == null) {
            return false;
        }

        $id = (int) $this->data->user->id;

        $sql = "
            SELECT *
            FROM $this->config->table->log
            WHERE user_id = $id
                ";
        $hasil = $this->db->fetchAll($sql);

        if (count($hasil) > 0) {
            foreach ($hasil as $value) {
                $this->log[$value['table_name']][$value['table_id']][$value['action']] = $value['content'];
            }
        }
    }

    /**
     * Logs user action
     * @param <type> $tablename
     * @param <type> $tableid
     * @param <type> $action
     * @param <type> $value
     */
    public function log($tablename, $tableid, $action, $value = 1) {
        $id = (int) $this->data->user->id;
        $insert['user_id'] = $id;
        $insert['table_id'] = $tableid;
        $insert['table_name'] = $tablename;
        $insert['created'] = $this->db->mysqlDate();
        $insert['action'] = $action;
        $insert['description'] = $value;
        $this->db->insert($this->config->table->log, $insert);
    }

    /**
     *
     * @param string $field
     * @param string $value
     * @param int $id
     * @return bool
     */
    public function updateData($field, $value, $id = null) {
        // validate
        if ($field == null || $value == null) {
            return false;
        }
        if ($id == null) {
            $id = (int) $this->data->user->id;
        }
        // check field existence
        $sql = "
            SELECT *
            FROM $this->config->table->data
            WHERE title = '" . $this->db->clean($field) . "'
            AND user_id = $id
                ";
        $result = $this->db->fetchAll($sql);
        // validate
        if (count($result) == 1) {
            // update, field is already exist
            $update['content'] = $this->db->clean($value);
            $update['updated'] = $this->db->mysqlDate();
            $this->db->update($this->config->table->data, $update, " title = '" . $this->db->clean($field) . "' AND user_id = $id");
            unset($update);
        } else {
            // doesnt have it.. create one
            $insert['content'] = $this->db->clean($value);
            $insert['title'] = $this->db->clean($field);
            $insert['created'] = mysqlDate();
            $insert['updated'] = mysqlDate();
            $insert['user_id'] = $id;
            $this->db->insert($this->config->table->data, $insert);
            unset($insert);
        }
        $this->data->user->{$field} = $value;
        return true;
    }

    /**
     * Gets a single member data
     * @param int $id
     * @return array
     */
    public function getData($id = null) {
        if ($id == null) {
            $id = (int) $this->data->user->id;
        }
        $sql = "
                SELECT *
                FROM $this->config->table->data
                WHERE user_id = $id
                ";
        $result = $this->db->fetchAll($sql);

        if (count($result) > 0) {
            foreach ($result as $value) {
                $data[$value['title']] = $value['content'];
            }
        }

        return $data;
    }

    public function deleteData($variable, $id = null) {
        if ($id == null) {
            $id = (int) $this->data->user->id;
        }

        $this->db->delete($this->config->table->data, 'user_id = ' . $id . ' AND title = \'' . $variable . '\' LIMIT 1');
    }

    /**
     * Get a few member's data
     */
    public function getMembers($array) {
        $ids = implode(',', (array) $array);
        $sql = "
            SELECT *
            FROM $this->config->table->user
            WHERE id IN ($ids)
                ";
        $result = $this->db->fetchAll($sql);

        // do need to check others?
        $sql = "
            SELECT *
            FROM $this->config->table->data
            WHERE user_id IN ($ids)
                ";
        $result2 = $this->db->fetchAll($sql);

        if (count($result) > 0) {
            foreach ($result as $value) {
                $data[$value['id']] = $value;
            }
        }
        if (count($result2) > 0) {
            foreach ($result as $value) {
                $data[$value['user_id']][$value['title']] = $value['content'];
            }
        }
        return $data;
    }

    /**
     * Searches member having $value as content of $variable
     * @param string $variable
     * @param string $value
     * @return array
     */
    public function fetchUserFromData($variable, $value) {
        $sql = "
            SELECT *
            FROM $this->config->user->data
            WHERE title = '" . $this->db->clean($variable) . "'
            AND content = '" . $this->db->clean($value) . "'
        ";
        $hasil = $this->db->fetchAll($sql);

        if (count($hasil) > 0) {
            foreach ($hasil as $value) {
                $return[] = $value['user_id'];
            }
            return $return;
        }
        return array();
    }

    /**
     * Searches userdata from result array
     * @param string $key
     * @param array $result
     * @return array
     */
    public function fetchUserFromResult($key, $result) {
        // validate
        if (!is_array($result)) {
            return false;
        }

        foreach ($result as $value) {
            $ids[] = $value[$key];
        }

        // get the data
        if (count($ids) > 0) {
            $data = $this->getMembers($ids);
        } else {
            $data = array();
        }
        return $data;
    }

}
