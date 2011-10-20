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

document.updateFieldRoles = function(element) {
  var $this = $(element.target);
	var field_roles = $this.val();

	var field_id = $this.attr("id").split("_")[1];
	var element_id = $this.attr("id");

	var data = "action=guthrie_update_profile_field_roles" + 
	           "&field_id=" + field_id + 
	           "&element_id=" + element_id;
	if( field_roles != null ){
		for ( i=0; i < field_roles.length; i++ ){
			data += '&';
			data += 'field_roles[]=' + encodeURIComponent( field_roles[i] );
		}
	}
	$.ajax({
		type: "POST",
		url: ajaxurl,
		data: data,
		dataType: "json",
		complete: function( response ){
			// do nothing for now
		}
	});
}
