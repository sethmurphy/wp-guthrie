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

class Guthrie_Install {
	
	private $TABLE_NAMES = array( 
	  "profile_field",
	  "profile_field_role",
	  "profile_field_type",
	  "profile_invitation",
	  "profile_invitation_role",
	  "profile_role",
	  "profile_field_instance"
	);


	function Guthrie_Install() {
		$this->__construct();
	}

	function __construct() {
	}


	/**********************************************************************
	 *  create the default page for profiles
	 **********************************************************************/
	function create_default_guthrie_profile_page() {
		global $user_ID;
		$pagename = 'my-guthrie';
		$page['post_type']    = 'page';
		$page['post_content'] = '[GUTHRIE]';
		$page['post_parent']  = 0;
		$page['post_author']  = $user_ID;
		$page['post_status']  = 'publish';
		$page['post_title']   = 'My Guthrie';
		$page = apply_filters('guthrie_add_new_page', $page, $pagename);
		$pageid = wp_insert_post ($page);
		if ( $pageid == 0 ) { 
			/* Add Page Failed */ 
		} else { 
			add_option( "guthrie_default_post_id", $pageid, false, false ); 
			add_option( "guthrie_default_page_name", $pagename, false, false ); 
			add_option( "guthrie_show_profile_page", 'false', false, false );
		}
	}
	
	/**********************************************************************
	 *  Remove the default page for profiles and the option to hold the post_id
	 **********************************************************************/
	function delete_default_guthrie_profile_page() {
		wp_delete_post( get_option( "guthrie_default_post_id" ), true ); 
		delete_option( "guthrie_default_post_id" );
		delete_option( "guthrie_default_page_name" );
		delete_option( "guthrie_show_profile_page" );
	}

	/**********************************************************************
	 ** Creates the database structure
	 ** Safe to call anytime AS no action is taken if table already exists
	 **********************************************************************/
	function create_database() {
		 global $wpdb;
		 require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	
		$table_name = $wpdb->prefix . "guthrie_profile_field_instance"; 
		if ( $wpdb->get_var( "show tables like '$table_name'" ) != $table_name ) {
			$sql = "CREATE TABLE " . $table_name . " (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			profile_field_id mediumint(9) NOT NULL,
			time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			value text NOT NULL,
			sequence mediumint(9) NOT NULL,
			PRIMARY KEY	(id),
			KEY r_field_id (profile_field_id),
			KEY r_sequence (sequence)
			);";
			dbDelta( $sql );
		 }
	
