<?php
class Woowhatspowers_Woocommerce_Front {
	public $settings;
	private $whatsapp;
	private $log;
	
	function __construct() {
		$this->whatsapp = new Woowhatspowers_Whastapp;
		$this->log = new Woowhatspowers_log;

		$this->wwp_load_action();
		
		add_filter( 'wwp_replace_woocommerce_modelo', array($this,'wwp_tracking_filter' ), 10, 2 );
		add_filter( 'wwp_replace_woocommerce_modelo', array($this,'wwp_fs_license_manager_filter' ), 10, 2 );
		if(isset($_GET['wwp_do_reminder'])){
			$this->wwp_do_reminder();
			exit();
		}
	}

	private function wwp_load_action() {
		if (empty($this->whatsapp->getKey())) {
			return false;
		}
		$settings = get_option( 'wwp_triggers', '' );
		if (!empty($settings)) {
			$settings = json_decode($settings);
			$this->settings = $settings;
			foreach ($settings as $key => $sValue) {
				if ($sValue->ativo == '1' and $sValue->action != 'reminder') {
					if ( strpos($key, 'custom_status_') === false) {						
						add_action( $sValue->action, function ($orderId) use ($sValue) {
							$this->wwp_do_action($orderId,$sValue);
						}, PHP_INT_MAX ,1 );
					} else{
						add_action( 'woocommerce_order_status_changed', function ($orderId, $status_from,  $status_to) use ($sValue) {				
							if (str_replace('wc-', '', $sValue->action) == $status_to ) {
								$this->wwp_do_action($orderId,$sValue);
							}
						}, PHP_INT_MAX ,3 );
					}
				}
			}

			add_action( 'wwp_reminder_action', array( $this, 'wwp_do_reminder' ) );
		}

		//ACTION TO CREAT TO SAVE NOTIFY FIELD
		if (wwp_is_plugin_active('woofunnels-aero-checkout/woofunnels-aero-checkout.php')) {
			add_action( 'woocommerce_review_order_before_payment', array( $this, 'wwp_option_field' ) );	
		} elseif(wwp_is_plugin_active('checkout-mestres-wp/checkout-woocommerce-mestres-wp.php')){
			add_action( 'woocommerce_after_checkout_billing_form', array( $this, 'wwp_option_field' ) );
		} else{
			add_action( 'woocommerce_after_order_notes', array( $this, 'wwp_option_field' ) );
		}
		
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'wwp_save_option_field' ) );
	}

	public function wwp_do_reminder (){
		date_default_timezone_set(get_option('timezone_string','America/Sao_Paulo'));
		$settings = $this->settings;
		$intervalos = $settings->hold_order->intervalo;
		if (!isset($settings->hold_order) or $settings->hold_order->ativo != 1) {
			return false;
		}
		$orders = wc_get_orders(array(
		    'post_status'=> array('on-hold','pending'),
		    'meta_key' => 'wwp_notify_whatsapp',
		    'meta_compare' => '==',
		    'meta_value' =>  1,
			'limit' => 999
	    ));

	    
	    foreach ($orders as $order) { 	
	    	$dataCriado = $order->order_date;
	    	$keySend = '';
	    	foreach ($intervalos as $key => $value) {
	    		if ($value!='off') {
	    			$dataProg = date('YmdHi', strtotime('+'.$value, strtotime($dataCriado)));
	    			$dataGap = date("YmdHi", strtotime('+2 hours', strtotime("now")));
	    			if ($dataProg<=$dataGap and $dataProg>=date("YmdHi")) {
	    				$keySend = $key;
	    			}
	    		}
	    	}

	    	
	    	if ($keySend!=='') {
		    	$modelo =  $settings->hold_order->modelo[$keySend];
		    	//REPLACE MODELO
				$modelo  = $this->wwp_replace_modelo($order->id, $modelo);
				//GET PHONE
				$target_phone = $order->get_billing_phone();
				$target_phone = $this->phone_validation($target_phone,$this->whatsapp->getCode());
				if ($target_phone !== FALSE) {
					//SEND MESSAGE
					$res = $this->whatsapp->sendMessage($target_phone,$modelo);
				}
	    	}
		}
	}



	public function wwp_do_action($order_id,$trigger) {

		$wwp_notify = get_post_meta($order_id, 'wwp_notify_whatsapp', true);
		if (empty($wwp_notify)) {
			return false;
		}
		$modelo = $trigger->modelo;
		$rData = array();
		$order = wc_get_order($order_id);

		//GET PHONE
		$target_phone = $order->get_billing_phone();
		$target_phone = $this->phone_validation($target_phone,$this->whatsapp->getCode());
		if ($target_phone === FALSE) {
			return false;
		}

		//REPLACE MODELO
		$modelo  = $this->wwp_replace_modelo($order_id, $modelo);

		//SEND MESSAGE
		$res = $this->whatsapp->sendMessage($target_phone,$modelo);
		if ($res) {
			if (isset($trigger->file) and !empty($trigger->file)) {
				if(@is_array(getimagesize($trigger->file))){
				    $res = $this->whatsapp->sendImg($target_phone,$trigger->file);
				} else {
				    $res = $this->whatsapp->sendFile($target_phone,$trigger->file);
				}
			}
		}
	}

	public function wwp_replace_modelo($order_id,$modelo) {

		//BASICS 
		$order = wc_get_order($order_id);
		$rData['order_id'] = $order_id;
		$rData['site_url'] = get_site_url();
		$rData['order_comments'] = $order->get_customer_note();
		$rData['order_date_created'] = $order->get_date_created();		
		
		//Metas
		$metas = get_post_meta($order_id);
		foreach ($metas as $key => $value) {
			if (substr($key, 0,1) == '_') {	$key = substr($key, 1); }
			if (is_array($value)) {	$value = $value[0];	}
			$rData[$key] = $value;
		}
		if (!empty($rData['order_total'])) {
			$rData['order_total'] = 'R$ '.number_format($rData['order_total'], 2, ',', ' ');
		}

		//itens
		$rData['products_name'] = '';
		foreach ($order->get_items() as $item_key => $item_values){
			$rData['products_name'] .= '- '.$item_values->get_name()."\n";
		}

	
		//payment
		$payment_data = $this->get_payment_data($order);
		$rData['payment_url'] = "\n".$payment_data['payment_url']."\n";



		//Filters
		$rData = apply_filters( 'wwp_replace_woocommerce_modelo', $rData, $order );

		//Replace Model
		foreach ($rData as $key => $value) {
    		$modelo = str_replace('{'.$key.'}', $value, $modelo);
		}

		return $modelo;
	}

	public function wwp_fs_license_manager_filter($rData,$order){
		if (wwp_is_plugin_active('FS-License-Manager/wp_wc_fs_license_manager.php')){
	      	$meta = get_post_meta( $order->get_id(), 'fslm_json_license_details', true);
        	if (!empty($meta)) {
        		$metaArr = json_decode($meta,true);
	            if (json_last_error() === JSON_ERROR_NONE) {
	                foreach ($metaArr as $key => $l) {
	                    $product = wc_get_product( $l['product_id'] );
	                    $produtoName = $product->get_title();
	                    if ($l['variation_id']!=0) {
	                        $variation = wc_get_product($l['variation_id']);
	                        $produtoName .= ' - ' . $variation->get_formatted_name();
	                    }
	                    $keys[] = array(
	                        'produto' => $produtoName,
	                        'key' => encrypt_decrypt('decrypt', $l['license_key'], ENCRYPTION_KEY, ENCRYPTION_VI)
	                    );
	                }
	            }
	            $table = '';
    			if (!empty($keys)) {
			        $table .= '*Códigos de Recarga*';
			        foreach ($keys as $key => $v) {
			            $table .= "\n";
			            $table .= '*'.$v['produto'].'*:';
			            $table .= ' '.$v['key'].'';
			        }
			        $table .= "\n";
			        $rData['fslm_codigos'] = $table;
	    		}
	    	}
		}
		return $rData;
	}

	public function wwp_tracking_filter($rData,$order){
		$rData['universal_tracking_code'] = '';
		$rData['universal_tracking_url'] = '';

		//Notificação de rastreio por transportadora
		if (wwp_is_plugin_active('wc-any-shipping-notify/wc-any-shipping-notify.php')) {
			if (isset($rData['wc_any_shipping_notify_tracking_code'])) {
				$codigos = unserialize ( $rData['wc_any_shipping_notify_tracking_code'] );
				$companies = get_option('wc_any_shipping_notify_available_companies', '');
				$urls = array();
				foreach ($codigos as $key => $v) {
					if (isset( $companies[$v] )) {
						$url = str_replace('{tracking_code}', $key, $companies[$v]['url']);
						if (isset( $rData['billing_cpf'])) {
							$url = str_replace('{cpf}', $rData['billing_cpf'], $url);
						}
						$urls[] = $url;
					}
				}
				$rData['universal_tracking_url'] = implode(' - ',$urls );
				$rData['universal_tracking_code'] =  implode(' - ',array_keys( $codigos ) );

				
			}
		}

		//Claudio Sanches - Correios for WooCommerce
		if (wwp_is_plugin_active('woocommerce-correios/woocommerce-correios.php')) {
			if (isset($rData['correios_tracking_code'])) {
				$rData['universal_tracking_code'] =  $rData['correios_tracking_code'];
				$rData['universal_tracking_url'] = 'https://linketrack.com/track?codigo='.$rData['correios_tracking_code'];
			}
		}

		//Advanced Shipment Tracking for WooCommerce
		if (wwp_is_plugin_active('woo-advanced-shipment-tracking/woocommerce-advanced-shipment-tracking.php')) {
			if (isset($rData['wc_shipment_tracking_items'])) {
				$data = @unserialize($rData['wc_shipment_tracking_items']);
				if ($data !== false and isset($data[0]['tracking_number'])) {
					global $wpdb;
					$table_name = $wpdb->prefix . 'woo_shippment_provider';
					$field_name = 'provider_url';
					$rData['universal_tracking_code'] =  $data[0]['tracking_number'];
					$rData['universal_tracking_url'] = $wpdb->get_col ( $wpdb->prepare( "SELECT {$field_name} FROM {$table_name} WHERE  ts_slug = %s", $data[0]['tracking_provider'] ));
					$rData['universal_tracking_url'] = str_replace('%number%', $rData['universal_tracking_code'], $rData['universal_tracking_url']);
					$rData['universal_tracking_url'] = $rData['universal_tracking_url'][0]; 
				}
			}
		}
		
		return $rData;
	}

	public function get_payment_data( $order = false ) {
	    if ( ! $order && ! $this->order ) {
	      return false;
	    } elseif ( ! $order && $this->order ) {
	      $order = $this->order;
	    }

	    $defaults = array(
	      'method_name' => $order->get_payment_method_title(),
	      'payment_url' => $order->needs_payment() ? $order->get_checkout_payment_url() : $order->get_view_order_url()
	    );

	    if ( 'pagseguro' === $order->get_payment_method() && 'Boleto' === $order->get_meta( 'Tipo de pagamento' ) ) {
	      $args = array(
	        'payment_url' => $order->get_meta( 'URL de pagamento.' ),
	      );
	    } elseif ( 'pix_gateway' === $order->get_payment_method() && class_exists( 'WC_Pix_Gateway' ) ) {
	      
	      $pix = new WC_Pix_Gateway;
	      $dados = $pix->generate_pix( $order->get_id() );
	      $args = array(
	        'payment_url' => $dados['instructions'] .' '. $dados['link']
	        
	      );
	    } elseif ( 'itau-shopline' === $order->get_payment_method() && class_exists( 'WC_Itau_Shopline' ) ) {
	      $args = array(
	        'payment_url' => WC_Itau_Shopline::get_payment_url( $order->get_order_key() ),
	        'expiry_time' => $order->get_meta( '_wc_itau_shopline_expiry_time' ),
	      );
	    } elseif ( 'woo-mercado-pago-ticket' === $order->get_payment_method() ) {
	      $args = array(
	        'payment_url' => $order->get_meta( '_transaction_details_ticket' )
	      );
	    } elseif ( 'woo-mercado-pago-pix' === $order->get_payment_method() ) {
	      $args = array(
	        'payment_url' => 'Código PIX para pagamento: ' . $order->get_meta( 'mp_pix_qr_code' )
	      );
	    } elseif ( 'bcash' === $order->get_payment_method() ) {
	      $args = array(
	        'payment_url' => add_query_arg( array( 'order_id' => $order->get_id() ), untrailingslashit( WC()->api_request_url( 'bcash_boleto_reminder' ) ) )
	      );
	    } elseif ( 'pagarme-banking-ticket' === $order->get_payment_method() ) {
	      $pagarme = get_post_meta( $order->get_id(), '_wc_pagarme_transaction_data', true );
	      $args = array(
	        'payment_url' => isset( $pagarme['boleto_url'] ) ? $pagarme['boleto_url'] : $defaults['payment_url']
	      );
	    } elseif ( 'wc_pagarme_pix_payment_geteway' === $order->get_payment_method() ) { //Pix Automático com Pagarme para WooCommerce
	      $pix = get_post_meta( $order->get_id(), '_wc_pagarme_pix_payment_qr_code', true );
	      $args = array(
	        'payment_url' => empty( $pix ) ? $defaults['payment_url'] : $pix
	      );
	    } elseif ( 'woo-moip-official' === $order->get_payment_method() && 'payBoleto' === $order->get_meta( '_moip_payment_type' ) ) {
	      $moip = get_post_meta( $order->get_id(), '_moip_payment_links', true );
	      $moip = maybe_unserialize( $moip );
	      $args = array(
	        'payment_url' => isset( $moip->payBoleto->printHref ) ? $moip->payBoleto->printHref : $defaults['payment_url']
	      );
	    } elseif ( in_array($order->get_payment_method(), ['paghiper', 'paghiper_billet', 'paghiper_pix'])) {
	      $paghiper = get_post_meta( $order->get_id(), 'wc_paghiper_data', true );
	      $paghiper = maybe_unserialize( $paghiper );
	      $url = '';
	      $url = isset( $paghiper['url_slip_pdf'] ) ? $paghiper['url_slip_pdf'] : $url;
	      $url = isset( $paghiper['pix_url'] ) ? $paghiper['pix_url'] : $url;
	      $args = array(
	        'payment_url' => $url!='' ? $url : $defaults['payment_url']
	      );
	    } elseif ( 'mundipagg-banking-ticket' === $order->get_payment_method() ) {
	      $mundipagg = get_post_meta( $order->get_id(), '_mundipagg_banking_ticket_data', true );
	      $mundipagg = maybe_unserialize( $mundipagg );
	      $args = array(
	        'payment_url' => isset( $mundipagg['url'] ) ? $mundipagg['url'] : $defaults['payment_url']
	      );
	    } elseif ( 'jrossetto_woo_cielo_webservice_boleto' === $order->get_payment_method() ) {
	      $cielo = get_post_meta( $order->get_id(), '_transacao_boletoURL', true );
	      $args = array(
	        'payment_url' => $cielo ? $cielo : $defaults['payment_url']
	      );
	    } elseif ( 'boletofacil' === $order->get_payment_method() ) {
	      $url  = get_post_meta( $order->get_id(), 'boletofacil_url', true );
	      $args = array(
	        'payment_url' => $url ? $url : $defaults['payment_url']
	      );
	    } elseif ( 'loja5_woo_itau_shopline' === $order->get_payment_method() ) {
	      $url  = get_post_meta( $order->get_id(), 'loja5_woo_itau_shopline_link_boleto', true );
	      $args = array(
	        'payment_url' => $url ? $url : $defaults['payment_url']
	      );
	    } elseif ( 'loja5_woo_bradesco_api_boleto' === $order->get_payment_method() ) {
	      $data = get_post_meta( $order->get_id(), 'loja5_woo_bradesco_api_boleto_dados', true );
	      $args = array(
	        'payment_url' => isset( $data['link_boleto'] ) ? $data['link_boleto'] : $defaults['payment_url']
	      );
	    } elseif ( 'juno-bank-slip' === $order->get_payment_method() ) {
	      $data = $order->get_meta( '_juno_payment_response' );
	      $args = array(
	        'payment_url' => isset( $data->charges[0]->installmentLink ) ? $data->charges[0]->installmentLink : $defaults['payment_url'],
	      );
	    } elseif ( 'widepay' === $order->get_payment_method() ) {
	      $url  = $order->get_meta( 'URLpagamento' );
	      $args = array(
	        'payment_url' => $url ? $url : $defaults['payment_url']
	      );
	    } elseif ( 'asaas-ticket' === $order->get_payment_method() ) {
	      $data = $order->get_meta( '__ASAAS_ORDER' );
	      $data = json_decode( $data );
	      $args = array(
	        'payment_url' => isset( $data->bankSlipUrl ) ? $data->bankSlipUrl : $defaults['payment_url']
	      );
	    } elseif ( 'loja5_woo_mercadopago_boleto' === $order->get_payment_method() ) {
	      $data = $order->get_meta( '_mercadopago_transacao' );
	      $args = array(
	        'payment_url' => isset( $data['transaction_details']['external_resource_url'] ) ? $data['transaction_details']['external_resource_url'] : $defaults['payment_url']
	      );
	    } elseif ( 'vindi-bank-slip' === $order->get_payment_method() ) {
	      $url = $order->get_meta( 'vindi_wc_invoice_download_url' );
	      $args = array(
	        'payment_url' => $url ? $url : $defaults['payment_url']
	      );
	    } else {
	      // se o método não está integrado, retorna false
	      return $defaults;
	    }

	    $args = wp_parse_args( $args, $defaults );
	    return $args;
	  }




	public function wwp_option_field($checkout) {
		 echo '<div id="wpp_check"><h2>' . __('Notificação por Whatsapp') . '</h2>';
		 woocommerce_form_field( 'wwp_notify_whatsapp', array(
	        'type'          => 'checkbox',
	        'class'         => array('input-checkbox'),
	        'label'         => __('Aceito ser notificado por whatsapp para este pedido'),
	        'required' => false,
	        ), 1);
		echo '</div>';

	}
	public function wwp_save_option_field( $order_id ) {
	    if ( ! empty( $_POST['wwp_notify_whatsapp'] ) ) {
	        update_post_meta( $order_id, 'wwp_notify_whatsapp', sanitize_text_field( $_POST['wwp_notify_whatsapp'] ) );
	    }
	}


	public function phone_validation($billing_phone, $code_country){
		if (empty($billing_phone)) {
			return false;
		}
        $nom = trim($billing_phone);
        $nom = filter_var($nom, FILTER_SANITIZE_NUMBER_INT);
        $nom = str_replace("-","",$nom);
        $nom = str_replace("(","",$nom);
        $nom = str_replace(")","",$nom);
        $nom = str_replace(" ","",$nom);
        if (strpos($nom, '+'.$code_country) === FALSE) {
         	$nom = '+'.$code_country.$nom;
        }
        
        return $nom;
    }



	
}