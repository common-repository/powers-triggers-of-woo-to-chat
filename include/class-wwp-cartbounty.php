<?php
class Woowhatspowers_cartbounty {
	
	public $acoes = array();
	public $whatsapp;

	function __construct() {
		$this->wpp_load();
	}

	function wpp_load(){
		$acoes = get_option( 'wwp_cartbounty', '' );
		if (!empty($acoes)) {
			$this->acoes = $acoes;
		}
		$this->whatsapp = new Woowhatspowers_Whastapp;
	}

	function wpp_save($dados = array()){
		if (empty($dados)) { return false;	}
		if (!is_array($dados) ) { $dados[] = $dados; }
		$salvar = array();
		if (isset($dados['wwp_textarea']) and !empty($dados['wwp_textarea']) ) {
			$dados['wwp_cron'] = isset($dados['wwp_cron']) ? $dados['wwp_cron'] : '';
			$salvar = array('template' => $dados['wwp_textarea'], 'cron' => $dados['wwp_cron']);
			$up = update_option('wwp_cartbounty', $salvar,FALSE);
			if (!$up) {
				$up = add_option('wwp_cartbounty', $salvar);
			}
		}
		
		$this->wpp_load();
		return $up;
	}

	function wpp_replace_template($cart,$template){
		if (!class_exists('PseudoCrypt')) {
    		global $wwp_db_path;
    		require_once($wwp_db_path . 'include/class-wwp-pseudocrypt.php');
    	}
    	$idEncrypt = PseudoCrypt::hash($cart->id, 6);
    	foreach ($cart as $key => $value) {
    		$template = str_replace('{'.$key.'}', $value, $template);
    	}
    	$template = str_replace('{cart_link}',   wc_get_cart_url().'?wwp_r='.$idEncrypt, $template);

    	if (!empty($cart->cart_contents)) {
    		$cart_contents = array();
        	$cart->cart_contents = unserialize($cart->cart_contents);
        	foreach ($cart->cart_contents as $key => $value) {
        		$cart_contents[] = $value['product_title'];
        	}
    	}
    	$template = str_replace('{cart_contents_all}', implode(', ', $cart_contents) , $template);

    	return $template;

	}

	function wpp_send($wwp_id){
		$wwp_template_cartbounty = $this->acoes['template'];
		if (  empty($wwp_template_cartbounty))  { 
			if (wp_doing_ajax()) {
	        	exit('false');
	    	}
	    	return false;
		}
			
		global $wpdb;
		$table_prefix = $wpdb->prefix;
        $args = $wpdb->prepare('SELECT * FROM `'.$table_prefix.'cartbounty` WHERE id ='.$wwp_id);
        $cart = $wpdb->get_row($args);
        if (! empty($cart) ) {
        	
        	$wwp_template_cartbounty = $this->wpp_replace_template($cart,$wwp_template_cartbounty);
        	if ($this->whatsapp->sendMessage($cart->phone,$wwp_template_cartbounty)) {
        		if ( wp_doing_ajax()) {
        			exit('true');		
        		}
        		return true;
        	};
        }

	}


}