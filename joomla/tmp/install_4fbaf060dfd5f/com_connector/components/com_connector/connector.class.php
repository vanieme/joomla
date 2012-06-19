<?php
/**
* @version $Id: connector.class.php 85 2005-09-15 23:12:03Z eddieajau $
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

/// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
* @package Joomla
* @subpackage Connectors
*/
class mosConnector extends mosDBTable {
	/** @var int Primary key */
	var $id					= null;
	/** @var string */
	var $title				= null;
	/** @var string */
	var $checked_out		= null;
	/** @var time */
	var $checked_out_time	= null;
	/** @var boolean */
	var $published			= null;
	/** @var int */
	var $access				= null;
	/** @var int */
	var $lag				= null;

	/**
	* @param database A database connector object
	*/
	function mosConnector( &$db ) {
		$this->mosDBTable( '#__connectors', 'id', $db );
	}

	// overloaded check function
	function check() {
		// check for valid name
		if (trim( $this->title ) == '') {
			$this->_error = 'Your Connector must contain a title.';
			return false;
		}
		// check for existing title
		$query = "SELECT id"
		. "\n FROM #__connectors"
		. "\n WHERE title = '$this->title'"
		;
		$this->_db->setQuery( $query );

		$xid = intval( $this->_db->loadResult() );
		if ( $xid && $xid != intval( $this->id ) ) {
			$this->_error = 'There is a module already with that name, please try again.';
			return false;
		}

		// sanitise some data
		if ( !get_magic_quotes_gpc() ) {
			$this->title = addslashes( $this->title );
		}

		return true;
	}

}
?>
