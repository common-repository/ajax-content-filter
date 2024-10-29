function change_content(id){
	var data = {
			action: 'my_action',
			id: id
		};
		jQuery.post(ajaxurl, data, function(response){
			jQuery("#loadcontent_here").html(response);
		});
}