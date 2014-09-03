<?
/**
 * Config file for Crayd Framework
 * 
 * Config will be stored within registry
 * 
 * @author vee@gate00.net
 */
#######
## Protection
#######
if(!defined('INFRAMEWORK')) {
    exit;
}

#######
## Routing configuration
#######
$config = new stdClass();
$config->route = new stdClass();
$config->view = new stdClass();
$config->db = new stdClass();
$config->auth = new stdClass();
$config->auth->table = new stdClass();
/**
 * Enables section if array is entered
 */
$config->route->sections = array();
/**
 * Namespace, section like container, but not URL-effected 
 */
$config->route->namespace = null;
/**
 * Default controllers
 * ie: 
 * array('detail' => 'product');
 * will "force" /detail/id/1 to "/product/id/1"
 */
$config->route->defaultControllers = array();

#######
## View class configuration
#######
$config->view->baseHref = '';
$config->view->sendHeader = true;

#######
## Database configuration
#######
$config->db->host = '';
$config->db->username = '';
$config->db->password = '';
$config->db->database = '';
$config->db->debug = true;
$config->db->enabled = false;

#######
## Auth class configuration 
#######
$config->auth->table->member = '';
$config->auth->table->data = '';
$config->auth->table->log = '';
$config->auth->uniqueID = '1230';
$config->auth->domain = '';

#######
## Other config
#######
/**
 * Enables debugging... once implemented
 */
$config->debug = true;
/**
 * Comment to disable session
 */
$config->session = true;
session_start();