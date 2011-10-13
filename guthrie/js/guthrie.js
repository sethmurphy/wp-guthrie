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
					 + '&field_instance_id=' + encodeURIComponent( field_instance_id ) 
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
				var profile_field_instances = $( '#profile-field-instances' );
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
					containment: $( '#profile-field-instances' ),
					snap: $( '.profile-field-instance' ),
					helper: document.draggableFieldInstance,
					stack: $( '#profile-field-instances' )
					} );
			
				$( ".field-instance-container" ).droppable( { 
					drop: document.handleDroppableFieldInstance,
					} );

				dragged_item_to_drop.find( '.profile-field' ).removeClass('edit-in-place');
				dragged_item_to_drop.find( '.profile-field' ).guthrieEditInPlace(
					{
						url:                   "/wp-admin/admin-ajax.php", // string: POST URL to send adjusted amount
						action:                "guthrie_update_profile_field_value", // string: POST URL to send adjusted amount
						input_width:           "290", // integer: value for width
						element_id:            "element_id", // string: the id of the element to insert the returned value in
						field_instance_id:     "field_instance_id", // string: the id of the element to insert the returned value in
						field_value:           "value", // string: parameter name for the adjustment value
						original_field_value:  "original_value" // string: parameter name for the adjustment value
					}
				);				
				
			}
		}
	} );
};

$( document ).ready( function() {
	$( "#profile-field-instances .chzn-select" ).chosen( { no_results_text: 'No results matched. <button class="add-role-button">Add</button>' } ).change( document.updateFieldRoles );
	$( ".profile-field" ).guthrieEditInPlace(
		{
			url:                   "/wp-admin/admin-ajax.php", // string: POST URL to send adjusted amount
			action:                "guthrie_update_profile_field_value", // string: POST URL to send adjusted amount
			input_width:           "290", // integer: value for width
			element_id:            "element_id", // string: the id of the element to insert the returned value in
			field_instance_id:     "field_instance_id", // string: the id of the element to insert the returned value in
			field_value:           "value", // string: parameter name for the adjustment value
			original_field_value:  "original_value" // string: parameter name for the adjustment value
		}
	);

	$( ".draggable-field-handle" ).draggable( { 
		containment: $( '#profile-field-instances' ),
		snap: $( '.profile-field-instance' ),
		helper: document.draggableFieldInstance,
		} );

	$( ".profile-field-instance" ).droppable( { 
		drop: document.handleDroppableFieldInstance,
		} );

	$( "#profile-invite-field-roles-container .chzn-select" ).chosen( { no_results_text: 'No results matched.' } );


});
