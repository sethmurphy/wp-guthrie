document.updateFieldValue = function(element) {
  var $this = $(element.target);
	var field_value = $this.val();

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