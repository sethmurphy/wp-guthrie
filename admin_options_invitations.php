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

class Guthrie_Admin_Options_Invitations {
	private $guthrie = null;
	private $admin_options = null;

	public $email_error = '';
	public $name_error = '';
	public $description_error = '';
	public $roles_error = '';

	public $email = '';
	public $name = '';
	public $description = '';
	public $roles = array();
	
	function factory( $guthrie = null, $admin_options = null) {
		return new Guthrie_Admin_Options_Invitations($guthrie, $admin_options);
	}

	function Guthrie_Admin_Options_Invitations( $guthrie = null, $admin_options = null ) {
		$this->__construct( $guthrie, $admin_options );
	}

	function __construct( $guthrie = null, $admin_options = null ) {
		$this->guthrie = $guthrie;
		$this->admin_options = $admin_options;
		if( isset( $_POST['profile-invite-submitted'] ) ) {
			$this->do_submit();
		}
	}
	
	private function generate_guid ($key, $salt) {
		return md5( $key . $salt );
	}

	private function generate_invitation_guid ($salt) {
		$key = $this->email;
		return $this->generate_guid( $key, $salt );
	}
	
	public function generate_profile_url( $guid = null ) {
		$url = site_url() . "?page_id=" . get_option( "guthrie_default_post_id" );
		if ( isset( $guid ) && '' != $guid ) {
			$url .= "&" . "key=" . $guid;
		}
		return $url;
	}

	function do_submit() {
		global $wpdb;
		$has_error = false;

		// validate submitted values server side
		$this->email = trim( $_POST['profile-invite-email'] );
		if( '' === $this->email ) {
			$this->email_error = 'Please enter an email address.';
			$has_error = true;
		}

		if( array_key_exists ( 'profile-invite-roles' , $_POST ) ) {
			$this->roles = $_POST['profile-invite-roles'];
			if( sizeof($this->roles) == 0 || '' == $this->roles[0] ) {
				$this->roles_error = 'Please choose at least one role.';
				$has_error = true;
			}
		}	else {
				$this->roles_error = 'Please choose at least one role.';
				$has_error = true;
		}
		$this->name = trim( $_POST['profile-invite-name'] );
		$this->description = trim( $_POST['profile-invite-description'] );

		if( true == $has_error ) { 
			// we failed validation
			$this->guthrie->status_message = "Could not send invitation!";
			$this->guthrie->show_admin_messages( true );
		} else { 
			// good to go, get to work
			$table_name = $wpdb->prefix . "guthrie_profile_invitation"; 
			// insert our invitation without the unique key
			$rows_affected = $wpdb->insert( $table_name, array( 
			  'id' => null, 
			  'time' => current_time( 'mysql' ), 
			  'email' => $this->email, 
			  'name' => $this->name, 
			  'description' => $this->description, 
			  'guid' => '' ) );

			// generate the key using the DB id from the insert
			$profile_invitation_id = $wpdb->get_var( "SELECT LAST_INSERT_ID();" );
			$guid = $this->generate_invitation_guid( $profile_invitation_id );
			$sql = $wpdb->prepare( "UPDATE $table_name SET guid = %s where id = %d;", $guid, $profile_invitation_id );
			$wpdb->query( $sql );

			// associate the roles for the invitation
			$table_name = $wpdb->prefix . "guthrie_profile_invitation_role"; 
			// loop through out roles
			for($i=0; $i < sizeof( $this->roles ); $i++ ) {
				$role_id = $this->roles[ $i ];
				$rows_affected = $wpdb->insert( $table_name, array( 
				  'time' => current_time( 'mysql' ), 
				  'id' => null, 
				  'profile_invitation_id' => $profile_invitation_id, 
				  'profile_role_id' => $role_id ) 
				);
			}
			
			// construct and send the invitation email
			$url = $this->generate_profile_url( $guid );
			$admin_email = get_option('admin_email');
			$email_to = $this->email;
			$subject = 'Guthrie Profile Invitation';
			$body = "Hello, \n\n$url\n\nKeep in touch!\n";
			$headers = 'From: Guthrie <' . $admin_email . '>' . "\r\n" . 'Reply-To: ' . $admin_email;				
			mail($email_to, $subject, $body, $headers);
			$email_sent = true;

			// set our success message for the user
			$this->guthrie->status_message = "Sent Invitation!";
			$this->guthrie->show_admin_messages( false );
	
			// clear our submitted values on success so they are not used to re-populate the form
			$this->email = '';
			$this->name = '';
			$this->description = '';
			$this->roles = '';
		}
	} 
}
?>


