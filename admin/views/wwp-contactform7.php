<div class="wrap metabox-holder wooWhatsapp">
	<h2>Formulários</h2>
	<div class="row postbox">
		<h2 class="hndle"><span>Selecione o formulário</span></h2>
		<form action="" method="post">
			<div id="painel" class="col s12">
				<div id="accordionTriggers">
					<?php	foreach ($triggers as $tKey => $tValue): ?>
					<h3>
						<?php echo $tValue['titulo'] ?>
						<div class="right"><label>Ativo <input name="<?php echo $tKey ?>_ativo" type="checkbox" value="1" <?php echo $tValue['ativo']==1 ? 'checked' : '' ?>></label></div>
					</h3>
					<div class="row">
						<?php echo $tValue['descricao']!='' ? '<p>'.$tValue['descricao'].'</p>' : ''; ?>
						<div class="col s6 m4">
							<label for="<?php echo $tKey ?>_campo_telefone"><b>Campo de telefone destino:</b></label>
						</div>
						<div class="col s6 m8">
							<select name="<?php echo $tKey ?>_campo_telefone" id="<?php echo $tKey ?>_campo_telefone">
								<?php foreach ($tValue['campos'] as $campo): ?>
									<?php if ($campo->name != ""): ?>
									<option <?php echo $campo->name==$tValue['campo_telefone'] ? 'selected' : '' ?> value="<?php echo $campo->name ?>"><?php echo $campo->name ?></option>
									<?php endif ?>
								<?php endforeach ?>
							</select>
						</div>	
						<div class="col s12 m6 ">
							<label>
								<strong>Modelo de Mensagem:</strong> <br>
								<textarea name="<?php echo $tKey ?>_modelo" cols="30" rows="10"><?php echo $tValue['modelo'] ?></textarea>
							</label>
						</div>
						<div class="col s12 m6 ">
							<b>Shortcode disponíveis:</b>
							<p>
							<?php foreach ($tValue['campos'] as $campo): ?>
								<?php if ($campo->name != ""): ?>
								{<?php echo $campo->name ?>} <br />
								<?php endif ?>
							<?php endforeach ?>
							</p>
						</div>
					</div>
					<?php endforeach ?>
				</div>

				<input type="submit" class="margin-top-bottom15 button button-primary" value="Salvar Gatilhos" />
			</div>
		</form>	
	</div>
	
	<div class="footer">
		<p>
			Encontrou algum bug ou quer fazer um contário? <a href="https://wordpress.org/plugins/powers-triggers-of-woo-to-chat/" target="_blank">Entre em contato aqui</a> Gostou do plugin? Considere dar 5 estrelas em uma avaliação no <a href="https://wordpress.org/support/plugin/powers-triggers-of-woo-to-chat/reviews/#new-post" target="_blank">wordpress.org</a>. Obrigado! :)
		</p>
	</div>
	<input id="pluginurl" type="hidden" value="<?php echo plugin_dir_url( dirname( __FILE__ ) ); ?>">
	<input id="ajaxurl" type="hidden" value="<?php echo admin_url('admin-ajax.php'); ?>">
</div>