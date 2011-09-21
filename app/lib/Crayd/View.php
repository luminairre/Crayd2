<?

/**
 * Crayd View Class
 * 
 * Handles views as well as encapsulate view transactions
 * 
 * @author vee@gate00.net
 */
class Crayd_View {

    var $data;
    var $_data;
    var $route;
    var $config;
    var $dir;
    var $layoutFile;
    var $viewFile;

    /**
     * Routing function
     * @param Crayd_Route $route 
     */
    public function __construct($route) {
        // Pass needed configurations
        $this->config = Crayd_Registry::get('config')->view;
        $this->route = $route;
        // Set directory
        if (!empty($this->route->data->section)) {
            $this->dir = Crayd_Registry::get('appDir') . DS . 'views' . DS . $this->route->data->section;
        } else {
            $this->dir = Crayd_Registry::get('appDir') . DS . 'views';
        }

        // Set filenames
        $this->layoutFile = 'layout';
        if (!empty($this->route->data->action)) {
            $this->viewFile = $this->route->data->controller . '_' . $this->route->data->action;
        } else {
            $this->viewFile = $this->route->data->controller;
        }
    }

    /**
     * Dispatches the layout...
     */
    public function dispatchLayout() {
        if (substr($this->layoutFile, -3) != 'php') {
            $filename = $this->layoutFile . '.php';
        } else {
            $filename = $this->layoutFile;
        }

        if (file_exists($this->dir . DS . $filename)) {
            include($this->dir . DS . $filename);
        }
    }

    /**
     * Content caller
     */
    public function content() {
        if (substr($this->viewFile, -3) != 'php') {
            $filename = $this->viewFile . '.php';
        } else {
            $filename = $this->viewFile;
        }
        if (file_exists($this->dir . DS . $filename)) {
            include($this->dir . DS . $filename);
        }
    }

    /**
     * Includer
     * @param string $file 
     */
    public function partial($file) {
         include($this->dir . DS . $file);
    }
    
    /**
     * Change layout file
     */
    public function setLayout($file) {
        $this->layoutFile = $file;
    }

    /**
     * Change view file
     */
    public function setView($file) {
        $this->viewFile = $file;
    }

    /**
     * Change include directory
     */
    public function setDir() {
        
    }

    /**
     * Magic method
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value) {
        $this->_data[$name] = $value;
    }

    /**
     * Magic method
     * @param string $var
     */
    public function __get($var) {
        return $this->_data[$var];
    }

}