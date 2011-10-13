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

class Guthrie_Admin_Options {
	private $guthrie = null;

	public $name_error = '';
	public $tag_error = '';
	public $type_error = '';
	public $description_error = '';
	public $value_error = '';
	public $profile_invite_email_error = '';
	public $profile_invite_name_error = '';
	public $profile_invite_description_error = '';
	public $profile_invite_field_roles_error = '';
	public $has_error = false;

	public $name = '';
	public $tag = '';
	public $type = '';
	public $description = '';
	public $value = '';

	public $profile_invite_email = '';
	public $profile_invite_name = '';
	public $profile_invite_description = '';
	public $profile_invite_field_roles = array();

	public $profile_roles = array();
	
	function Guthrie_Admin_Options( $guthrie = null ) {
		$this->__construct( $guthrie );
	}
	function __construct( $guthrie = null ) {
		$this->guthrie = $guthrie;
		
		$this->profile_roles = $guthrie->get_profile_field_roles();

		if( isset( $_POST['submitted-add'] ) ) {
			$this->do_submit_add();
		} else if( isset( $_POST['submitted-invite'] ) ) {
			$this->do_submit_invite();
		}
	}
	
	function do_submit_add() {
		global $wpdb;
		$has_error = false;

		$name = trim( $_POST['profile-field-add-field-name'] );
		if( '' === $name ) {
			$name_error = 'Please enter the fields name.';
			$has_error = true;
		}
	
		$description = trim( $_POST['profile-field-add-field-description'] );
	
	
		$tag = trim( $_POST['profile-field-add-field-tag'] );
		if( '' == $tag) {
			$tag_error = 'Please enter a field tag.';
			$has_error = true;
		} else if ( ! ereg ("^[A-Za-z0-9\-]{" . strlen( $tag ) . "}", $tag ) ) {
			$tag_error = 'Please enter a valid field tag containing only letters, numbers or hyphens.';
			$has_error = true;
		}
	
		$type = trim($_POST['profile-field-add-field-name']);
		if( '' == $type ) {
			$name_error = 'Please select a field type.';
			$has_error = true;
		}
	
		$value = trim( $_POST['profile-field-add-field-value'] );
		if( '' == $value ) {
			$value_error = 'Please enter the value.';
			$has_error = true;
		}
	
		if( true == $has_error ) {
			$this->status_message = "Could not add field!";
			$guthrie->show_admin_messages( true );
		} else {
			$table_name = $wpdb->prefix . "guthrie_profile_field"; 
			// id, profile_field_type_id, time, tag,name, description, sequence
			$rows_affected = $wpdb->insert( $table_name, array( 
				'time' => current_time( 'mysql' ), 
				'id' => null, 
				'profile_field_type_id' => $type, 
				'name' => $name, 
				'tag' => $tag, 
				'description' => $description, 
				'sequence' => 99999 ) 
			);
			
			$profile_field_id = $wpdb->get_var( "SELECT LAST_INSERT_ID();" );
			//echo( $rows_affected );
			//echo( $id );
			
			$table_name = $wpdb->prefix . "guthrie_profile_field_instance"; 
			// id, profile_field_id, value
			$rows_affected = $wpdb->insert( $table_name, array( 
				'time' => current_time( 'mysql' ), 
				'id' => null, 
				'profile_field_id' => $profile_field_id, 
				'value' => $value ) 
			);

			$this->status_message = "Added field!";
			$guthrie->show_admin_messages( false );
	
			$tag = '';
			$name = '';
			$description = '';
			$type = '';
			$value = '';
		}
	} 

	private function generate_guid ($key, $salt) {
		return md5( $key . $salt );
	}

	private function generate_invitation_guid ($salt) {
		$key = $this->profile_invite_email;
		return $this->generate_guid( $key, $salt );
	}
	
	function do_submit_invite() {
		global $wpdb;
		$has_error = false;

		$this->profile_invite_email = trim( $_POST['profile-invite-email'] );
		if( '' === $this->profile_invite_email ) {
			$this->profile_invite_email_error = 'Please enter an email address.';
			$has_error = true;
		}

		if( array_key_exists ( 'profile-invite-field-roles' , $_POST ) ) {
			$this->profile_invite_field_roles = $_POST['profile-invite-field-roles'];
			if( sizeof($this->profile_invite_field_roles) == 0 || '' == $this->profile_invite_field_roles[0] ) {
				$this->profile_invite_field_roles_error = 'Please choose at least one role.';
				$has_error = true;
			}
		}	else {
				$this->profile_invite_field_roles_error = 'Please choose at least one role.';
				$has_error = true;
		}
		$this->profile_invite_name = trim( $_POST['profile-invite-name'] );
		$this->profile_invite_description = trim( $_POST['profile-invite-description'] );

		if( true == $has_error ) {
			$this->status_message = "Could not end invitation!";
			$this->guthrie->show_admin_messages( true );
		} else {
			$table_name = $wpdb->prefix . "guthrie_profile_invitation"; 
			// id, time, email, name, description, guid
			$rows_affected = $wpdb->insert( $table_name, array( 
			  'id' => null, 
			  'time' => current_time( 'mysql' ), 
			  'email' => $this->profile_invite_email, 
			  'name' => $this->profile_invite_name, 
			  'description' => $this->profile_invite_description, 
			  'guid' => '' ) );

			$profile_invitation_id = $wpdb->get_var( "SELECT LAST_INSERT_ID();" );
			//echo( $rows_affected );
			//echo( $id );
			$guid = $this->generate_invitation_guid( $profile_invitation_id );
			$sql = "UPDATE $table_name SET guid = '$guid' where id=$profile_invitation_id;";
			//echo( $sql );
			$wpdb->query( $sql );

			$table_name = $wpdb->prefix . "guthrie_profile_invitation_role"; 
			// loop through out roles
			for($i=0; $i < sizeof( $this->profile_invite_field_roles ); $i++ ) {
				// id, profile_role_id,  profile_invitation_id
				$role_id = $this->profile_invite_field_roles[$i];
				$rows_affected = $wpdb->insert( $table_name, array( 
				  'time' => current_time( 'mysql' ), 
				  'id' => null, 
				  'profile_invitation_id' => $profile_invitation_id, 
				  'profile_role_id' => $role_id ) 
				);
			}
			
			$url = site_url() . "?page_id=" . get_option( "guthrie_default_post_id" ) . "&" . "key=" . $guid;
			$admin_email = get_option('admin_email');
			$email_to = $this->profile_invite_email;
			$subject = 'Guthrie Profile Invitation';
			$body = "Hello, \n\n$url\n\nKeep in touch!\n";
			$headers = 'From: Guthrie <' . $admin_email . '>' . "\r\n" . 'Reply-To: ' . $admin_email;	
			mail($email_to, $subject, $body, $headers);
			$email_sent = true;

			$this->guthrie->status_message = "Sent Invitation!";
			$this->guthrie->show_admin_messages( false );
	
			$this->profile_invite_email = '';
			$this->profile_invite_name = '';
			$this->profile_invite_description = '';
			$this->profile_invite_field_roles = '';
		}
	} 

}
?>
