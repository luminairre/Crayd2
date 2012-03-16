<?
/**
 * Crayd Framework 
 * Simple, fast, easy to use framework with similarities to Zend Framework
 * 
 * Boot file
 * 
 * @author vee@gate00.net
 * @version 2.0.0b
 */

// Protection
define('INFRAMEWORK', TRUE);
// Initialize variables
define('DS', DIRECTORY_SEPARATOR);
define('EXT', '.php');
global $root, $appDir;
$root = dirname(__FILE__);
// App folder config
$appDir = $root . DS . 'app';
// Include path for multiple library compatibility
set_include_path($appDir . DS . 'lib');
// Include bootstrapper
include_once($appDir . DS . 'init.php');