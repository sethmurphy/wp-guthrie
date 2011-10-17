<?php
/*
Manage our roles
*/
?>
<?php 
global $guthrie;
require_once( 'admin_options.php' );
$admin_options = new Guthrie_Admin_Options( $guthrie );	
$profile_roles = $guthrie->get_profile_field_roles();
?>
<h3 class="manage-roles">Manage Roles</h3>

