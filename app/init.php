<?

/**
 * Crayd Framework bootstapper file
 * 
 * @author vee@gate00.net
 */
/**
 * Protection
 */
if (!defined('INFRAMEWORK')) {
    exit;
}

/**
 * Debugger...
 * @param string $message 
 */
function iQuit($message) {
    // validate setting again
    if (Crayd_Registry::get('config')->debug) {
        echo '<!-- ' . $message . ' -->';
        exit;
    } else {
        return true;
    }
}

// Initialize autoload
function __autoload($className) {
    global $appDir, $section, $namespace;
    // Parse class name to determine include file
    if (strpos($className, 'Controller') !== false && strpos($className, '_Controller') === false) {
        // Is controller
        // Do the section detection...
        if (!empty($section)) {
            if (!empty($namespace)) {
                if (file_exists($appDir . DS . 'controllers' . DS . $namespace . DS . $section . DS . $className . EXT)) {
                    include_once($appDir . DS . 'controllers' . DS . $namespace . DS . $section . DS . $className . EXT);
                }
            } else {
                if (file_exists($appDir . DS . 'controllers' . DS . $section . DS . $className . EXT)) {
                    include_once($appDir . DS . 'controllers' . DS . $section . DS . $className . EXT);
                }
            }
        } else {
            if (!empty($namespace)) {
                if (file_exists($appDir . DS . 'controllers' . DS . $namespace . DS . $className . EXT)) {
                    include_once($appDir . DS . 'controllers' . DS . $namespace . DS . $className . EXT);
                }
            } else {
                if (file_exists($appDir . DS . 'controllers' . DS . $className . EXT)) {
                    include_once($appDir . DS . 'controllers' . DS . $className . EXT);
                }
            }
        }
    } else if (strpos($className, 'Model') !== false) {
        // Model
        if (file_exists($appDir . DS . 'models' . DS . $className . EXT)) {
            include_once($appDir . DS . 'models' . DS . $className . EXT);
        }
    } else {
        // Detect underscores as subfolder
        if (strpos($className, '_') !== false) {
            $classFile = str_replace('_', '/', $className);
        } else {
            $classFile = $className . DS . $className;
        }
        // check.. sadly.. no exception...
        if (file_exists($appDir . DS . 'lib' . DS . $classFile . EXT)) {
            include_once($appDir . DS . 'lib' . DS . $classFile . EXT);
        } else if (file_exists($appDir . DS . 'models' . DS . $className . EXT)) { // Add no "Model" prefix class name support
            include_once($appDir . DS . 'models' . DS . $className . EXT);
        } else {
            // Debugger test
            iQuit('Class doesnt exist: ' . $className);
        }
    }
}

// Load config
require_once($appDir . DS . 'config.php');
// Store registry
Crayd_Registry::set('config', $config);
Crayd_Registry::set('appDir', $appDir);

// Parse route from path info OR route param
if (empty($_GET['_route']) && (!empty($_SERVER['PATH_INFO']) || !empty($_SERVER['ORIG_PATH_INFO']))) {
    if (!empty($_SERVER['PATH_INFO'])) {
        $_GET['_route'] = substr($_SERVER['PATH_INFO'], 1);
    } else {
        $_temp = explode('index.php', $_SERVER['ORIG_PATH_INFO']);
        $_temp = $_temp[1];
        $_GET['_route'] = substr($_temp, 1);
    }
} else {
    // Handler for ?_route=blablabla?var=value 
    if (strpos($_SERVER['REQUEST_URI'], '?') !== false) {
        $_temp = explode('?', $_SERVER['REQUEST_URI']);
        parse_str($_temp[1], $ar);
        $_GET = array_merge($_GET, $ar);
        $_REQUEST = array_merge($_REQUEST, $ar);
        unset($ar);
        unset($_temp);
    }
}
// Check for $argv
if(is_array($argv) && count($argv) > 0 && empty($_GET['_route'])) {
    // this is a CLI call..
    // route is the second argument
    $_GET['_route'] = $argv[1];
    // second arg is query..
    if(!empty($argv[2])) {
        $ar = array();
        $_temp = parse_str($argv[2], $ar);
        $_GET = array_merge($_GET, $ar);
        $_REQUEST = $ar;
    }
}

// Init route
$route = new Crayd_Route($_GET['_route']);

// Init controller
if ($route->forceDefault) {
    $controller = new Crayd_Controller($route);
} else {
    $controller = $route->data->controller . 'Controller';
    $controller = new $controller($route);
}

// W00t dispatch time
$controller->dispatch();