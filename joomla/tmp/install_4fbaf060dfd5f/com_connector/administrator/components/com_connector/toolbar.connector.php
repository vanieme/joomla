<?php
/**
* @version $Id: toolbar.connector.php 85 2005-09-15 23:12:03Z eddieajau $
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

require_once( $mainframe->getPath( 'toolbar_html' ) );

switch ($task) {
	case 'new':
		TOOLBAR_connector::_NEW();
		break;

	case 'edit':
		$cid = mosGetParam( $_REQUEST, 'cid', array(0) );
		if (!is_array( $cid )) {
			$cid = array(0);
		}

		$query = "SELECT published"
		. "\n FROM #__connectors"
		. "\n WHERE id = $cid[0]"
		;
		$database->setQuery( $query );
		$published = $database->loadResult();

		$cur_template = $mainframe->getTemplate();

		TOOLBAR_connector::_EDIT( $cid[0], $cur_template );
		break;

	case 'editA':
		$id = mosGetParam( $_REQUEST, 'id', 0 );

		$query = "SELECT published"
		. "\n FROM #__connectors"
		. "\n WHERE id = $id"
		;
		$database->setQuery( $query );
		$published = $database->loadResult();

		$cur_template = $mainframe->getTemplate();

		TOOLBAR_connector::_EDIT( $id, $cur_template );
		break;
	case 'editM':
                $id = mosGetParam( $_REQUEST, 'id', 0 );

                $query = "SELECT published"
                . "\n FROM #__connectors"
                . "\n WHERE id = $id"
                ;
                $database->setQuery( $query );
                $published = $database->loadResult();

                $cur_template = $mainframe->getTemplate();

                TOOLBAR_connector::_EDITM( $id, $cur_template );
                break;


	default:
		TOOLBAR_connector::_DEFAULT();
		break;
}
?>