		$table_name = $wpdb->prefix . "guthrie_profile_field_type"; 
		if ( $wpdb->get_var( "show tables like '$table_name'" ) != $table_name ) {
			$sql = "CREATE TABLE " . $table_name . " (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			name tinytext NOT NULL,
			description text NOT NULL,
			PRIMARY KEY	(id)
			);";
			dbDelta( $sql );
		 }
	
		$table_name = $wpdb->prefix . "guthrie_profile_role"; 
		if ( $wpdb->get_var( "show tables like '$table_name'" ) != $table_name ) {
			$sql = "CREATE TABLE " . $table_name . " (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			name tinytext NOT NULL,
			description text NOT NULL,
			sequence mediumint(9) NOT NULL,
			PRIMARY KEY	(id),
			KEY r_sequence (sequence)
			);";
			dbDelta( $sql );
		 }
	
		 $table_name = $wpdb->prefix . "guthrie_profile_field"; 
		 if ( $wpdb->get_var( "show tables like '$table_name'" ) != $table_name ) {
				$sql = "CREATE TABLE " . $table_name . " (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				profile_field_type_id mediumint(9) NOT NULL,
				time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				tag tinytext,
				name tinytext NOT NULL,
				description text NOT NULL,
				sequence mediumint(9) NOT NULL,
				PRIMARY KEY	(id),
				KEY r_profile_field_type_id (profile_field_type_id),
				KEY r_sequence (sequence)
				);";
				dbDelta( $sql );
			}
	
		 $table_name = $wpdb->prefix . "guthrie_profile_field_role"; 
		 if ( $wpdb->get_var( "show tables like '$table_name'" ) != $table_name ) {
			 $sql = "CREATE TABLE " . $table_name . " (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				profile_field_id mediumint(9) NOT NULL,
				profile_role_id mediumint(9) NOT NULL,
				time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				sequence mediumint(9) NOT NULL,
				PRIMARY KEY	(id),
				KEY r_sequence (sequence),
				UNIQUE KEY r_field_role (profile_field_id, profile_role_id),
				KEY r_field (profile_field_id),
				KEY r_role (profile_role_id)
				);";
		
				dbDelta( $sql );
			}
	
			$table_name = $wpdb->prefix . "guthrie_profile_invitation"; 
			if ( $wpdb->get_var( "show tables like '$table_name'" ) != $table_name ) {
			 $sql = "CREATE TABLE " . $table_name . " (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				name tinytext NOT NULL,
				description text NOT NULL,
				email text NOT NULL,
				guid text NOT NULL,
				PRIMARY KEY	(id),
				KEY r_name (name(4)),
				KEY r_email (email(4)),
				KEY r_guid (guid(4))
				);";
				dbDelta( $sql );
			}
	
			$table_name = $wpdb->prefix . "guthrie_profile_invitation_role"; 
			if ( $wpdb->get_var( "show tables like '$table_name'" ) != $table_name ) {
			 $sql = "CREATE TABLE " . $table_name . " (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				profile_invitation_id mediumint(9) NOT NULL DEFAULT 1,
				profile_role_id mediumint(9) NOT NULL DEFAULT 1,
				time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				PRIMARY KEY	(id),
				UNIQUE KEY r_invitation_role (profile_invitation_id, profile_role_id),
				KEY r_invitation (profile_invitation_id),
				KEY r_role (profile_role_id)
				);";
				dbDelta( $sql );
			}
	}
	
	
	/* default values needed to get started */
	function populate_database_defaults () {
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	
		$DEFAULT_PROFILE_FIELD_TYPES = array( // id, name, description
		                                      array(1, "Text", "Any Text/HTML" )
		                                    );
	
		$DEFAULT_PROFILE_FIELDS = array( // id, tag, name, description, profile_field_type_id, sequence
		                                 array(1, "name", "Name", "Your full name.", 1, 10),
		                                 array(2, "byline", "Byline", "Your public byline.", 1, 20),
		                                 array(3, "email", "Email", "Your public email address.", 1, 30),
		                                 array(4, "bio", "Bio", "bio", "Your public profile introduction.", 1, 40),
		                               );
	
		$DEFAULT_PROFILE_ROLES = array( // id, name, description, sequence
		                                array(1, "Public", "Available to everyone without an invitation.", 1),
		                              );
	
		$DEFAULT_PROFILE_FIELD_ROLES = array( // id, profile_field_id, profile_role_id
		                                      array(1, 1, 1),
		                                      array(2, 2, 1),
		                                      array(3, 3, 1),
		                                      array(4, 4, 1),
		                                    );
	
		$DEFAULT_PROFILE_FIELD_INSTANCES = array( // id, profile_field_id, value, sequence
		                                          array( 1, 1, "Full Name", 10 ),
		                                          array( 2, 2, "My public byline ...", 20 ),
		                                          array( 3, 3, "public@me.com", 40 ),
		                                          array( 4, 4, "My public profile ...", 60 ),
		                                        );
	
		$DEFAULT_PROFILE_INVITATIONS = array( // id, name, description, email, guid
		                                      array( 1, "anonymous", "anybody", "anonymous@mail.com", "1" ),
		                                    );
	
		$DEFAULT_PROFILE_INVITATION_ROLES = array( // id, profile_invitation_id, profile_role_id
		                                           array( 1, 1, 1 ),
		                                         );
	
		$table_name = $wpdb->prefix . "guthrie_profile_field_type";
		if ( $wpdb->get_var( "SELECT count(*) FROM `$table_name`" ) == 0) {
			for ( $i = 0; $i < sizeof( $DEFAULT_PROFILE_FIELD_TYPES ); $i++) {
				$field = $DEFAULT_PROFILE_FIELD_TYPES[$i];
				// id, name, description
				$rows_affected = $wpdb->insert( $table_name, 
				                        array( 'time' => current_time( 'mysql' ), 
				                               'id' => $field[0], 
				                               'name' => $field[1], 
				                               'description' => $field[2]) );
			}
		}
	
		$table_name = $wpdb->prefix . "guthrie_profile_field"; 
		if ( $wpdb->get_var( "SELECT count(*) FROM `$table_name`" ) == 0 ) {
			for ( $i = 0; $i < sizeof( $DEFAULT_PROFILE_FIELDS ); $i++) {
				$field = $DEFAULT_PROFILE_FIELDS[$i];
				// id, tag, name, description, profile_field_type_id, sequence
				$rows_affected = $wpdb->insert( $table_name, 
				                                array( 'time' => current_time( 'mysql' ), 
				                                       'id' => $field[0], 
				                                       'tag' => $field[1], 
				                                       'name' => $field[2], 
				                                       'description' => $field[3], 
				                                       'profile_field_type_id' => $field[4], 
				                                       'sequence' => $field[5] ) );
			}
		}
	
	
		$table_name = $wpdb->prefix . "guthrie_profile_role"; 
		if ( $wpdb->get_var( "SELECT count(*) FROM `$table_name`" ) == 0) {
			for ( $i = 0; $i < sizeof( $DEFAULT_PROFILE_ROLES ); $i++) {
				$field = $DEFAULT_PROFILE_ROLES[$i];
				// id, name, description, sequence
				$rows_affected = $wpdb->insert( $table_name, 
				                                array( 'time' => current_time( 'mysql' ), 
				                                       'id' => $field[0], 
				                                       'name' => $field[1], 
				                                       'description' => $field[2],	
				                                       'sequence' => $field[3] ) );
			}
		}
	
		$table_name = $wpdb->prefix . "guthrie_profile_field_role"; 
		if ( $wpdb->get_var( "SELECT count(*) FROM `$table_name`" ) == 0) {
			for ( $i = 0; $i < sizeof( $DEFAULT_PROFILE_FIELD_ROLES ); $i++) {
				$field = $DEFAULT_PROFILE_FIELD_ROLES[$i];
				// id, profile_field_id, profile_role_id
				$rows_affected = $wpdb->insert( $table_name, 
				                                array( 'time' => current_time( 'mysql' ), 
				                                       'id' => $field[0], 
				                                       'profile_field_id' => $field[1], 
				                                       'profile_role_id' => $field[2]) );
			}
		}
	
		$table_name = $wpdb->prefix . "guthrie_profile_field_instance"; 
		if ( $wpdb->get_var( "SELECT count(*) FROM `$table_name`" ) == 0) {
			for ( $i = 0; $i < sizeof( $DEFAULT_PROFILE_FIELD_INSTANCES ); $i++) {
				$field = $DEFAULT_PROFILE_FIELD_INSTANCES[$i];
				// id, profile_field_id, value
				$rows_affected = $wpdb->insert( $table_name, 
				                                array( 'time' => current_time( 'mysql' ), 
				                                       'id' => $field[0], 
				                                       'profile_field_id' => $field[1], 
				                                       'value' => $field[2], 
				                                       'sequence' => $field[3]) );
			}
		}
	
		$table_name = $wpdb->prefix . "guthrie_profile_invitation"; 
		if ( $wpdb->get_var( "SELECT count(*) FROM `$table_name`" ) == 0) {
			for ( $i = 0; $i < sizeof( $DEFAULT_PROFILE_INVITATIONS ); $i++) {
				$field = $DEFAULT_PROFILE_INVITATIONS[$i];
				// id, name, description, email, guid
				$rows_affected = $wpdb->insert( $table_name, 
				                                array( 'time' => current_time('mysql' ), 
				                                       'id' => $field[0], 
				                                       'name' => $field[1], 
				                                       'description' => $field[2], 
				                                       'email' => $field[3], 
				                                       'guid' => $field[4]) );
			}
		}
	
	
		$table_name = $wpdb->prefix . "guthrie_profile_invitation_role"; 
		if ( $wpdb->get_var( "SELECT count(*) FROM `$table_name`" ) == 0) {
			for ( $i = 0; $i < sizeof( $DEFAULT_PROFILE_INVITATION_ROLES ); $i++) {
				$field = $DEFAULT_PROFILE_INVITATION_ROLES[$i];
				// id, profile_invitation_id, profile_role_id
				$rows_affected = $wpdb->insert( $table_name, 
				                                array( 'time' => current_time( 'mysql' ), 
				                                       'id' => $field[0], 
				                                       'profile_invitation_id' => $field[1], 
				                                       'profile_role_id' => $field[2]) );
			}
		}

		
		// some stuff needed for upgrades from previous versions
		// remove our textarea profile_field_type if we exist and revert any references to the 'Text' type ( id==1 )
		$table_name = $wpdb->prefix . "guthrie_profile_field_type"; 
		if ( $wpdb->get_var( "SELECT count(*) FROM `$table_name` where `name` = 'TextArea' and id = 2" ) == 1 ) {
			$wpdb->query( "DELETE FROM `$table_name` WHERE id=2" );
			$table_name = $wpdb->prefix . "guthrie_profile_field"; 
			// update our profile field type ( only one exists now ... )
			$wpdb->query( "UPDATE `$table_name` set `profile_field_type_id` = 1 WHERE `profile_field_type_id` < 3" );
		}

		// see if we have the 'default' field in guthrie_profile_field, if not we have lot's of updates to do for microformat
		$table_name = $wpdb->prefix . "guthrie_profile_field";
		
		
		if( 0 === $wpdb->query("SHOW COLUMNS FROM `$table_name` WHERE field = 'is_default'" ) ) {
	                $instance_table_name = $wpdb->prefix . "guthrie_profile_field_instance";

			// add the 'required' field
			$wpdb->query( "ALTER TABLE $table_name ADD COLUMN is_default INT DEFAULT 0" );
			// update the name field
			$wpdb->query( "UPDATE $table_name SET tag='fn', name='Full Name', is_default = 1 WHERE id=1" ); 

                        // update the email field
                        $wpdb->query( "UPDATE $table_name SET tag='email', name='Email', is_default = 1 WHERE id=3" );

			// Add the rest of the hcard fields with no value (meaning no display) and no relationship
			$wpdb->insert( $table_name,
					array( 'time' => current_time( 'mysql' ),
			                       'id' => null,
			                       'profile_field_type_id' => 1,
			                       'tag' => 'org',
			                       'name' => 'Organization',
			                       'description' => 'Organization or Company Name',
			                       'sequence' => '1000',
			                       'is_default' => 1 ) 
			                     );
			$profile_field_id = $wpdb->get_var( "SELECT LAST_INSERT_ID();" );
                        $rows_affected = $wpdb->insert( $instance_table_name,
                                                        array( 'time' => current_time( 'mysql' ),
                                                               'id' => null,
                                                               'profile_field_id' => $profile_field_id,
                                                               'value' => '',
                                                               'sequence' => 1000) );
			
			$wpdb->insert( $table_name,
                                        array( 'time' => current_time( 'mysql' ),
                                               'id' => null,
                                               'profile_field_type_id' => 1,
                                               'tag' => 'street-address',
                                               'name' => 'Street Address',
                                               'description' => 'Street Address',
                                               'sequence' => '1010',
                                               'is_default' => 1 ) 
                                             );  
                        $profile_field_id = $wpdb->get_var( "SELECT LAST_INSERT_ID();" );
                        $rows_affected = $wpdb->insert( $instance_table_name,
                                                        array( 'time' => current_time( 'mysql' ),
                                                               'id' => null,
                                                               'profile_field_id' => $profile_field_id,
                                                               'value' => '',
                                                               'sequence' => 1010) );

                        $wpdb->insert( $table_name,
                                        array( 'time' => current_time( 'mysql' ),
                                               'id' => null,
                                               'profile_field_type_id' => 1,
                                               'tag' => 'locality',
                                               'name' => 'Town',
                                               'description' => 'Town or Locality',
                                               'sequence' => '1020',
                                               'is_default' => 1 ) 
                                             );  
                        $profile_field_id = $wpdb->get_var( "SELECT LAST_INSERT_ID();" );
                        $rows_affected = $wpdb->insert( $instance_table_name,
                                                        array( 'time' => current_time( 'mysql' ),
                                                               'id' => null,
                                                               'profile_field_id' => $profile_field_id,
                                                               'value' => '',
                                                               'sequence' => 1020) );

                        $wpdb->insert( $table_name,
                                        array( 'time' => current_time( 'mysql' ),
                                               'id' => null,
                                               'profile_field_type_id' => 1,
                                               'tag' => 'region',
                                               'name' => 'State',
                                               'description' => 'State or Province',
                                               'sequence' => '1030',
                                               'is_default' => 1 ) 
                                             );  
                        $profile_field_id = $wpdb->get_var( "SELECT LAST_INSERT_ID();" );
                        $rows_affected = $wpdb->insert( $instance_table_name,
                                                        array( 'time' => current_time( 'mysql' ),
                                                               'id' => null,
                                                               'profile_field_id' => $profile_field_id,
                                                               'value' => '',
                                                               'sequence' => 1030) );

                        $wpdb->insert( $table_name,
                                        array( 'time' => current_time( 'mysql' ),
                                               'id' => null,
                                               'profile_field_type_id' => 1,
                                               'tag' => 'postal-code',
                                               'name' => 'ZIP Code',
                                               'description' => 'ZIP or Postal Code',
                                               'sequence' => '1040',
                                               'is_default' => 1 ) 
                                             );  
                        $profile_field_id = $wpdb->get_var( "SELECT LAST_INSERT_ID();" );
                        $rows_affected = $wpdb->insert( $instance_table_name,
                                                        array( 'time' => current_time( 'mysql' ),
                                                               'id' => null,
                                                               'profile_field_id' => $profile_field_id,
                                                               'value' => '',
                                                               'sequence' => 1040) );

                        $wpdb->insert( $table_name,
                                        array( 'time' => current_time( 'mysql' ),
                                               'id' => null,
                                               'profile_field_type_id' => 1,
                                               'tag' => 'country-name',
                                               'name' => 'Country',
                                               'description' => 'Full Country Name',
                                               'sequence' => '1050',
                                               'is_default' => 1 ) 
                                             );  
                        $profile_field_id = $wpdb->get_var( "SELECT LAST_INSERT_ID();" );
                        $rows_affected = $wpdb->insert( $instance_table_name,
                                                        array( 'time' => current_time( 'mysql' ),
                                                               'id' => null,
                                                               'profile_field_id' => $profile_field_id,
                                                               'value' => '',
                                                               'sequence' => 1050) );

                        $wpdb->insert( $table_name,
                                        array( 'time' => current_time( 'mysql' ),
                                               'id' => null,
                                               'profile_field_type_id' => 1,
                                               'tag' => 'org',
                                               'name' => 'Organization',
                                               'description' => 'Organization or Company Name',
                                               'sequence' => '1060',
                                               'is_default' => 1 ) 
                                             );  
                        $profile_field_id = $wpdb->get_var( "SELECT LAST_INSERT_ID();" );
                        $rows_affected = $wpdb->insert( $instance_table_name,
                                                        array( 'time' => current_time( 'mysql' ),
                                                               'id' => null,
                                                               'profile_field_id' => $profile_field_id,
                                                               'value' => '',
                                                               'sequence' => 1060) );

                        $wpdb->insert( $table_name,
                                        array( 'time' => current_time( 'mysql' ),
                                               'id' => null,
                                               'profile_field_type_id' => 1,
                                               'tag' => 'tel',
                                               'name' => 'Phone',
                                               'description' => 'Primary Phone Number',
                                               'sequence' => '1070',
                                               'is_default' => 1 ) 
                                             );  
                        $profile_field_id = $wpdb->get_var( "SELECT LAST_INSERT_ID();" );
                        $rows_affected = $wpdb->insert( $instance_table_name,
                                                        array( 'time' => current_time( 'mysql' ),
                                                               'id' => null,
                                                               'profile_field_id' => $profile_field_id,
                                                               'value' => '',
                                                               'sequence' => 1070) );

                        $wpdb->insert( $table_name,
                                        array( 'time' => current_time( 'mysql' ),
                                               'id' => null,
                                               'profile_field_type_id' => 1,
                                               'tag' => 'photo',
                                               'name' => 'Image URL',
                                               'description' => 'Photo or Image URL',
                                               'sequence' => '1080',
                                               'is_default' => 1 )
                                             );

                        $profile_field_id = $wpdb->get_var( "SELECT LAST_INSERT_ID();" );
                        $rows_affected = $wpdb->insert( $instance_table_name,
                                                        array( 'time' => current_time( 'mysql' ),
                                                               'id' => null,
                                                               'profile_field_id' => $profile_field_id,
                                                               'value' => '',
                                                               'sequence' => 1080) );
		}		
	} 
	
	/********************************************
	 ** Drop all our tables
	 *********************************************/
	function remove_database() {
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		for( $i = 0; $i < sizeof( $this->TABLE_NAMES ); $i++){
			$table_name = $this->TABLE_NAMES[$i];
			$table_name = $wpdb->prefix . "guthrie_" . $table_name;
			if ( $table_name == $wpdb->get_var( "show tables like '$table_name'" ) ) {
				$sql = "DROP TABLE " . $table_name . ";";
				echo($sql);
				$wpdb->query( $sql );
			}
		}
	}
}
