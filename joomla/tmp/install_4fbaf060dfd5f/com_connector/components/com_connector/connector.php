<?php
/**
* @version $Id: connector.php 393 2005-12-15 13:37:52Z leonsio $
* @package com_connector
* @copyright Copyright (C) 2005 Leonid Kogan. All rights reserved.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

require_once( $mainframe->getPath( 'class' ) );

$connector = new mosConnector( $database );

$cid 	= intval( mosGetParam( $_REQUEST, 'cid', 0 ) );

$query ="       SELECT *                "
."\n            FROM #__connectors      "
."\n            WHERE published=1       "
."\n		AND id=$cid		"
;

$database->setQuery($query);
$data = $database->loadObject($module);

// Passendes Modul laden
require_once("./components/com_connector/modules/$module->module.class.php");

//Modul initialisieren
$params=new mosParameters($module->params);
$application=new $module->module($params, $module->id);

if(method_exists($application, 'activate'))
{
	if($application->activate())
	{
		$module->url.=$application->__sessionid;
	}
}


switch($module->smode)
{
	case 1:
		wrapped_mode($module);
		break;
	case 2:
		wrapped_extreme();
		break;	
	case 0:
	default:
		mosRedirect( $module->url );
		break;
}

function wrapped_mode($module)
{
	$html='
           <iframe   
                id="blockrandom"
                name="forum"
                src="'.$module->url.'" 
                width="100%" 
                height="3800" 
                scrolling="yes" 
                align="top"
                frameborder="0"
                class="wrapper"
                >
                '._CMN_IFRAMES.'
                </iframe>

	';

	echo $html;
}

function wrapped_extreme()
{
	// kommt noch
	// will came later
}

?>
