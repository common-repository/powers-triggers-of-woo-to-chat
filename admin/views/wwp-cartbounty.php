<?php 
	$url = admin_url('admin.php?page=cartbounty');
?>
<form id="wwp_form" method="post" action="<?php echo $url; ?>" style="width:50%;">
	<h2 style="font-weight: bold">Configuração de Mensagem WhatsApp</h2>
	<label for="wwp_textarea"><input type="checkbox" value="1" <?php echo empty($wwp_cron_cartbounty) ? '' : 'checked'; ?> name="wwp_cron"> <b>Habilitar envio automático</b></label> <br />
	<label for="wwp_textarea"><b>Template:</b></label>
	<textarea id="wwp_textarea" name="wwp_textarea" style="width:100%; height: 117px;" required="required"><?php echo $wwp_template_cartbounty; ?></textarea>
	<input type="submit" value="Salvar" class="button button-primary">
	<h2>Legenda de Shortcode</h2>
	<div class="postbox" style="display: inline-block;">
		<div class="legenda-block" style="padding: 0 0.75rem;">
			<p><strong>Carrinho abandonado</strong></p>
			<ul>
				<li>{name}</li>
				<li>{surname}</li>
				<li>{email}</li>
				<li>{cart_contents_all}</li>
				<li>{cart_contents($)}</li>
				<li>{cart_link}</li>
			</ul>
		</div>
	</div>
</form>