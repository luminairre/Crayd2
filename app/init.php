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

// Initialize autoload
function __autoload($className) {
    global $appDir, $section;
    // Parse class name to determine include file
    if (strpos($className, 'Controller') !== false && strpos($className, '_Controller') === false) {
        // Is controller
        // Do the section detection...
        if ($section != null) {
            if (file_exists($appDir . DS . 'controllers' . DS . $section . DS . $className . EXT)) {
                include_once($appDir . DS . 'controllers' . DS . $section . DS . $className . EXT);
            }
        } else {
            if (file_exists($appDir . DS . 'controllers' . DS . $className . EXT)) {
                include_once($appDir . DS . 'controllers' . DS . $className . EXT);
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
    if(strpos($_SERVER['REQUEST_URI'], '?') !== false) {
        $_temp = explode('?', $_SERVER['REQUEST_URI']);
        $_temp = explode('&', $_temp[1]);
		foreach($_temp as $value) {
			$_temp2 = explode('=', $value);
			$_GET[$_temp2[0]] = $_temp2[1];
		}
        unset($_temp);
		unset($_temp2);
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