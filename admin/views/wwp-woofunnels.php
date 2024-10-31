<div class="wrap metabox-holder wooWhatsapp">
	<h2>Ações para Woofunnels</h2>
	<?php do_action( 'pac_admin_notices' ); ?>
	<div class="row">
		<div class="col m8 s12 ">
			<h3>Templates para envio</h3>
			<div class="col postbox">
				<form id="wwp_form_acoes" action="" method="post">
					<p><input id="newactionjetbooking" name="newaction" type="checkbox" value="1"> Criar nova Ação</p>
					<fieldset id="fieldsetnewactionjetbooking" style="border: 1px solid #000;" class="hide">
						<legend style="margin-left: 13px; font-size: 21px;"><b>Nova ação</b></legend>
						<div class="col s12">
							<div class="col m3 s12">
								<p>Slug da ação:</p>
							</div>
							<div class="col m9 s12">
								<p><small>pac/</small><input name="newslug" class="slugacao" type="text" value=""></p>
							</div>
							<div class="col s12">
								<p>Template da Mensagem:
								<textarea name="newtemplate" rows="10"></textarea>
								</p>
							</div>	
						</div>
						<div class="col s12">
							<input type="submit" class="margin-top-bottom15 button button-primary" value="Salvar nova Ação" />
						</div>
					</fieldset>
					<?php if (!empty($acoes)): ?>
					<div id="accordionTriggers">
						<?php	foreach ($acoes as $aKey => $aValue): ?>
						<h3>Hook: pac/<?php echo $aValue['slug']; ?> </h3>
						<div class="row">
							<div class="col m3 s12">
								<p>Slug da ação:</p>
							</div>
							<div class="col m9 s12">
								<p><small>pac/</small><input name="slug[]" type="text" class="slugacao" value="<?php echo $aValue['slug']; ?>"></p>
							</div>
							<div class="col s12">
								<p>Template da Mensagem:
								<textarea name="template[]" rows="10"><?php echo $aValue['template']; ?></textarea>
								</p>
							</div>	
						</div>	
						<?php endforeach ?>
					</div>
					<div>
						<input type="submit" class="margin-top-bottom15 button button-primary" value="Salvar" />
					</div>
					<?php endif ?>
				</form>
			</div>
		</div>
		<div class="col m3 s12">
			<div>
				<h3>Como usar</h3>
				<div class="col postbox">
					<p>Para usar as ações cadastradas, acesse a área de conectores Automations do Woofunnels e clique em "Selecionar ação" na caixa do diagrama selecione um evento que deseja ativsr o gatilho.</p>
					<p>No painel ao lado, selecione "Custom Callback" e no campo "Select Action"</p>
					<p>No campo "Callback Name" insira o slug da ação cadastrada com "pac/" como prefixo, e salve.</p>
				</div>
			</div>
			<div>
				<h3>Shortcode</h3>
				<div class="col postbox">
					<p>Você pode utilizar os nome dos campos como variáveis para o template, basta colocar o nome do campo entre colchetes [].</p>
					<p>É obrigatório conter um campo com o nome de whatsapp, phone ou cell_phone como destinatário da mensagem.</p>
				</div>
			</div>
		</div>
	</div>
	<div class="footer">
		<p>
			Encontrou algum bug ou quer fazer um contário? <a href="https://wordpress.org/plugins/powers-triggers-of-woo-to-chat/" target="_blank">Entre em contato aqui</a> Gostou do plugin? Considere dar 5 estrelas em uma avaliação no <a href="https://wordpress.org/support/plugin/powers-triggers-of-woo-to-chat/reviews/#new-post" target="_blank">wordpress.org</a>. Obrigado! :)
		</p>
	</div>
	<input id="pluginurl" type="hidden" value="<?php echo plugin_dir_url( dirname( __FILE__ ) ); ?>">
	<input id="ajaxurl" type="hidden" value="<?php echo admin_url('admin-ajax.php'); ?>">
</div>