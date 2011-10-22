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

document.draggableFieldInstance = function( event ) {
	var parent = $( this ).parent();
	//var html = parent.attr('outerHTML');
	var html = $('<div>').append(parent.clone()) .html();
	var field_instance_id = parent.attr( "id" ).split( "_" )[ 1 ];
	var field_instance_index = parent.index();
	var $profile_fields = $('#profile-fields');
	helper_html = '<div><table id="draggable-field-instance-helper_' + field_instance_id + '_' + field_instance_index + '" class="draggable-field-instance-helper" style="position: relative; z-index: 9999; width: ' + $profile_fields.width() + 'px; height: ' + (parent.height()) + 'px;">' + html + '</table></div>';
	return helper_html;
};

document.handleDraggableFieldInstanceStop = function( event, ui ) {
	//alert("stop");
};

document.handleDroppableFieldInstance = function( event, ui ) {
	var helper = $( ui.helper.html() );
	var original_field_instance_id = helper.attr( "id" ).split( "_" )[ 1 ];
	var original_field_instance_index = helper.attr( "id" ).split( "_" )[ 2 ];

	var droppable = $( event.target );
	var field_instance_id = droppable.attr( "id" ).split( "_" )[ 1 ];
	var field_instance_index = droppable.index();

	var url = "/wp-admin/admin-ajax.php";
	
	// Make an AJAX call to update the order before we move it.
	var data = 'action=guthrie_update_profile_field_sequence' 
					 + '&original_field_instance_id=' + encodeURIComponent( original_field_instance_id ) 
					 + '&original_field_instance_index=' + encodeURIComponent( original_field_instance_index ) 
					 + '&field_id_name=' + encodeURIComponent( field_instance_id ) 
					 + '&field_instance_index=' + encodeURIComponent( field_instance_index );

	$.ajax( {
		url: url,
		type: "POST",
		data: data,
		dataType: "json",
		complete: function( response ){
			var oResponse = jQuery.parseJSON( response.responseText );
			if( oResponse.update_profile_field_sequence ) {
				// success, move our rows
				var dragged_from_index = oResponse.original_field_instance_index;
				var dropped_on_index = oResponse.field_instance_index;
				var profile_field_instances = $( '#profile-fields tbody' );
				var dragged_item = profile_field_instances.children().eq( dragged_from_index );
				var html = $('<div>').append(dragged_item.clone()).html();
				var dropped_item = profile_field_instances.children().eq( dropped_on_index );
				var dragged_item_to_drop = $(html);
				
				
				
				dragged_item_to_drop.find('.chzn-container').remove();
				dragged_item_to_drop.find('.field-roles').removeClass('chzn-select');
				dragged_item_to_drop.find('.field-roles').removeClass('chzn-done');
				dragged_item_to_drop.find('.field-roles').width('300px');
				dragged_item_to_drop.find('.profile-field').html(dragged_item_to_drop.find('.profile-field').find('.editable').contents());

				if ( dragged_from_index > dropped_on_index ) {
					dropped_item.before( dragged_item_to_drop );
				} else {
					dropped_item.after( dragged_item_to_drop );
				}
				dragged_item.remove();
				
				dragged_item_to_drop.find('.field-roles').chosen( { no_results_text: 'No results matched. <button class="add-role-button">Add</button>' } ).change( document.updateFieldRoles );

				// make sure we are stilled wired to our events
				$( ".draggable-field-handle" ).draggable( { 
					containment: $( '#profile-fields tbody' ),
					snap: $( '.profile-fields tbody' ),
					helper: document.draggableFieldInstance,
					stack: $( '#profile-fields tbody' )
					} );
			
				$( ".field-instance-container" ).droppable( { 
					drop: document.handleDroppableFieldInstance,
					} );

				dragged_item_to_drop.find( '.profile-field' ).removeClass('edit-in-place');
				dragged_item_to_drop.find( ".profile-field" ).guthrieEditInPlace(
					{
						url:                   "/wp-admin/admin-ajax.php", // string: POST URL to send adjusted amount
						action:                "guthrie_update_profile_field_value", // string: POST URL to send adjusted amount
						input_width:           "290", // integer: value for width
						element_id:            "element_id", // string: the id of the element to insert the returned value in
						field_id_name:         "field_instance_id", // string: the id of the element to insert the returned value in
						field_value_name:      "value", // string: parameter name for the adjustment value
						original_field_value:  "original_value" // string: parameter name for the adjustment value
					}
				);
				
			}
		}
	} );
};

