<?php
/**
 * @package GoodSearchZen
 * @copyright Copyright 2011 A. Justo, www.AJweb.eu
 * @copyright Portions Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version Good Search Zen 1.0
 * @todo Msg on activation/deactivation. Translations. Move to Configuration menu. Activate the fulltext search on admin site.
 */

 error_reporting(E_ALL);
 ini_set('display_errors', 'On');

require('includes/application_top.php');

$goodZenSearch_Files = array(
							DIR_FS_ADMIN.'goodSearchZen.php',
							DIR_FS_ADMIN.DIR_WS_INCLUDES.'extra_datafiles/goodSearchZen.php',
							DIR_FS_ADMIN.DIR_WS_BOXES.'extra_boxes/goodSearchZen_catalog_dhtml.php',
							DIR_FS_CATALOG.DIR_WS_CLASSES.'observers/class.goodSearchZen.php',
							DIR_FS_CATALOG.DIR_WS_INCLUDES.'auto_loaders/config.goodSearchZen.php'
							);
$goodZenSearch_Msg = array();

$action = !isset($_GET['action']) ? 'check' : $_GET['action'];

switch($action):
 	case 'install': 
 		if( goodSearchZen_Install() ) $goodZenSearch_Msg[] = 'Good Search Zen Installation: Everything OK.';
 		else $goodZenSearch_Msg[] = 'Good Search Installation: PROBLEMS FOUND. Please retry the installation or 
 									manually check the indexes on the '.TABLE_PRODUCTS_DESCRIPTION.' table';
 		break;
 	
 	case 'activation':
 		if( $_GET['status'] === '1' ) goodSearchZen_ActivationToogle(1);
 		elseif( $_GET['status'] === '0' ) goodSearchZen_ActivationToogle(0);
 		break;
 		
 	case 'uninstall':
 		if( goodSearchZen_Uninstall() ) {
 			$goodZenSearch_Msg[] = 'Good Search Zen Uninstallation: The DB has been cleaned. 
 									You should now remove the files associated with this contribution:';
 			foreach( $goodZenSearch_Files as $file ) {
 				$goodZenSearch_Msg[] = $file;
 			}
 		}
 		else $goodZenSearch_Msg[] = 'Good Search Zen Uninstallation: There has been a problem trying to clean the DB. Please try again. 
 									If the problem continues, you may want to do the cleaning yourself with these SQL statements:<br/>
 									<code>DROP INDEX fulltextsearch_name ON products_description;<br/>
						 			DROP INDEX fulltextsearch_description ON products_description;</code>';
 		break;
		
	 case 'check':
 		$check=true;
 		if( !goodSearchZen_CheckDB() ) {
 			$goodZenSearch_Msg[] = 'Good Search Zen Installation check: The database is not ready. 
 									Please run the GoodSearch installation.';
 			$check=false;
 		}
 		else $goodZenSearch_Msg[] = 'Good Search Zen Installation check: Database is ready.';
 		
 		if( !goodSearchZen_CheckFiles() ) {
 			$goodZenSearch_Msg[] = 'Good Search Zen Installation check: Missing files. 
 									Please check your installation of GoodSearch 
 									making sure you upload all files to their proper places.';
 			$check=false;
 		}
 		else $goodZenSearch_Msg[] = 'Good Search Zen Installation check: Files are installed correctly.';
 		
 		if( $check===true ) $goodZenSearch_Msg[] = 'Good Search Zen Installation check: Everything OK.';
 		break;
endswitch;
 

