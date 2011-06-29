<?
/**
 * Crayd Controller
 * 
 * The... base controller class?
 * 
 * @author vee@gate00.net
 */
class Crayd_Controller {

    /**
     *
     * @var Crayd_View
     */
    var $view;
    
    /**
     *
     * @var Crayd_Database
     */
    var $db;
    
    /**
     *
     * @var Crayd_Route
     */
    var $route;
    
    /**
     * 
     * @var mixed
     */
    var $config;
    
    /**
     *
     * @param Crayd_Route $route 
     */
    public function __construct($route) {
        // Must do
        header( 'Content-Type: text/html; charset=utf-8' );
        // set the route
        $this->route = $route;
        $this->config = Crayd_Registry::get('config');
        // Session?
        if($this->config->session) {
            session_start();
        }
    }
    
    /**
     * Dispatcher
     */
    public function dispatch() {
        // Get app directory
        $appDir = Crayd_Registry::get('appDir');
        // Check for global preDispatch
        if(file_exists($appDir . DS . 'globalPreDispatch' . EXT)) {
            include_once($appDir . DS . 'globalPreDispatch' . EXT);
        }
        // run preDispatch
        $this->preDispatch();
        
        
        // run postdispatch
        $this->postDispatch();
        // global postdispatch
        if(file_exists($appDir . DS . 'globalPostDispatch' . EXT)) {
            include_once($appDir . DS . 'globalPostDispatch' . EXT);
        }
        
    }
    
    public function preDispatch() {
        
    }

    public function postDispatch() {
        
    }
}