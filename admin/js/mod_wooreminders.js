jQuery(document).ready(function($) { 
	var checked = wwp_mod_wooreminders_object.wpp_enable == 'true' ? true : false;
	$('<input />', {
	    type: 'checkbox',
	    id: 'wpp_enable',
	    name: 'wpp_enable',
	    value: '1',
	    style: 'margin: 10px;'
	}).insertAfter("#saveEmailTemp");
	$( "#wpp_enable" ).prop( "checked", checked );
	$('<span>Envie esta mensagem também por WhatsApp.</span>').insertAfter("#wpp_enable");
	$('#saveEmailTemp').click(function(event) {
		var enable = $('#wpp_enable').is(':checked');
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				'action': 'wwp_wooreminders', 
	    		'wpp_enable': enable,
			},
		});
	});
	
});