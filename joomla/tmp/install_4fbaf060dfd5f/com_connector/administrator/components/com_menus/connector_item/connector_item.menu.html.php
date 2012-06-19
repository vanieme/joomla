<?php
/**
* @version $Id: connector_item.menu.html.php 266 2005-11-28 04:44:59 leonsio $
* @package com_connector
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
* Display Connector item link
* @package Joomla
* @subpackage Menus
*/
class connector_item_menu_html {

	function edit( &$menu, &$lists, &$params, $option ) {
		global $mosConfig_live_site;
		?>
<div align="center">                    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="image" src="https://www.paypal.com/de_DE/i/btn/x-click-but04.gif" border="0" name="submit" alt="Zahlen Sie mit PayPal - schnell, kostenlos und sicher!">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
</form></div>

		<div id="overDiv" style="position:absolute; visibility:hidden; z-index:10000;"></div>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if ( pressbutton == 'cancel' ) {
				submitform( pressbutton );
				return;
			}

			// do field validation
			if ( trim(form.name.value) == "" ){
				alert( "Link must have a name" );
			} else if ( trim( form.link.value ) == "" ){
				alert( "You must select a Connector to link to" );
			} else {
				submitform( pressbutton );
			}
		}
		</script>

		<form action="index2.php" method="post" name="adminForm">
		<table class="adminheading">
		<tr>
			<th>
			<?php echo $menu->id ? 'Edit' : 'Add';?> Menu Item :: Connector  Item
			</th>
		</tr>
		</table>

		<table width="100%">
		<tr valign="top">
			<td width="60%">
				<table class="adminform">
				<tr>
					<th colspan="2">
					Details
					</th>
				</tr>
				<tr>
					<td width="10%" align="right">
					Name:
					</td>
					<td width="80%">
					<input class="inputbox" type="text" name="name" size="50" maxlength="100" value="<?php echo $menu->name; ?>" />
					</td>
				</tr>
				<tr>
					<td width="10%" align="right" valign="top">
					Connector to Link:
					</td>
					<td width="80%">
					<?php echo $lists['connectors']; ?>
					</td>
				</tr>
				<tr>
					<td width="10%" align="right">Url:</td>
					<td width="80%">
                    <?php echo ampReplace($lists['link']); ?>
					</td>
				</tr>
				<tr>
					<td width="10%" align="right" valign="top">
					On Click, Open in:
					</td>
					<td width="80%">
					<?php echo $lists['target']; ?>
					</td>
				</tr>
				<tr>
					<td align="right">
					Parent Item:
					</td>
					<td>
					<?php echo $lists['parent']; ?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">
					Ordering:
					</td>
					<td>
					<?php echo $lists['ordering']; ?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">
					Access Level:
					</td>
					<td>
					<?php echo $lists['access']; ?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">Published:</td>
					<td>
					<?php echo $lists['published']; ?>
					</td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
				</table>
			</td>
			<td width="40%">
				<table class="adminform">
				<tr>
					<th>
					Parameters
					</th>
				</tr>
				<tr>
					<td>
					<?php echo $params->render();?>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		</table>

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="id" value="<?php echo $menu->id; ?>" />
		<input type="hidden" name="menutype" value="<?php echo $menu->menutype; ?>" />
		<input type="hidden" name="type" value="<?php echo $menu->type; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="hidemainmenu" value="0" />
		</form>
		<script language="Javascript" src="<?php echo $mosConfig_live_site;?>/includes/js/overlib_mini.js"></script>
		<?php
	}
}
?>
