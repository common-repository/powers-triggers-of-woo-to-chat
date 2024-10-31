jQuery(document).ready(function($) { 
	$('.wp-list-table thead tr, .wp-list-table tfoot tr').append('<th scope="col" id="whastapp" class="manage-column column-whastapp"><span>Enviar WhatsApp</span></th>');
	$('.wp-list-table tbody tr').each(function(index, el) {
		var telefone = $(el).find('.column-phone').html();
		if (telefone != '') {
			var id = $(el).find('.check-column input').val();
			$(el).append('<td class="whastapp column-whastapp" data-colname="WhastApp"><a href="#" data-itemid='+id+'><span class="dashicons dashicons-testimonial"></span> Enviar Mensagem</a></td>');
		}else{
			$(el).append('<td class="whastapp column-whastapp" data-colname="WhastApp"> - - </td>');
		}
	});

	var selectize = $('#bulk-action-selector-top')[0].selectize;
	var selectize_bottom = $('#bulk-action-selector-bottom')[0].selectize;
	selectize.addOption({value: 'whatsapp', text: 'Enviar Mensagem whatsapp'});
	selectize_bottom.addOption({value: 'whatsapp', text: 'Enviar Mensagem whatsapp'});

	$('.column-whastapp a').each(function(index, el) {
		$(el).click(function(event) {
			event.preventDefault();
			if (confirm("Deseja realmente enviar essa mensagem?")) {
				$(el).parent('.column-whastapp').append('<span class="spinner is-active"></span>');
				$(el).hide();
				var id = $(el).attr('data-itemid');
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						'action': 'wwp_send_from_cartbounty', 
                		'wwp_id': id,
					},
				})
				.done(function(data) {
					if (data=='true') {
						alert("Mensagem enviada com sucesso");	
					} else{
						alert("Não foi possível enviar a mensagem.");
					}
					$('.spinner').remove();
					$(el).show();
				})
				.fail(function(data ) {
					alert("Não foi possível enviar a mensagem.");
				})


			}
		});
	});

	//Carregar view;
	$.ajax({
		url: ajaxurl,
		type: 'POST',
		data: {
			'action': 'wwp_view_cartbounty'
		},
	})
	.done(function(data) {
		$("#wpbody-content").after(data);
	});
});