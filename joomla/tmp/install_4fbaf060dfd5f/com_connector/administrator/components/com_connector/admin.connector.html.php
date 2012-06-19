<?php
/**
* @version $Id: admin.connector.html.php 85 2005-09-15 23:12:03Z eddieajau $
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
class HTML_connector {

	function showConnectors( &$rows, &$pageNav, $option ) {
		global $my;

		mosCommonHTML::loadOverlib();
		?>
<div align="center">			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="image" src="https://www.paypal.com/de_DE/i/btn/x-click-but04.gif" border="0" name="submit" alt="Zahlen Sie mit PayPal - schnell, kostenlos und sicher!">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
</form></div>
		<form action="index2.php" method="post" name="adminForm">
		<table class="adminheading">
		<tr>
			<th>Connector Manager</th>
		</tr>
		</table>

		<table class="adminlist">
		<tr>
			<th width="5">
			#
			</th>
			<th width="20">
			<input type="checkbox" name="toggle" value="" onClick="checkAll(<?php echo count( $rows ); ?>);" />
			</th>
			<th align="left">
			Connector Title
			</th>
                        <th width="10%" align="center">
                        Published
                        </th>
			<th width="10%" align="center">
			Module
			</th>
		</tr>
		<?php
		$k = 0;
		for ($i=0, $n=count( $rows ); $i < $n; $i++) {
			$row = &$rows[$i];

			$link 	= 'index2.php?option=com_connector&task=editA&hidemainmenu=1&id='. $row->id;

			$task 	= $row->published ? 'unpublish' : 'publish';
			$img 	= $row->published ? 'publish_g.png' : 'publish_x.png';
			$alt 	= $row->published ? 'Published' : 'Unpublished';

			$checked 	= mosCommonHTML::CheckedOutProcessing( $row, $i );
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td>
				<?php echo $row->id; ?>
				</td>
				<td>
				<?php echo $checked; ?>
				</td>
				<td>
				<a href="<?php echo $link; ?>" title="Edit Connector">
				<?php echo $row->title; ?>
				</a>
				</td>
                                <td align="center">
                                <a href="javascript: void(0);" onclick="return listItemTask('cb<?php echo $i;?>','<?php echo $task;?>')">
                                <img src="images/<?php echo $img;?>" width="12" height="12" border="0" alt="<?php echo $alt; ?>" />
                                </a>
                                </td>
				<td align="center">
				<?php echo $row->module; ?>
				</td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		</table>
		<?php echo $pageNav->getListFooter(); ?>

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0">
		</form>
		<?php
	}


	function editConnector( &$row, &$options, &$lists ) {
		mosMakeHtmlSafe( $row, ENT_QUOTES );
		?>
<div align="center">                    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="image" src="https://www.paypal.com/de_DE/i/btn/x-click-but04.gif" border="0" name="submit" alt="Zahlen Sie mit PayPal - schnell, kostenlos und sicher!">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
</form></div>
		<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}
			// do field validation
			if (form.title.value == "") {
				alert( "Connector must have a title" );
			} else {
				submitform( pressbutton );
			}
		}
		</script>
		<form action="index2.php" method="post" name="adminForm">
		<table class="adminheading">
		<tr>
			<th>
			Connector:
			<small>
			<?php echo $row->id ? 'Edit' : 'New';?>
			</small>
			</th>
		</tr>
		</table>

		<table class="adminform">
		<tr>
			<th colspan="2">
			Details
			</th>
		</tr>
		<tr>
			<td width="10%" align="left">
			Title:
			</td>
			<td align="left">
			<input class="inputbox" type="text" name="title" size="60" value="<?php echo $options[0]->title; ?>" />
			</td>
		</tr>
		<tr>
			<td>
			Module:
			</td>
			<td>
			<?php echo $options['modules']; ?> 
			</td>
		</tr>
                <tr>
                        <td>
                        URL:
                        </td>
                        <td>
                        <input class="inputbox" type="text" name="url" size="60" value="<?php echo $options[0]->url; ?>" />
                        </td>
                </tr> 
                <tr>
                        <td>
                        Add User to Joomla:
                        </td>
                        <td>
			<?php echo $options['jos_useradd']; ?>
                        </td>
                </tr> 
                <tr>
                        <td>
                        Add User to App.:
                        </td>
                        <td>
			<?php echo $options['app_useradd']; ?>
                        </td>
                </tr> 
                <tr>
                        <td>
                        Display Mode:
                        </td>
                        <td>
                        <?php echo $options['smode']; ?>
                        </td>
                </tr>
		</table>

		<input type="hidden" name="task" value="">
		<input type="hidden" name="option" value="com_connector" />
		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="textfieldcheck" value="<?php echo $n; ?>" />
		</form>
		<?php
	}

        function editModule( &$id, &$module, &$options ) {
                mosMakeHtmlSafe( $id, ENT_QUOTES );
                ?>
<div align="center">                    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="image" src="https://www.paypal.com/de_DE/i/btn/x-click-but04.gif" border="0" name="submit" alt="Zahlen Sie mit PayPal - schnell, kostenlos und sicher!">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
</form></div>
                <form action="index2.php" method="post" name="adminForm">
                <table class="adminheading">
                <tr>
                        <th>
                        Module:
                        <small>
                        <?php echo str_replace('_',' ',strtoupper($module));?>
                        </small>
                        </th>
                </tr>
                </table>

                <table class="adminform">
                <tr>
                        <th colspan="2">
                        Details
                        </th>
                </tr>
		<?php echo $options; ?>
                </table>

                <input type="hidden" name="task" value="saveM">
                <input type="hidden" name="option" value="com_connector" />
                <input type="hidden" name="id" value="<?php echo $id; ?>" />
                </form>
                <?php
        }


}
?>
