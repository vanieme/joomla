<?php

define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', '/work/joomla');
if (file_exists(JPATH_BASE.'/defines.php')) {
	include_once JPATH_BASE.'/defines.php';
}

require_once JPATH_BASE.'/includes/defines.php';

require_once JPATH_BASE.'/includes/framework.php';
 
jimport('joomla.plugin.plugin');
jimport('joomla.user.helper');
jimport( 'joomla.application.component.helper' );
jimport( 'joomla.registry.registry' );

echo doHello('asdasd');

if(!extension_loaded("soap")){
  dl("php_soap.dll");
}

ini_set("soap.wsdl_cache_enabled","0");
$server = new SoapServer('hello.wsdl');

function doHello($yourName){

 $id = JUserHelper::getUserId('vanitha.mohan@tradingpursuits.com');
 $instance = new JUser();
 $instance->load($id);
 $instance->name = 'Vanitha';
 $instance->save(true);

 return "Hello, ".$instance->name;
}

$server->AddFunction("doHello");
$server->handle();

?>