<?php
/**
* @version $Id: toolbar.connector.html.php 108 2005-09-16 17:39:25Z stingrey $
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

/**
* @package Joomla
* @subpackage Connectors
*/
class TOOLBAR_connector {
	/**
	* Draws the menu for a New category
	*/
	function _NEW() {
		mosMenuBar::startTable();
		mosMenuBar::save();
		mosMenuBar::spacer();
		mosMenuBar::cancel();
		mosMenuBar::spacer();
		mosMenuBar::help( 'screen.connectors.edit' );
		mosMenuBar::endTable();
	}
	/**
	* Draws the menu for Editing an existing category
	*/
	function _EDIT( $connectorid, $cur_template ) {
		global $database;
		global $id;

		$sql = "SELECT template"
		. "\n FROM #__templates_menu"
		. "\n WHERE client_id = 0"
		. "\n AND menuid = 0"
		;
		$database->setQuery( $sql );
		$cur_template = $database->loadResult();
		mosMenuBar::startTable();
		mosMenuBar::save();
		mosMenuBar::spacer();
		if ( $id ) {
			// for existing content items the button is renamed `close`
			mosMenuBar::cancel( 'cancel', 'Close' );
		} else {
			mosMenuBar::cancel();
		}
		mosMenuBar::spacer();
		mosMenuBar::help( 'screen.connectors.edit' );
		mosMenuBar::endTable();
	}
        function _EDITM( $connectorid, $cur_template ) {
                global $database;
                global $id;

                $sql = "SELECT template"
                . "\n FROM #__templates_menu"
                . "\n WHERE client_id = 0"
                . "\n AND menuid = 0"
                ;
                $database->setQuery( $sql );
                $cur_template = $database->loadResult();
                mosMenuBar::startTable();
                mosMenuBar::save('saveM');
                mosMenuBar::spacer();
                if ( $id ) {
                        // for existing content items the button is renamed `close`
                        mosMenuBar::cancel( 'cancel', 'Close' );
                } else {
                        mosMenuBar::cancel();
                }
                mosMenuBar::spacer();
                mosMenuBar::help( 'screen.connectors.edit' );
                mosMenuBar::endTable();
        }
	function _DEFAULT() {
		mosMenuBar::startTable();
		mosMenuBar::publishList();
		mosMenuBar::spacer();
		mosMenuBar::unpublishList();
		mosMenuBar::spacer();
		mosMenuBar::deleteList();
		mosMenuBar::spacer();
		mosMenuBar::editListX();
		mosMenuBar::spacer();
		mosMenuBar::addNewX();
		mosMenuBar::spacer();
		mosMenuBar::help( 'screen.connectors' );
		mosMenuBar::endTable();
	}
}
?>
