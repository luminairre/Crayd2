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
/**
 * Enables custom route
 * @see route.php
 */
$config->route->enableCustomRoute = false;
/**
 * Enables section if array is entered
 */
$config->route->sections = array('about');

#######
## View class configuration
#######
$config->view->baseHref = '';

#######
## Database configuration
#######
$config->db->host = '';
$config->db->username = '';
$config->db->password = '';
$config->db->name = '';
$config->db->debug = true;

#######
## Auth class configuration 
#######
$config->auth->table->member = '';
$config->auth->table->data = '';
$config->auth->table->log = '';
$config->auth->uniqueID = '1230';

#######
## Other config
#######
/**
 * Enables debugging... once implemented
 */
$config->debug = true;
/**
 * Turns on session
 */
$config->session = false;