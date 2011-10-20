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
global $admin_options;

class Guthrie_Admin_Options {
	public $current_tab = '';

	private $default_tab = 'profile';

	// holds our tabs meta data ( [ SLUG_POSTFIX ], [ TITLE] )
	private $option_tabs = array( 'profile' => 'Manage Profile' ,
	                              'invitations' => 'Invitations' ,
	                              'roles' => 'Roles' );

	
	function Guthrie_Admin_Options( $guthrie = null ) {
		$this->__construct( $guthrie );
	}

	function __construct( $guthrie = null ) {
		// get our requested tab if valid, otherwise the default one
		$this->current_tab = isset( $_GET['tab'] ) && array_key_exists($_GET['tab'], $this->option_tabs ) ? $_GET['tab'] : $this->default_tab;

		// create the appropriate object for the tab
		require ( 'admin_options_' . $this->current_tab . '.php' );
		$this->admin_options_tab = call_user_func( 'Guthrie_Admin_Options_' . ucfirst( $this->current_tab) . '::factory', $guthrie, $this);
	}
	
	function create_tabs() {
		// create our tabs
		screen_icon();
		echo '<h2>Guthrie Settings</h2>';
		echo '<h3 class="nav-tab-wrapper">';
		foreach ( $this->option_tabs as $tab_key => $tab_caption ) {
			$active = $this->current_tab == $tab_key ? 'nav-tab-active' : '';
			echo '<a class="nav-tab ' . $active . '" href="?page=guthrie&tab=' . $tab_key . '">' . $tab_caption . '</a>';
		}
		echo '</h3>';
	}
}
?>
