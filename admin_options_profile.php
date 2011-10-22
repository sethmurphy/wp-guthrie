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

class Guthrie_Admin_Options_Profile {
	private $guthrie = null;
	
	public $name_error = '';
	public $tag_error = '';
	public $type_error = '';
	public $description_error = '';
	public $value_error = '';
	public $roles_error = '';

	public $name = '';
	public $tag = '';
	public $type = '';
	public $description = '';
	public $value = '';
	public $roles = array();

	public $profile_roles = array();

	function factory( $guthrie = null ) {
		return new Guthrie_Admin_Options_Profile( $guthrie );
	}

	function Guthrie_Admin_Options_Profile( $guthrie = null ) {
		$this->__construct( $guthrie );
	}

	function __construct( $guthrie = null ) {
		$this->guthrie = $guthrie;
		if( isset( $_POST[ 'profile-field-add-submitted' ] ) ) {
			$this->do_submit();
		}
	}

	function do_submit() {
		global $wpdb;
		$has_error = false;

		$this->name = trim( $_POST[ 'profile-field-add-field-name' ] );
		if( '' === $this->name ) {
			$this->name_error = 'Please enter the fields name.';
			$has_error = true;
		}
	
		$this->description = trim( $_POST[ 'profile-field-add-field-description' ] );
	
		$this->tag = trim( $_POST[ 'profile-field-add-field-tag' ] );
		if( '' == $this->tag) {
			$this->tag_error = 'Please enter a field tag.';
			$has_error = true;
		} else if ( ! ereg ("^[A-Za-z0-9\-]{" . strlen( $this->tag ) . "}", $this->tag ) ) {
			$this->tag_error = 'Please enter a valid field tag containing only letters, numbers or hyphens.';
			$has_error = true;
		}
	
		$this->type = trim( $_POST[ 'profile-field-add-field-name' ]);
		if( '' == $this->type ) {
			$this->name_error = 'Please select a field type.';
			$has_error = true;
		}
	
		$this->value = trim( $_POST[ 'profile-field-add-field-value' ] );
		if( '' == $this->value ) {
			$this->value_error = 'Please enter the value.';
			$has_error = true;
		}

		if( array_key_exists ( 'profile-field-add-roles' , $_POST ) ) {
			$this->roles = $_POST[ 'profile-field-add-roles' ];
			if( sizeof( $this->roles ) == 0 || '' == $this->roles[ 0 ] ) {
				$this->roles_error = 'Please choose at least one role.';
				$has_error = true;
			}
		}	else {
				$this->roles_error = 'Please choose at least one role.';
				$has_error = true;
		}

		if( true == $has_error ) {
			$this->guthrie->status_message = "Could not add field!";
			$this->guthrie->show_admin_messages( true );
		} else {
			$table_name = $wpdb->prefix . "guthrie_profile_field"; 
			$rows_affected = $wpdb->insert( $table_name, array( 
				'time' => current_time( 'mysql' ), 
				'id' => null, 
				'profile_field_type_id' => $this->type, 
				'name' => $this->name, 
				'tag' => $this->tag, 
				'description' => $this->description, 
				'sequence' => 99999 ) 
			);
			
			$profile_field_id = $wpdb->get_var( "SELECT LAST_INSERT_ID();" );
			
			$table_name = $wpdb->prefix . "guthrie_profile_field_instance"; 
			$rows_affected = $wpdb->insert( $table_name, array( 
				'time' => current_time( 'mysql' ), 
				'id' => null, 
				'profile_field_id' => $profile_field_id, 
				'value' => $this->value, 
				'sequence' => 99999 ) 
			);

			// associate the roles for the field
			$table_name = $wpdb->prefix . "guthrie_profile_field_role"; 
			// loop through out roles
			for($i=0; $i < sizeof( $this->roles ); $i++ ) {
				$role_id = $this->roles[ $i ];
				$rows_affected = $wpdb->insert( $table_name, array( 
				  'time' => current_time( 'mysql' ), 
				  'id' => null, 
				  'profile_field_id' => $profile_field_id, 
				  'profile_role_id' => $role_id ) 
				);
			}

			$this->guthrie->status_message = "Added field!";
			$this->guthrie->show_admin_messages( false );
	
			$this->tag = '';
			$this->name = '';
			$this->description = '';
			$this->type = '';
			$this->value = '';
		}
	} 
}
?>
