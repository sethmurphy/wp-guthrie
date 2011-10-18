<?php
/*
Template Name: Profiles
*/
?>
<?php 
	global $guthrie;
	require_once( 'admin_options.php' );
	$admin_options = new Guthrie_Admin_Options( $guthrie );	
	
	$tab = $admin_options->current_tab;
?>
<div class="wrap">
	<?php echo($admin_options->tabs()); ?>
	<?php require("admin_options_" . $tab . "_page.php"); ?>
</div>
