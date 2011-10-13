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

class Guthrie_Ajax {
	private $guthrie = null;
	function Guthrie_Ajax( $guthrie = null ) {
		$this->__construct( $guthrie );
	}
	function __construct( $guthrie = null ) {
		$this->guthrie = $guthrie;
	}

	function update_profile_field_sequence() {
		$success = false;
		$debug = '';
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$original_field_instance_id = '';
		$original_field_instance_index = '';
		$field_instance_id = '';
		$field_instance_index = '';

		if( array_key_exists ( 'original_field_instance_id' , $_POST ) &&
		    array_key_exists ( 'original_field_instance_index' , $_POST ) &&
		    array_key_exists ( 'field_instance_id' , $_POST ) &&
		    array_key_exists ( 'field_instance_index' , $_POST )) {

			$original_field_instance_id = $_POST[ 'original_field_instance_id' ];
			$original_field_instance_index = $_POST[ 'original_field_instance_index' ];
			$field_instance_id = $_POST[ 'field_instance_id' ];
			$field_instance_index = $_POST[ 'field_instance_index' ];

			$table_name = $wpdb->prefix . "guthrie_profile_field_instance";

			// select all our field instances in order
			$profile_field_instances = $this->guthrie->get_profile_field_instances();
			$sequence = 10;

			foreach ( $profile_field_instances as $field_instance ) {
				if( $original_field_instance_id != $field_instance->id ){
					if( $field_instance_id == $field_instance->id ) {
						if ( $original_field_instance_id > $field_instance_index ) {
							// place before
							$sql = "UPDATE " . $table_name . " SET `sequence` = '$sequence' WHERE id = $original_field_instance_id";
							//echo($sql);
							//echo("\n");
							$wpdb->query( $sql );
							$sequence = $sequence + 10;

							$sql = "UPDATE " . $table_name . " SET `sequence` = '$sequence' WHERE id = $field_instance_id";
							//echo($sql);
							//echo("\n");
							$wpdb->query( $sql );
						} else {
							// place after
							$sql = "UPDATE " . $table_name . " SET `sequence` = '$sequence' WHERE id = $field_instance_id";
							//echo($sql);
							//echo("\n");
							$wpdb->query( $sql );
							$sequence = $sequence + 10;
	
							$sql = "UPDATE " . $table_name . " SET `sequence` = '$sequence' WHERE id = $original_field_instance_id";
							//echo($sql);
							//echo("\n");
							$wpdb->query( $sql );
						}						
						$success = true;
						$sequence = $sequence + 10;
					} else {
						$sql = "UPDATE " . $table_name . " SET `sequence` = '$sequence' WHERE id = $field_instance->id";
						//echo($sql);
						//echo("\n");
						$wpdb->query( $sql );
						$success = true;
						$sequence = $sequence + 10;
					}
				}
			}
		}

		// generate the response
		$response = json_encode( array( 'update_profile_field_sequence' => $success,
		                                 'original_field_instance_id' => $original_field_instance_id,
		                                 'original_field_instance_index' => $original_field_instance_index,
		                                 'field_instance_id' => $field_instance_id,
		                                 'field_instance_index' => $field_instance_index ) );
		// response output
		header( "Content-Type: application/json" );
		echo $debug;
		echo $response;
		// IMPORTANT: don't forget to "exit"
		exit;
	}

	function update_profile_field_value() {
		$success = false;
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$element_id = '';
		$value = '';
		$field_instance_id = '';
		$original_value = '';
		if( array_key_exists ( 'element_id' , $_POST ) &&
		    array_key_exists ( 'value' , $_POST ) &&
		    array_key_exists ( 'field_instance_id' , $_POST ) &&
		    array_key_exists ( 'original_value' , $_POST )) {
			$element_id = $_POST['element_id'];
			$value = $_POST['value'];
			$field_instance_id = $_POST[ 'field_instance_id' ];
			$original_value = $_POST[ 'original_value' ];
			$table_name = $wpdb->prefix . "guthrie_profile_field_instance";
			$sql = "UPDATE " . $table_name . " SET `value` = '$value' WHERE id = $field_instance_id";
			//echo($sql);
			$wpdb->query( $sql );
			$success = true;
		}

		// generate the response
		$response = json_encode( array( 'update_profile_field_value' => $success,
		                                 'element_id' => $element_id,
		                                 'value' => $value,
		                                 'field_instance_id' => $field_instance_id,
		                                 'original_value' => $original_value ) );

		// response output
		header( "Content-Type: application/json" );
		echo $response;
		
		// IMPORTANT: don't forget to "exit"
		exit;
	}

