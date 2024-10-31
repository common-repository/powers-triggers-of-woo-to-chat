<?php
class Woowhatspowers_Woocommerce {
	public $triggers = array(
		'after_checkout' => array(
			'ativo' => '',
			'titulo' => 'Assim que o pedido for feito', 
			'descricao' => '',
			'action' => 'woocommerce_checkout_order_processed',
			'modelo' => '',
			'file' => '',
		),
		'processing_order' => array(
			'ativo' => '',
			'titulo' => 'O pedido for pago! (Processando)', 
			'descricao' => '',
			'action' => 'woocommerce_order_status_processing',
			'modelo' => '',
			'file' => '',
		),
		'hold_order' => array(
			'ativo' => '',
			'titulo' => 'O pedido estiver em aguardando ou pendente', 
			'descricao' => 'Configure até 4 gatilhos para enviar mensagens quando o pedido estiver ainda em "aguardando" ou "pendente". Escolha o intervalo de cada mensagem baseado na data da criação do pedido.',
			'action' => 'reminder',
			'intervalo' => array('off','off','off','off'),
			'modelo' => array('','','',''),
		),
		'cancel_order' => array(
			'ativo' => '',
			'titulo' => 'O pedido for cancelado', 
			'descricao' => '',
			'action' => 'woocommerce_order_status_cancelled',
			'modelo' => '',
			'file' => '',
		),
		'failed_order' => array(
			'ativo' => '',
			'titulo' => 'O pedido falhou', 
			'descricao' => 'Durante a tentativa de pagamento, a operação pode falhar.',
			'action' => 'woocommerce_order_status_failed',
			'modelo' => '',
			'file' => '',
		),
		'completed_order' => array(
			'ativo' => '',
			'titulo' => 'O pedido for concluido!', 
			'descricao' => '',
			'action' => 'woocommerce_order_status_completed',
			'modelo' => '',
			'file' => '',
		),
		'refunded_order' => array(
			'ativo' => '',
			'titulo' => 'O pedido for reembolsado', 
			'descricao' => '',
			'action' => 'woocommerce_order_status_refunded',
			'modelo' => '',
			'file' => '',
		), 
	);

	function __construct() {
		$this->wpp_load_triggers();
	}

	function wpp_load_triggers(){
		$this->wpp_load_custom_status();
		$settings = get_option( 'wwp_triggers', '' );
		if (!empty($settings)) {
			$settings = json_decode($settings);
			foreach ($this->triggers as $tKey => $tValue) {
				$this->triggers[$tKey]['ativo'] = isset($settings->$tKey->ativo) ? $settings->$tKey->ativo : '';
				if (is_array($this->triggers[$tKey]['modelo']) ) {
					if (isset($settings->$tKey->modelo) and is_array($settings->$tKey->modelo) ) {
						$this->triggers[$tKey]['modelo'] = $settings->$tKey->modelo;
					}					
				} else {
					$this->triggers[$tKey]['modelo'] = isset($settings->$tKey->modelo) ? $settings->$tKey->modelo : $this->triggers[$tKey]['modelo'];		
				}

				if (isset($this->triggers[$tKey]['file'])) {
					$this->triggers[$tKey]['file'] = isset($settings->$tKey->file) ? $settings->$tKey->file : $this->triggers[$tKey]['file'];
				}
				
				if (isset($this->triggers[$tKey]['intervalo'])) {
					$this->triggers[$tKey]['intervalo'] = isset($settings->$tKey->intervalo) ? $settings->$tKey->intervalo : $this->triggers[$tKey]['intervalo'];
				}
			}	
		}	
	}

	function wpp_load_custom_status(){
		$custom_status = wc_get_order_statuses();
		$statusPadrao = array( 'wc-pending','wc-processing','wc-on-hold','wc-completed','wc-cancelled','wc-refunded','wc-failed');
		foreach ($custom_status as $key => $status) {
			if (in_array($key, $statusPadrao)) { continue;	}
			$this->triggers['custom_status_'.$key] = array(
					'ativo' => '',
					'titulo' => 'Status customizado: '.$status,
					'descricao' => '',
					'action' => $key,
					'modelo' => '',
					'file' => '',
				);
		}
	}

	function wwp_save_triggers ($dados){
		if (!empty($dados)) {
			$saveTriggers = array();
			foreach ($this->triggers as $tKey => $tValue) {
				if (isset($dados[$tKey.'_modelo']) and count($dados[$tKey.'_modelo'])==1) {
					$saveTriggers[$tKey]['modelo'] = $dados[$tKey.'_modelo'][0];
				} else{
					$saveTriggers[$tKey]['modelo'] = $dados[$tKey.'_modelo'];		
				}
				if (isset($dados[$tKey.'_file']) and count($dados[$tKey.'_file'])==1) {
					$saveTriggers[$tKey]['file'] = $dados[$tKey.'_file'][0];
				} else{
					$saveTriggers[$tKey]['file'] = $dados[$tKey.'_file'];		
				}
				if (isset($dados[$tKey.'_intervalo']) and count($dados[$tKey.'_intervalo'])==1) {
					$saveTriggers[$tKey]['intervalo'] = $dados[$tKey.'_intervalo'][0];
				} else{
					$saveTriggers[$tKey]['intervalo'] = $dados[$tKey.'_intervalo'];		
				}
				$saveTriggers[$tKey]['action'] = $this->triggers[$tKey]['action'];
				$saveTriggers[$tKey]['ativo'] = isset($dados[$tKey.'_ativo']) ? $dados[$tKey.'_ativo'] : '';
				
			}
			$saveTriggers = json_encode($saveTriggers);
			$up = update_option('wwp_triggers', $saveTriggers,FALSE);
			if (!$up) {
				$up = add_option('wwp_triggers', $saveTriggers);
			}
		}
	}

}