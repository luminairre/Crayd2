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
     * @var Crayd_Request 
     */
    var $request;

    /**
     *
     * @param Crayd_Route $route 
     */
    public function __construct($route) {
        // Must do
        header('Content-Type: text/html; charset=utf-8');
        // set the route
        $this->route = $route;
        $this->config = Crayd_Registry::get('config');
        // Session?
        if ($this->config->session) {
            session_start();
        }
        // Request object
        $this->request = new Crayd_Request($route);
    }

    /**
     * Dispatcher
     */
    public function dispatch() {
        // Get app directory
        $appDir = Crayd_Registry::get('appDir');

        // Create view instance
        $this->view = new Crayd_View($this->route);

        // Check for global preDispatch
        if (file_exists($appDir . DS . 'globalPreDispatch' . EXT)) {
            include_once($appDir . DS . 'globalPreDispatch' . EXT);
        }
        // Run preDispatch
        $this->preDispatch();

        // Create action name
        $actionName = $this->route->data->action . 'Action';
        // Method check b4 calling
        if ($this->route->data->action != '' && method_exists($this, $actionName)) {
            // Method exist, call it
            $this->$actionName();
        } else {
            // Doesnt exist, call error handler
            if ($this->route->forceView) {
                // Do nothing
            } else {
                // Show error ?
                $this->errorAction();
                $this->view->setView($this->route->data->controller . '_error');
            }
        }

        // Run postDispatch
        $this->postDispatch();
        // Global postDispatch
        if (file_exists($appDir . DS . 'globalPostDispatch' . EXT)) {
            include_once($appDir . DS . 'globalPostDispatch' . EXT);
        }

        // Dispatch layout
        $this->view->dispatchLayout();
    }

    /**
     * Request object handler
     * @return Crayd_Request 
     */
    public function getRequest() {
        return $this->request;
    }

    /**
     * Redirects to another url
     * @param string $url 
     */
    public function _redirect($url) {
        if (strpos($url, 'tp://') !== false) {
            $url = $this->config->view->baseHref . $url;
        }
        header('Location: ' . $url);
        exit;
    }

    /**
     * Calls another action
     * @param string $action
     */
    public function _forward($action, $controller = null) {
        if (!$controller) {
            // No controller..
            $this->view->setView($this->route->data->controller . '_' . $action);
            $actionName = $action . 'Action';
            $this->$actionName();
        } else {
            // Got controller
            $controllerName = $controller . 'Controller';
            $route = $this->route;
            $route->data->controller = $controller;
            $route->data->action = $action;
            $instance = new $controllerName($route);
            $instance->dispatch();
        }
    }

    /**
     * Get all params helper
     * @return mixed
     */
    public function _getAllParams() {
        return $this->getRequest()->getAllParams();
    }

    /**
     * Get a param helper, $default will be returned if no value exists
     * @param string $key
     * @param mixed $default
     * @return mixed 
     */
    public function _getParam($key, $default = null) {
        return $this->getRequest()->getParam($key, $default);
    }
    
    public function _getSegments() {
        return $this->route->data->segments;
    }

    public function preDispatch() {
        
    }

    public function postDispatch() {
        
    }
    
    public function errorAction() {
        
    }

}