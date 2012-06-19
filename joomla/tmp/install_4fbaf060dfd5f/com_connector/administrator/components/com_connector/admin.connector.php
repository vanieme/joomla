<?php
/**
* @version $Id: admin.connector.php 393 2005-10-08 13:37:52Z akede $
* @package Joomla
* @subpackage Connectors
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

// ensure user has access to this function
if (!($acl->acl_check( 'administration', 'edit', 'users', $my->usertype, 'components', 'all' )
		| $acl->acl_check( 'administration', 'edit', 'users', $my->usertype, 'components', 'com_connector' ))) {
	mosRedirect( 'index2.php', _NOT_AUTH );
}

require_once( $mainframe->getPath( 'admin_html' ) );
require_once( $mainframe->getPath( 'class' ) );

$cid 	= mosGetParam( $_REQUEST, 'cid', array(0) );
if (!is_array( $cid )) {
	$cid = array(0);
}

switch( $task ) {
	case 'new':
		editConnector( 0, $option );
		break;

	case 'edit':
		editConnector( $cid[0], $option );
		break;

	case 'editA':
		editConnector( $id, $option );
		break;
	case 'editM':
		editModule($id,$module, $option);
		break;
	case 'save':
		saveConnector($option );
		break;
        case 'saveM':
                saveModule($id,  $option );
                break;
	case 'remove':
		removeConnector( $cid, $option );
		break;

	case 'publish':
		publishConnectors( $cid, 1, $option );
		break;

	case 'unpublish':
		publishConnectors( $cid, 0, $option );
		break;

	case 'cancel':
		cancelConnector( $option );
		break;

	default:
		showConnectors( $option );
		break;
}

function showConnectors( $option ) {
	global $database, $mainframe, $mosConfig_list_limit;

	$limit 		= $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit );
	$limitstart = $mainframe->getUserStateFromRequest( "view{$option}limitstart", 'limitstart', 0 );

	$query = "SELECT COUNT(*)"
	. "\n FROM #__connectors"
	;
	$database->setQuery( $query );
	$total = $database->loadResult();

	require_once( $GLOBALS['mosConfig_absolute_path'] . '/administrator/includes/pageNavigation.php' );
	$pageNav = new mosPageNav( $total, $limitstart, $limit  );

	$query = "SELECT *"
	. "\n FROM #__connectors AS m"
	. "\n GROUP BY m.id"
	. "\n LIMIT $pageNav->limitstart, $pageNav->limit"
	;
	$database->setQuery( $query );
	$rows = $database->loadObjectList();

	if ($database->getErrorNum()) {
		echo $database->stderr();
		return false;
	}

	HTML_connector::showConnectors( $rows, $pageNav, $option );
}

function editConnector( $uid=0, $option='com_connector' ) {
	global $database, $my;

	$row = new mosConnector( $database );
	// load the row from the db table
	$row->load( $uid );

	// fail if checked out not by 'me'
	if ($row->isCheckedOut( $my->id )) {
		mosRedirect( 'index2.php?option='. $option, 'The connector '. $row->title .' is currently being edited by another administrator.' );
	}

	if ($uid) {
		$row->checkout( $my->id );
		$query = "SELECT *"
		. "\n FROM #__connectors"
		. "\n WHERE id = $uid"
		. "\n "
		;
		$database->setQuery($query);
		$options = $database->loadObjectList();
	} 
	$d=dir('../components/com_connector/modules');
	while (false !== ($entry = $d->read())) {
		if(preg_match('/[\w]+/',$entry,$name))
		{
			$modules[] = mosHTML::makeOption( $name[0], str_replace('_',' ',strtoupper($name[0])) );
		}
	}

	$displaymode[]=mosHTML::makeOption( 0, 'not Wrapped' );
	$displaymode[]=mosHTML::makeOption( 1, 'Wrapped' );
	$displaymode[]=mosHTML::makeOption( 2, 'Wrapped Extreme' );

        $options['modules'] = mosHTML::selectList( $modules, 'module', 'class="inputbox" size="1"', 'value', 'text', $options[0]->module);
        $options['smode'] = mosHTML::selectList( $displaymode, 'smode', 'class="inputbox" size="1"', 'value', 'text', $options[0]->smode);

    	$options['jos_useradd'] = mosHTML :: yesnoRadioList('jos_useradd', 'class="inputbox" size="1"', $options[0]->jos_useradd);
     	$options['app_useradd'] = mosHTML :: yesnoRadioList('app_useradd', 'class="inputbox" size="1"', $options[0]->app_useradd);


	HTML_connector::editConnector($row, $options, $lists );
}

function saveConnector( $option ) {
	global $database, $my;
	// save the connector parent information
	$row = new mosConnector( $database );
	if (!$row->bind( $_POST )) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	$isNew = ($row->id == 0);

	if (!$row->check()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}

	if (!$row->store()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	$row->checkin();

	// save the connector options
	$title = mosGetParam( $_POST, 'title', '' );
	$module = mosGetParam( $_POST, 'module',''  );
	$url = mosGetParam( $_POST, 'url', '' );
	$jos_useradd = mosGetParam( $_POST, 'jos_useradd', '' );
	$app_useradd = mosGetParam( $_POST, 'app_useradd', '' );
	$smode = mosGetParam( $_POST, 'smode', '' );
	$id = mosGetParam( $_POST, 'id', '' );

		if ($isNew) {
			$query = "UPDATE #__connectors"
                        . "\n SET title = '$title',"
                        . "\n  module = '$module',"
                        . "\n  url = '$url',"
                        . "\n  jos_useradd = '$jos_useradd',"
                        . "\n  app_useradd = '$app_useradd',"
                        . "\n  smode= '$smode'"
                        . "\n WHERE id='".$row->id."'"
			;
			$database->setQuery( $query );
			$database->query();

		} else {
			$query = "UPDATE #__connectors"
			. "\n SET title = '$title',"
			. "\n  module = '$module',"
			. "\n  url = '$url',"
			. "\n  jos_useradd = '$jos_useradd',"
			. "\n  app_useradd = '$app_useradd',"
			. "\n  smode= '$smode'"
			. "\n WHERE id='$id'"
			;
			$database->setQuery( $query );
			$database->query();
		}

	mosRedirect( 'index2.php?task=editM&id='.$id.'&module='.$module.'&option='. $option );
}
function editModule($id,$module,$option)
{
        global $database, $my;

        $row = new mosConnector( $database );
        // load the row from the db table
        $row->load( $id );
        // fail if checked out not by 'me'
        if ($row->isCheckedOut( $my->id )) {
                mosRedirect( 'index2.php?option='. $option, 'The connector '. $row->title .' is currently being edited by another administrator.' );
        }

        if ($id) {
                $row->checkout( $my->id );
                $query = "SELECT *"
                . "\n FROM #__connectors"
                . "\n WHERE id = $id"
                . "\n "
                ;
                $database->setQuery($query);
                $database->loadObject($options);
        }

	require_once("../components/com_connector/modules/$module.class.php");
	// es ist ein Update
	if($options->module==$module)
	{
		$old_params=new mosParameters( $options->params );
	}
	
	foreach($params as $name => $title)
	{
		$value=(isset($old_params->_params->$name))? $old_params->_params->$name : '' ;
		$html.= '
                <tr>
                        <td width="10%" align="left">
                        '.$title.':
                        </td>
                        <td align="left">
                        <input class="inputbox" type="text" name="params['.$name.']" size="60" value="'.$value.'" />
                        </td>
                </tr>
		';
	}
	HTML_connector::editModule($id, $module, $html );
}
function saveModule($id,$option)
{
        global $database, $my;
        // save the connector parent information
        $row = new mosConnector( $database );
        if (!$row->bind( $_POST )) {
                echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
                exit();
        }
        $row->checkin();

        // save the connector options
	$params = mosGetParam( $_POST, 'params', array() );

	foreach($params as $name => $value)
	{
		$s_params[]=$name.'='.$value;
	}

        $query = "UPDATE #__connectors"
                 . "\n SET params = '".addslashes(implode("\n",$s_params))."'"
                 . "\n WHERE id='$id'"
                 ;

        $database->setQuery( $query );
        $database->query();

	 mosRedirect( 'index2.php?option='. $option );
}
function removeConnector( $cid, $option ) {
	global $database;
	$msg = '';
	for ($i=0, $n=count($cid); $i < $n; $i++) {
		$connector = new mosConnector( $database );
		if (!$connector->delete( $cid[$i] )) {
			$msg .= $connector->getError();
		}
	}
	mosRedirect( 'index2.php?option='. $option .'&mosmsg='. $msg );
}

function cancelConnector( $option ) {
	global $database;
	$row = new mosConnector( $database );
	$row->bind( $_POST );
	$row->checkin();
	mosRedirect( 'index2.php?option='. $option );
}
function publishConnectors( $cid=null, $publish=1, $option ) {
        global $database, $my;
                
        $catid = mosGetParam( $_POST, 'catid', array(0) );
        
        if (!is_array( $cid ) || count( $cid ) < 1) {
                $action = $publish ? 'publish' : 'unpublish';
                echo "<script> alert('Select an item to $action'); window.history.go(-1);</script>\n";
                exit;
        }
                
        $cids = implode( ',', $cid );
                
        $query = "UPDATE #__connectors"
        . "\n SET published = " . intval( $publish )
        . "\n WHERE id IN ( $cids )"
        . "\n AND ( checked_out = 0 OR ( checked_out = $my->id ) )"
        ;
        $database->setQuery( $query );
        if (!$database->query()) {
                echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
                exit();
        }

        if (count( $cid ) == 1) {
                $row = new mosConnector( $database );
                $row->checkin( $cid[0] );
        }
        mosRedirect( 'index2.php?option='. $option );
}

?>
