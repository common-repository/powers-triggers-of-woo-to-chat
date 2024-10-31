<div class="wrap metabox-holder wooWhatsapp">
	<h2>Gatilhos</h2>
	<div class="row postbox">
		<h2 class="hndle"><span>Enviar Mensagem Quando...</span></h2>
		<p style="padding: 10px">Preencha os modelos de texto para cada status de pedido disponível, consulte a legenda de shortcode (variáveis) para personalizar. Você pode tambem selecionar um arquivo de imagem ou PDF para ser enviado logo em seguida. Deixe este campo em branco caso não queira utilizar essa função.</p>
		<form action="" method="post">
			<div id="painel" class="col s12">
				<div id="accordionTriggers">
					<?php	foreach ($triggers as $tKey => $tValue): ?>
					<?php 		$tValue['modelo'] = is_array($tValue['modelo']) ? $tValue['modelo'] : array($tValue['modelo']); ?>
					<?php 		$i = 0; ?>
					<h3>
						<?php echo $tValue['titulo'] ?>
						<div class="right"><label>Ativo <input name="<?php echo $tKey ?>_ativo" type="checkbox" value="1" <?php echo $tValue['ativo']==1 ? 'checked' : '' ?>></label></div>
					</h3>
					<div class="row">
						<?php echo $tValue['descricao']!='' ? '<p>'.$tValue['descricao'].'</p>' : ''; ?>
						<?php foreach ($tValue['modelo'] as $vModelo): ?>
						<div class="col s12 m6">
							<?php if ($tKey == 'hold_order'): ?>
								<label>
									<strong>Intervalo:</strong>
									<select name="<?php echo $tKey ?>_intervalo[]">
										<option <?php echo $tValue['intervalo'][$i]=='off' ? 'selected' : '' ?> value="off">Desligado</option>
										<option <?php echo $tValue['intervalo'][$i]=='1 hour' ? 'selected' : '' ?> value="1 hour">1 hora após a data do pedido</option>
										<option <?php echo $tValue['intervalo'][$i]=='1 days' ? 'selected' : '' ?> value="1 days">1 dia após a data do pedido</option>
										<option <?php echo $tValue['intervalo'][$i]=='2 days' ? 'selected' : '' ?> value="2 days">2 dias após a data do pedido</option>
										<option <?php echo $tValue['intervalo'][$i]=='3 days' ? 'selected' : '' ?> value="3 days">3 dias após a data do pedido</option>
										<option <?php echo $tValue['intervalo'][$i]=='4 days' ? 'selected' : '' ?> value="4 days">4 dias após a data do pedido</option>
									</select> <br />
								</label>
							<?php endif ?>
							<label>
								<strong>Modelo de Mensagem:</strong> <br>
								<textarea name="<?php echo $tKey ?>_modelo[]" cols="30" rows="10"><?php echo $vModelo ?></textarea>
							</label>
							<?php if (isset($tValue['file']) and $tKey != 'hold_order'): ?>
							<label style="display: block">
								<strong>URL de imagem ou PDF:</strong> <br>
								<input id="<?php echo $tKey ?>_pac_media_url" type="url" name="<?php echo $tKey ?>_file[]" value="<?php echo $tValue['file'] ?>">
								<input data-url-target="<?php echo $tKey ?>_pac_media_url" type="button" class="button pac_open_media" value="Escolher Arquivo" />
								
							</label>
							<?php endif ?>
						</div>
						<?php 		$i++; ?>
						<?php endforeach ?>
					</div>
					<?php endforeach ?>
				</div>

				<input type="submit" class="margin-top-bottom15 button button-primary" value="Salvar Gatilhos" />
			</div>
		</form>	
	</div>
	<h2>Legenda de Shortcode</h2>
	<div class="row postbox">
		<div class="legenda-block col s12">
			<div class="col s2">
				<p><strong>Básico</strong></p>
				<ul>
					<li>{site_url}</li>
				</ul>
				<?php if (is_plugin_active('woocommerce-correios/woocommerce-correios.php')): ?>	
				<p><strong>Claudio Sanches - Correios for WooCommerce</strong></p>
				<ul>
					<li>{correios_tracking_code}</li>
				</ul>
				<?php endif ?>
			</div>
			<div class="col s2">
				<p><strong>Nome</strong></p>
				<ul>
					<li>{billing_first_name}</li>
					<li>{billing_last_name}</li>
				</ul>
			</div>
			<div class="col s2">
				<p><strong>Endereço de cobrança</strong></p>
				<ul>
					<li>{billing_address_1}</li>
					<li>{billing_address_2}</li>
					<li>{billing_city}</li>
					<li>{billing_state}</li>
					<li>{billing_postcode}</li>
					<li>{billing_country}</li>
					<li>{billing_email}</li>
					<li>{billing_phone}</li>
					<li>{billing_company}</li>
				</ul>
			</div>
			<div class="col s2">
				<p><strong>Entrega</strong></p>
				<ul>
					<li>{shipping_first_name}</li>
					<li>{shipping_last_name}</li>
					<li>{universal_tracking_code}</li>
					<li>{universal_tracking_url}</li>
				</ul>
			</div>
			<div class="col s2">
				<p><strong>Endereço de entrega</strong></p>
				<ul>
					<li>{shipping_address_1}</li>
					<li>{shipping_address_2}</li>
					<li>{shipping_city}</li>
					<li>{shipping_state}</li>
					<li>{shipping_postcode}</li>
					<li>{shipping_company}</li>
					<li>{shipping_country}</li>
					<li>{order_comments}</li>
				</ul>
			</div>
			<div class="col s2">
				<p><strong>Pedido</strong></p>
				<ul>
					<li>{order_id}</li>
					<li>{products_name}</li>
					<li>{order_date_created}</li>
					<li>{order_total}</li>
					<li>{payment_url}</li>
				</ul>
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