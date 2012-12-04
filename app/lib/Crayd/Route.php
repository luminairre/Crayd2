<?

/**
 * Crayd route processing
 * 
 * Processes route
 * 
 * @author vee@gate00.net
 */
class Crayd_Route {

    /**
     * Config
     * @var mixed
     */
    var $config;

    /**
     * Store processed data
     * @var object
     */
    var $data;

    /**
     * Route string
     * @var string
     */
    var $route;

    /**
     * Forces default controller to load
     * @var boolean
     */
    var $forceDefault = false;

    /**
     * Forces view file without controller
     * @var boolean
     */
    var $forceView = false;

    /**
     * Constructor
     * @param string 
     */
    public function __construct($route) {
        // Get config from registry
        $this->config = Crayd_Registry::get('config')->route;

        // route
        $this->route = $route;

        $this->parse();
    }

    /**
     * Parses route
     */
    public function parse() {
        // App folder
        $appDir = Crayd_Registry::get('appDir');
        $controllerDir = $appDir . DS . 'controllers';
        $viewDir = $appDir . DS . 'views';
        // Used segment
        $used = 0;
        // Namespace detection 
        if (!empty($this->config->namespace)) {
            global $namespace;
            $this->data->namespace = $namespace = $this->config->namespace;
        }

        // Section detection
        if (!(empty($this->route) || $this->route == '/') && is_array($this->config->sections) && count($this->config->sections)) {
            $this->detectSection();
            $controllerDir = $appDir . DS . 'controllers';
            $viewDir = $appDir . DS . 'views';
            // Namespace detection
            if (!empty($this->config->namespace)) {
                $controllerDir .= DS . $this->config->namespace;
                $viewDir .= DS . $this->config->namespace;
            }
            $controllerDir .= DS . $this->data->section;
            $viewDir .= DS . $this->data->section;
        } else if (!empty($this->route) && $this->route != '/') {
            // No section detected, just get the segments if exists
            $this->data->segments = explode('/', $this->route);
            if (!empty($this->config->namespace)) {
                $controllerDir .= DS . $this->config->namespace;
                $viewDir .= DS . $this->config->namespace;
            }
        } else {
            if (!empty($this->config->namespace)) {
                $controllerDir .= DS . $this->config->namespace;
                $viewDir .= DS . $this->config->namespace;
            }
        }

        // Parse route to get action and controller
        if (empty($this->route) || $this->route == '/') {
            // Empty route
            $this->data->action = 'index';
            $this->data->controller = 'index';
            // Check the index controller
            if (!file_exists($controllerDir . DS . 'indexController' . EXT)) {
                $this->forceDefault = true;
                if (file_exists($viewDir . DS . 'index_index' . EXT)) {
                    $this->forceView = true;
                }
            } else if (!method_exists('indexController', 'indexAction')) {
                if (file_exists($viewDir . DS . 'index_index' . EXT)) {
                    $this->forceView = true;
                }
            }
        } else {
            // Well here goes the route parsing
            $segments = $this->data->segments;
            // check for default controller
            if(!empty($this->config->defaultControllers[$segments[0]])) {
                // there is a default controller, lets push it
                array_unshift($segments, $this->config->defaultControllers[$segments[0]]);
            }
            // Set default action
            // dunno but i just feel that i have to set here..
            if ($segments[1] != '') {
                $this->data->action = $segments[1];
            } else {
                $this->data->action = 'index';
            }
            // Check controller existence
            if (file_exists($controllerDir . DS . $segments[0] . 'Controller' . EXT)) {
                $this->data->controller = $segments[0];
                // Check method existence for this controller..
                if (method_exists($this->data->controller . 'Controller', $this->data->action . 'Action')) {
                    // Method exists...
                    $used = 2;
                } else if (file_exists($viewDir . DS . $this->data->controller . '_' . $this->data->action . EXT)) {
                    // View file exists but method doesnt...
                    $this->forceView = true;
                    $used = 2;
                } else if ($this->data->action != 'index' && method_exists($segments[0] . 'Controller', 'indexAction')) {
                    // Action is not index, but indexAction exists...
                    $this->data->action = 'index';
                    $used = 1;
                } else if (file_exists($viewDir . DS . $this->data->controller . '_index' . EXT)) {
                    // Index action does not exist, but view file exists..
                    $this->data->action = 'index';
                    $this->forceView = true;
                    $used = 1;
                } else {
                    // Meh... ? file exist but the class and the view doesnt... hacking attempt i guess..
                    $this->data->action = 'error';
                }
            } else {
                if (file_exists($controllerDir . DS . 'indexController' . EXT) && method_exists('indexController', $segments[0] . 'Action')) {
                    // Index controller exists, time to check the action...
                    // this time use first segment as action
                    // Method exists
                    $this->data->controller = 'index';
                    $this->data->action = $segments[0];
                    $used = 1;
                } else if (file_exists($viewDir . DS . 'index_' . $segments[0] . EXT)) {
                    // Only view file exists
                    $this->data->controller = 'index';
                    $this->data->action = $segments[0];
                    $this->forceView = true;
                    $used = 1;
                } else if (file_exists($viewDir . DS . $segments[0] . '_index' . EXT)) {
                    // View file of the segment..
                    $this->data->controller = $segments[0];
                    $this->data->action = 'index';
                    $this->forceView = true;
                    $this->forceDefault = true;
                    $used = 1;
                } else if (file_exists($viewDir . DS . $segments[0] . '_' . $segments[1] . EXT)) {
                    // View file of the segment..
                    $this->data->controller = $segments[0];
                    $this->data->action = '';
                    $this->forceView = true;
                    $this->forceDefault = true;
                    $used = 2;
                } else if (file_exists($viewDir . DS . $segments[0] . EXT)) {
                    // View file of the segment..
                    $this->data->controller = $segments[0];
                    $this->data->action = '';
                    $this->forceView = true;
                    $this->forceDefault = true;
                    $used = 1;
                } else if (file_exists($controllerDir . DS . 'indexController' . EXT) && method_exists('indexController', 'indexAction')) {
                    // Index... should i really do this?
                    $this->data->controller = 'index';
                    $this->data->action = 'index';
                } else {
                    // no view file found...
                    $this->data->controller = 'error';
                    $this->data->action = 'error';
                    $this->forceView = true;
                    $this->forceDefault = true;
                }
            }

            // Now parse variables
            $this->parseVariables($used);
        }

        // Move others
        $_REQUEST['_segments'] = $this->data->segments;
        if (!empty($this->data->section))
            $_REQUEST['_section'] = $this->data->section;
        if (!empty($this->data->namespace))
            $_REQUEST['_namespace'] = $this->data->namespace;
        $_REQUEST['_action'] = $this->data->action;
        $_REQUEST['_controller'] = $this->data->controller;
    }

    /**
     * Parses variables...
     * @param int $used 
     */
    public function parseVariables($used) {
        $segmentCount = count($this->data->segments);
        if ($segmentCount > $used) {
            // Some segment left...
            $segments = $this->data->segments;
            $usedCount = $used;
            for ($i = $usedCount; $i < $segmentCount; $i++) {
                $variables[$segments[$i]] = $segments[$i + 1];
                $_GET[$segments[$i]] = $segments[$i + 1];
                $_REQUEST[$segments[$i]] = $segments[$i + 1];
                $i++;
            }
            $this->data->variables = $variables;
        }
    }

    /**
     * Processes section
     */
    public function detectSection() {
        // Get segments
        $this->data->segments = explode('/', $this->route);
        // Check if first segment is a section
        if (in_array($this->data->segments[0], $this->config->sections)) {
            // Ok is a section...
            // Now set the section as well as getting rid of the string
            $this->data->section = array_shift($this->data->segments);
            global $section;
            $section = $this->data->section;
            // Re-set the route if there are some segment left
            if (count($this->data->segments) > 0) {
                // Still some segment left
                $this->route = implode('/', $this->data->segments);
            } else {
                // Meh...
                $this->route = '';
            }
        }
    }

}