function goodSearchZen_Install() {
 	global $db;
 	
 	// if no indexes found, run create queries
 	if( !goodSearchZen_CheckDB_indexProductsName() ) {
	 	$createNameIndexSQL = "CREATE FULLTEXT INDEX fulltextsearch_name ON ".TABLE_PRODUCTS_DESCRIPTION."(products_name)";	
		$db->Execute($createNameIndexSQL);	
	}
	if( !goodSearchZen_CheckDB_indexProductsDesc() ) {
		$createDescIndexSQL = "CREATE FULLTEXT INDEX fulltextsearch_description ON ".TABLE_PRODUCTS_DESCRIPTION."(products_description)";	
		$db->Execute($createDescIndexSQL);	
	}
	
	if( !goodSearchZen_CheckDB_configurationEntry() )	 {
		goodSearchZen_ActivationInstall();
	}
				
	// check again if indexes have been correctly added
	if( !goodSearchZen_Check() ) return false;

 	return true;
 }
 
function goodSearchZen_ActivationInstall() {
	global $db;
    $installSQL = "INSERT INTO " . TABLE_CONFIGURATION . 
    				" (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, 
    				sort_order,  last_modified)
    				 VALUES ('Good Search Zen Activation State', 'GOOD_SEARCH_ZEN_ACTIVE_STATE', '1', 'Activate Good Search Zen?', 
    				 '0', '0', now())";
	$db->Execute($installSQL);
}

function goodSearchZen_ActivationToogle($activation=1) {
	global $db;
    $db->Execute("UPDATE " . TABLE_CONFIGURATION . 
    				" SET configuration_value='$activation' WHERE configuration_key='GOOD_SEARCH_ZEN_ACTIVE_STATE'");
}

function goodSearchZen_checkActive() {
	global $db;
    $check_query = $db->Execute("SELECT * FROM " . TABLE_CONFIGURATION . 
    				" WHERE configuration_key='GOOD_SEARCH_ZEN_ACTIVE_STATE' AND configuration_value=1");

    return $check_query->RecordCount();
}
 
function goodSearchZen_Check() {
	if( !goodSearchZen_CheckDB() or !goodSearchZen_CheckFiles() ) return false;
	return true;		
}

function goodSearchZen_CheckDB() {
	global $db;
	$check1 = false; $check2 = false; $check3 = false;
	
	if( goodSearchZen_CheckDB_indexProductsName() ) $check1 = true;			

	if( goodSearchZen_CheckDB_indexProductsDesc() ) $check2 = true;
	
	if( goodSearchZen_CheckDB_configurationEntry() ) $check3 = true;

	if( $check1 and $check2 and $check3 ) return true;
	return false;
}

function goodSearchZen_CheckDB_indexProductsName() {
	global $db;
	// check name index
	$nameIndexSQL = "SHOW INDEX FROM ".TABLE_PRODUCTS_DESCRIPTION. 
  	" WHERE Key_name = 'fulltextsearch_name' "; 
	$checkNameIndex = $db->Execute($nameIndexSQL);
		
	if( $checkNameIndex->fields['Index_type'] == 'FULLTEXT' ) return true;
	else return false;
}

function goodSearchZen_CheckDB_indexProductsDesc() {
	global $db;
	// check description index
	$descIndexSQL = "SHOW INDEX FROM ".TABLE_PRODUCTS_DESCRIPTION. 
				  	" WHERE Key_name = 'fulltextsearch_description' ";
	$checkDescIndex = $db->Execute($descIndexSQL);
	if( $checkDescIndex->fields['Index_type'] == 'FULLTEXT' ) return true;
	else return false;
}

function goodSearchZen_CheckDB_configurationEntry() {
	global $db;
	// check presence of activation field
	$check_query = $db->Execute("SELECT * FROM " . TABLE_CONFIGURATION . 
    				" WHERE configuration_key='GOOD_SEARCH_ZEN_ACTIVE_STATE' ");
	
    if( $check_query->RecordCount() > 0 ) return true;
	else return false;
}

function goodSearchZen_CheckFiles() {
	global $goodZenSearch_Files;
	foreach( $goodZenSearch_Files as $file ) {
		if( !file_exists($file) ) return false;
	}
	return true;
}

