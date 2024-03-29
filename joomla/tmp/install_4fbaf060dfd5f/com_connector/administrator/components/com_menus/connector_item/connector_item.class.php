<?php
/**
* @version $Id: connector_item.class.php 393 2005-10-08 13:37:52Z akede $
* @package Joomla
* @subpackage Menus
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

/**
* Component item link class
* @package Joomla
* @subpackage Menus
*/
class connector_item_menu {

	function edit( &$uid, $menutype, $option ) {
		global $database, $my, $mainframe;

		$menu = new mosMenu( $database );
		$menu->load( $uid );

		// fail if checked out not by 'me'
		if ($menu->checked_out && $menu->checked_out != $my->id) {
			echo "<script>alert('The module $menu->title is currently being edited by another administrator'); document.location.href='index2.php?option=$option'</script>\n";
			exit(0);
		}

		if ( $uid ) {
			$menu->checkout( $my->id );
		} else {
			// load values for new entry
			$menu->type 		= 'connector_item';
			$menu->menutype 	= $menutype;
			$menu->browserNav 	= 0;
			$menu->ordering 	= 9999;
			$menu->parent 		= intval( mosGetParam( $_POST, 'parent', 0 ) );
			$menu->published 	= 1;
		}

		if ( $uid ) {
			$temp = explode( '&Itemid=', $menu->link );
			 $query = "SELECT a.name"
			. "\n FROM #__menu AS a"
			. "\n WHERE a.link = '$temp[0]'"
			;
			$database->setQuery( $query );
			$connectors = $database->loadResult();
			$lists['connectors'] =  $connectors;
			$lists['connectors'] .= '<input type="hidden" name="link" value="'. $menu->link .'" />';
		} else {
                	$query = "SELECT CONCAT( 'index.php?option=com_connector&amp;cid=', c.id ) AS value, c.title AS text"
                	. "\n FROM #__connectors AS c"
               	 	. "\n WHERE c.published = 1"
                	. "\n ORDER BY c.title"
                	;
			$database->setQuery( $query );
			$connectors = $database->loadObjectList( );
			//	Create a list of links
			$lists['connectors'] = mosHTML::selectList( $connectors, 'link', 'class="inputbox" size="10"', 'value', 'text', '' );
		}

		// build html select list for target window
		$lists['target'] 		= mosAdminMenus::Target( $menu );

		// build the html select list for ordering
		$lists['ordering'] 		= mosAdminMenus::Ordering( $menu, $uid );
		// build the html select list for the group access
		$lists['access'] 		= mosAdminMenus::Access( $menu );
		// build the html select list for paraent item
		$lists['parent'] 		= mosAdminMenus::Parent( $menu );
		// build published button option
		$lists['published'] 	= mosAdminMenus::Published( $menu );
		// build the url link output
		$lists['link'] 		= mosAdminMenus::Link( $menu, $uid, 1 );

		// get params definitions
		$params = new mosParameters( $menu->params, $mainframe->getPath( 'menu_xml', $menu->type ), 'menu' );

		connector_item_menu_html::edit( $menu, $lists, $params, $option );
	}
}
?>
