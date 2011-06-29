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
    var $route;
    var $config;

    /**
     * Routing function
     * @param Crayd_Route $route 
     */
    public function __construct($route) {
        // Pass needed configurations
        $this->config = Crayd_Registry::get('config')->view;
        $this->route = $route;
    }

    public function dispatchLayout() {
        
    }
    
    public function content() {
        
    }
    
    public function setLayout() {
        
    }
    
    public function setView() {
        
    }
    
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