$( document ).ready( function() {
	// for our "edit in place" table cells	
	$( "#profile-fields .chzn-select" ).chosen( { no_results_text: 'No results matched.' } ).change( document.updateFieldRoles );
	$( ".invitation-roles" ).chosen( { no_results_text: 'No results matched.' } ).change( document.updateInvitationRoles );
	
	
	$( ".profile-field" ).guthrieEditInPlace(
		{
			url:                   "/wp-admin/admin-ajax.php", // string: POST URL to send adjusted amount
			action:                "guthrie_update_profile_field_value", // string: POST URL to send adjusted amount
			input_width:           "98%", // integer: value for width
			element_id:            "element_id", // string: the id of the element to insert the returned value in
			field_id_name:         "field_instance_id", // string: the id of the element to insert the returned value in
			field_value_name:      "value", // string: parameter name for the adjustment value
			original_field_value:  "original_value" // string: parameter name for the adjustment value
		}
	);

	$( ".profile-role-name" ).guthrieEditInPlace(
		{
			url:                   "/wp-admin/admin-ajax.php", // string: POST URL to send adjusted amount
			action:                "guthrie_update_profile_role_name", // string: POST URL to send adjusted amount
			input_width:           "98%", // integer: value for width
			element_id:            "element_id", // string: the id of the element to insert the returned value in
			field_id_name:         "role_id", // string: the id of the element to insert the returned value in
			field_value_name:      "name", // string: parameter name for the adjustment value
			original_field_value:  "original_value" // string: parameter name for the adjustment value
		}
	);

	$( ".profile-role-description" ).guthrieEditInPlace(
		{
			url:                   "/wp-admin/admin-ajax.php", // string: POST URL to send adjusted amount
			action:                "guthrie_update_profile_role_description", // string: POST URL to send adjusted amount
			input_width:           "98%", // integer: value for width
			element_id:            "element_id", // string: the id of the element to insert the returned value in
			field_id_name:         "role_id", // string: the id of the element to insert the returned value in
			field_value_name:      "description", // string: parameter name for the adjustment value
			original_field_value:  "original_value" // string: parameter name for the adjustment value
		}
	);

	$( ".profile-invitation-name" ).guthrieEditInPlace(
		{
			url:                   "/wp-admin/admin-ajax.php", // string: POST URL to send adjusted amount
			action:                "guthrie_update_profile_invitation_name", // string: POST URL to send adjusted amount
			input_width:           "98%", // integer: value for width
			element_id:            "element_id", // string: the id of the element to insert the returned value in
			field_id_name:         "invitation_id", // string: the id of the element to insert the returned value in
			field_value_name:      "name", // string: parameter name for the adjustment value
			original_field_value:  "original_value" // string: parameter name for the adjustment value
		}
	);

	$( ".profile-invitation-description" ).guthrieEditInPlace(
		{
			url:                   "/wp-admin/admin-ajax.php", // string: POST URL to send adjusted amount
			action:                "guthrie_update_profile_invitation_description", // string: POST URL to send adjusted amount
			input_width:           "98%", // integer: value for width
			element_id:            "element_id", // string: the id of the element to insert the returned value in
			field_id_name:         "invitation_id", // string: the id of the element to insert the returned value in
			field_value_name:      "description", // string: parameter name for the adjustment value
			original_field_value:  "original_value" // string: parameter name for the adjustment value
		}
	);
	


	$( ".draggable-field-handle" ).draggable( { 
		containment: $( '#profile-fields tbody' ),
		snap: $( '.profile-field-instance' ),
		helper: document.draggableFieldInstance,
		} );

	$( ".profile-field-instance" ).droppable( { 
		drop: document.handleDroppableFieldInstance,
		} );

	// for our "add" forms	
	$( ".profile-invite-roles" ).chosen( { no_results_text: 'No results matched.' } );
	$( ".profile-field-add-roles" ).chosen( { no_results_text: 'No results matched.' } );

	$('#profile-invitations tbody .delete').click(function(element){
		if( confirm( 'Delete invitation?' ) ) {
			var $this = $(element.target);
			var profile_invitation_id = $this.attr("id").split("_")[1];
			var element_id = $this.attr("id");

			var data = "action=guthrie_remove_profile_invitation" + 
					       "&profile_invitation_id=" + profile_invitation_id + 
					       "&element_id=" + element_id;
			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: data,
				dataType: "json",
				complete: function( response ){
					// remove our row if we succeeded
					var oResponse = jQuery.parseJSON( response.responseText );
					if ( oResponse.remove_profile_invitation_success ) {
						$('#' + oResponse.element_id).parent().parent().remove();
					}
				}
			});
		}
	}); 
	$('#profile-fields tbody .delete').click(function(element){
		if( confirm( 'Delete field?' ) ) {
			var $this = $(element.target);
			var profile_field_instance_id = $this.attr("id").split("_")[1];
			var element_id = $this.attr("id");

			var data = "action=guthrie_remove_profile_field_instance" + 
					       "&profile_field_instance_id=" + profile_field_instance_id + 
					       "&element_id=" + element_id;
			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: data,
				dataType: "json",
				complete: function( response ){
					// remove our row if we succeeded
					var oResponse = jQuery.parseJSON( response.responseText );
					if ( oResponse.remove_profile_field_instance_success ) {
						$('#' + oResponse.element_id).parent().parent().remove();
					}
				}
			});
		}
	}); 
	$('#profile-roles tbody .delete').click(function(element){
		if( confirm( 'Delete role?' ) ) {
			var $this = $(element.target);
			var profile_role_id = $this.attr("id").split("_")[1];
			var element_id = $this.attr("id");

			var data = "action=guthrie_remove_profile_role" + 
					       "&profile_role_id=" + profile_role_id + 
					       "&element_id=" + element_id;
			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: data,
				dataType: "json",
				complete: function( response ){
					// remove our row if we succeeded
					var oResponse = jQuery.parseJSON( response.responseText );
					if ( oResponse.remove_profile_role_success ) {
						$('#' + oResponse.element_id).parent().parent().remove();
					}
				}
			});
		}
	}); 
});
