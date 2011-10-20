document.updateInvitationRoles = function(element) {
  var $this = $(element.target);
	var invitation_roles = $this.val();

	var invitation_id = $this.attr("id").split("_")[1];
	var element_id = $this.attr("id");

	var data = "action=guthrie_update_profile_invitation_roles" + 
	           "&invitation_id=" + invitation_id + 
	           "&element_id=" + element_id;
	if( invitation_roles != null ){
		for ( i=0; i < invitation_roles.length; i++ ){
			data += '&';
			data += 'invitation_roles[]=' + encodeURIComponent( invitation_roles[i] );
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
