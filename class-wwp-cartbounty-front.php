<?php
if (!class_exists('Woowhatspowers_cartbounty')) {
    global $wwp_db_path;
    require_once($wwp_db_path . 'include/class-wwp-cartbounty.php');
}
class Woowhatspowers_cartbounty_Front extends  Woowhatspowers_cartbounty{


	function __construct() {
		parent::__construct();
		if (!empty($this->acoes['cron'])) {
			add_action( 'wwp_reminder_action', array( $this, 'wwp_send_cron' ) );
		}
	}

	function wwp_send_cron(){
		global $wpdb;
		$table_prefix = $wpdb->prefix;
		$args = $wpdb->prepare("SELECT * FROM `".$table_prefix."cartbounty` WHERE phone != '' AND mail_sent = 1 LIMIT 0,5" );
		$carts = $wpdb->get_results($args);
		$updateIds = array();
		if (! empty($carts)) {
			foreach ($carts as $c) {
				$template = $this->wpp_replace_template($c,$this->acoes['template']);
				if ($this->whatsapp->sendMessage($c->phone,$template)) {
					$updateIds[] = $c->id;
	        	};
			}
			if (!empty($updateIds)) {
				$wpdb->query(
					"UPDATE ".$table_prefix."cartbounty
					SET mail_sent = 2
					WHERE id in  (".implode(',', $updateIds).")"
				);
			}
		}
	}

	function recoverCart($idcode){
		if (!class_exists('PseudoCrypt')) {
	        global $wwp_db_path;
	        require_once($wwp_db_path . 'include/class-wwp-pseudocrypt.php');
	    }
	    $idcode = PseudoCrypt::unhash($idcode);
	    global $wpdb;
	    $table_prefix = $wpdb->prefix;
	    $args = $wpdb->prepare('SELECT * FROM `'.$table_prefix.'cartbounty` WHERE id ='.$idcode);
	    $cart = $wpdb->get_row($args);
	    if (! empty($cart) and ! empty($cart->cart_contents) ) {
	        $cart->cart_contents = (array) unserialize( $cart->cart_contents );
	        if( function_exists('WC') ){
	            WC()->cart->empty_cart();
	            foreach ($cart->cart_contents as $produto) {
	                WC()->cart->add_to_cart( $produto['product_id']);
	            }
	            return true;
	        }
	    }
	    return false;
	}
	


	
}