	function update_profile_field_roles() {
		$success = false;
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		if( array_key_exists ( 'field_roles' , $_POST ) ) {
			$field_roles = $_POST[ 'field_roles' ];
			$field_id = $_POST[ 'field_id' ];
			$element_id = $_POST[ 'element_id' ];
			
			// get the existing field_roles from the database
			$sql = "SELECT id as profile_field_role_id, profile_field_id, profile_role_id " .
			       "FROM " . $wpdb->prefix . "guthrie_profile_field_role AS fr " .
			       "where fr.profile_field_id=" . $field_id . ";";
			
			$profilefieldroles = $wpdb->get_results($sql, OBJECT);
			
			if( sizeof( $profilefieldroles ) > sizeof( $field_roles ) ) {
				// we need to remove a record from the DB
				//loop through profileroles in DB
				foreach ( $profilefieldroles as $profilefieldrole) {
					// see if we should still be selected
					$exists = false;
					$profile_role_id = $profilefieldrole->profile_role_id;
					foreach ( $field_roles as $role ){
						if ( $role == $profile_role_id ) {
							$exists = true;
						}
					}
					// remove our record if neccessary
					if( !$exists ) {
						$table_name = $wpdb->prefix . "guthrie_profile_field_role";
						$sql = "DELETE FROM " . $table_name . " WHERE id = $profilefieldrole->profile_field_role_id;";
						$wpdb->query( $sql );
						$success = true;
					}
				}
			} else {
				// we need to add a record to the DB
				//loop through roles in DB
				foreach ( $field_roles as $role) {
					// see if we should still be selected
					$exists = false;
					foreach ( $profilefieldroles as $profilefieldrole) {
						$profile_role_id = $profilefieldrole->profile_role_id;
						if ( $role == $profile_role_id ) {
							$exists = true;
						}
					}
					// add a record if neccessary
					if( !$exists ) {
						$table_name = $wpdb->prefix . "guthrie_profile_field_role";
						$rows_affected = $wpdb->insert( $table_name, array( 'time' => current_time('mysql' ), 'id' => null, 'profile_field_id' => $field_id, 'profile_role_id' => $role) );
						$wpdb->query( $sql );
					}
				}
				$success = true;
			}
		} else {
			// remove all roles
			if( array_key_exists ( 'field_id' , $_POST ) ) {
				$field_id = $_POST['field_id'];
				$table_name = $wpdb->prefix . "guthrie_profile_field_role";
				$sql = "DELETE FROM " . $table_name . " WHERE profile_field_id = $field_id;";
				$wpdb->query( $sql );
				$success = true;
			}
		}
		// generate the response
		$response = json_encode( array( 'update_profile_field_roles_success' => $success ) );
 
		// response output
		header( "Content-Type: application/json" );
		echo $response;
		
		// IMPORTANT: don't forget to "exit"
		exit;
	}

	function remove_profile_field_instance() {
		$success = false;
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$element_id = '';
		$value = '';
		$field_instance_id = '';
		$original_value = '';
		if( array_key_exists ( 'field_instance_id' , $_POST ) &&
		    array_key_exists ( 'field_instance_index' , $_POST ) ) {
			$field_instance_id = $_POST[ 'field_instance_id' ];
			$field_instance_index = $_POST[ 'field_instance_index' ];
			$table_name = $wpdb->prefix . "guthrie_profile_field_instance";
			$sql = "DELETE FROM " . $table_name . " WHERE id = $field_instance_id LIMIT 1";
			//echo($sql);
			$wpdb->query( $sql );
			$success = true;
		}
		$response = json_encode( array( 'remove_profile_field_instance_success' => $success ) );

		// response output
		header( "Content-Type: application/json" );
		echo $response;

		// IMPORTANT: don't forget to "exit"
		exit;
	}
}
?>