function goodSearchZen_Uninstall() {
	global $db;
	
	if( !goodSearchZen_Check() ) return true;
	$drop_indexes_sql = 'DROP INDEX fulltextsearch_name ON products_description';
	$drop = $db->Execute($drop_indexes_sql);
	$drop_indexes_sql =	'DROP INDEX fulltextsearch_description ON products_description;';
	$drop = $db->Execute($drop_indexes_sql);
	$remove_activation_status_field_sql = 'DELETE FROM configuration WHERE configuration_key="GOOD_SEARCH_ZEN_ACTIVE_STATE" ';
	$remove_activation = $db->Execute($remove_activation_status_field_sql);
	
	if( !goodSearchZen_Check() ) return true;
	return false;
}
 
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
<script language="javascript" src="includes/menu.js"></script>
<script language="javascript" src="includes/general.js"></script>
<script type="text/javascript">
<!--
function init()
{
   cssjsmenu('navbar');
   if (document.getElementById)
   {
     var kill = document.getElementById('hoverJS');
     kill.disabled = true;
   }
 }

function popupWindow(url) {
  window.open(url, 'popupWindow', 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, copyhistory=no');
}

var browser_family;
var up = 1;

if (document.all && !document.getElementById)
  browser_family = "dom2";
else if (document.layers)
  browser_family = "ns4";
else if (document.getElementById)
  browser_family = "dom2";
else
  browser_family = "other";

-->
</script>
<style type="text/css">

#goodSearchZen {
	padding-left: 2em;
}

ul li {
		margin-bottom: 2em;
}

li p {
	margin-top: 0;
}

.message {
	padding: 1em;
	background-color: #F8D080;
	font-weight: bold;
}
</style>
</head>
<body onload="init()" id="salesChart">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!--  goodSearchZen -->
<div id="goodSearchZen">
	<h2 class="pageHeading">Good Search Zen Install/Uninstall and Options</h2>
	
	<?php if( !empty($goodZenSearch_Msg) ): ?>
	<div class="message">
		<?php foreach( $goodZenSearch_Msg as $msg ): ?>
			<p>
				<?php echo $msg; ?>
			</p>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
	
	<h3>Activation</h3>
	<form action="" method="GET" name="activateGoodSearchZen">
		<p>
			<input type="hidden" name="action" value="activation" />
			<input type="radio" name="status" id="activate" value="1" <?php if( goodSearchZen_checkActive() ) echo 'checked="checked"'; ?> /><label for="activate">Active</label>
			<input type="radio" name="status" id="deactivate" value="0" <?php if( !goodSearchZen_checkActive() ) echo 'checked="checked"'; ?> /><label for="deactivate">Inactive</label>
		</p>
		<p>
			<input type="submit" value="Change" />
		</p>
	</form>
	
	<h3>Install, Unistall, or Check status</h3>
	<ul>
		<li><a href="?action=install">Install Good Search Zen</a>
			<p>
				This will add an option to the Zencart configuration table and create two indexes on the <?php echo TABLE_PRODUCTS_DESCRIPTION; ?> table.
				<br/>These are not risky operations by themselfs, but as with any database change, 
				you are <strong>strongly advice to backup your database before proceeding.</strong>
			</p>
			<p>This operation may take some minutes on a large catalog. Please leave this window open and wait until the operation is finished. </p>
		</li>
		<li><a href="?action=uninstall">Uninstall the database changes</a>
			<p>
				This will remove the changes made on the Zencart configuration table and drop two indexes created on the <?php echo TABLE_PRODUCTS_DESCRIPTION; ?> table.
				<br/>These are not risky operations by themselfs, but as with any database change, 
				you are <strong>strongly advice to backup your database before proceeding.</strong>
			</p>
		</li>
		<li><a href="?action=check">Check if the components (files and database changes) are correctly installed</a></li>
		
	</ul>

</div><!--  eof goodSearchZen -->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>