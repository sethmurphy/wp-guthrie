<?php
/*
Author: Seth Murphy
Author URI: http://sethmurphy.com
License: GPL2
Copyright 2011	Seth Murphy	(email : seth@sethmurphy.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, AS 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA	02110-1301	USA
*/

class Guthrie_Admin_Options_Roles {
	private $guthrie = null;
	private $admin_options = null;
	
	public $name_error = '';
	public $description_error = '';

	public $name = '';
	public $description = '';

	public $profile_roles = array();

	function factory( $guthrie = null, $admin_options = null) {
		return new Guthrie_Admin_Options_Roles($guthrie, $admin_options);
	}

	function Guthrie_Admin_Options_Roles( $guthrie = null, $admin_options = null ) {
		$this->__construct( $guthrie, $admin_options );
	}

	function __construct( $guthrie = null, $admin_options = null ) {
		$this->guthrie = $guthrie;
		$this->admin_options = $admin_options;
		if( isset( $_POST['submitted'] ) ) {
			$this->do_submit();
		}
	}

	public function generate_profile_url( $role_id ) {
		$url = site_url() . "?page_id=" . get_option( "guthrie_default_post_id" );
		if ( isset( $role_id ) && '' != $role_id ) {
			$url .= "&" . "role_id=" . $role_id;
		}
		return $url;
	}

	function do_submit() {
		global $wpdb;
		$has_error = false;

		$this->name = trim( $_POST['profile-add-role-name'] );
		if( '' === $this->name ) {
			$this->name_error = 'Please enter the roles name.';
			$has_error = true;
		}
	
		$this->description = trim( $_POST['profile-add-role-description'] );
	
		if( true == $has_error ) {
			$this->status_message = "Could not add role!";
			$guthrie->show_admin_messages( true );
		} else {
			$table_name = $wpdb->prefix . "guthrie_profile_role"; 
			// id, profile_field_type_id, time, tag,name, description, sequence
			$rows_affected = $wpdb->insert( $table_name, array( 
				'time' => current_time( 'mysql' ), 
				'id' => null, 
				'name' => $this->name, 
				'description' => $this->description, 
				'sequence' => 99999 ) 
			);

			$this->guthrie->status_message = "Added role!";
			$this->guthrie->show_admin_messages( false );
	
			$this->name = '';
			$this->description = '';
		}
	} 
}
