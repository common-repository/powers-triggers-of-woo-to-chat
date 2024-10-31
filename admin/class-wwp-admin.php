<?php
class Woowhatspowers_Admin {

	private $whatsapp;
	

	function __construct() {

		$this->whatsapp = new Woowhatspowers_Whastapp;

		//ajax
		add_action( 'wp_ajax_wwp_save_settings', array( $this, 'wwp_save_settings' ) );
		add_action( 'wp_ajax_wwp_view_nova_campanha', array( $this, 'wwp_view_nova_campanha' ) );
		add_action( 'wp_ajax_wwp_view_novo_publico', array( $this, 'wwp_view_novo_publico' ) );
		add_action( 'wp_ajax_wwp_view_table_publico', array( $this, 'wwp_view_table_publico' ) );
		add_action( 'wp_ajax_wwp_view_disparar', array( $this, 'wwp_view_disparar' ) );
		add_action( 'wp_ajax_wwp_view_cartbounty', array( $this, 'wwp_view_cartbounty' ) );
		add_action( 'wp_ajax_wwp_send_from_cartbounty', array( $this, 'wwp_send_from_cartbounty' ) );
		add_action( 'wp_ajax_wwp_wooreminders', array( $this, 'wwp_send_from_wooreminders' ) );

		
		
		//outros
		add_action( 'woocommerce_admin_order_data_after_shipping_address', array( $this, 'wwp_woocommerce_chk_perms' ), 1000, 3 );
		add_action('save_post',array( $this, 'wwp_woocommerce_chk_perms_save' ),1,2);

		if (! wp_doing_ajax()) {
			add_action( 'admin_menu', array( $this, 'wwp_add_menu' ) );
		}
		if (wwp_is_plugin_active('woo-save-abandoned-carts/cartbounty-abandoned-carts.php') and $_GET['page']=='cartbounty' and ( !isset($_GET['tab']) or $_GET['tab']=='carts')){
			$this->wwp_mod_cartbounty_abandoned_carts();
		}
		if (wwp_is_plugin_active('woo-reminder/woo-reminder.php') and $_GET['page']=='woo-reminder'){
			$this->wwp_mod_wooreminders();
		}


	}

	function wwp_add_menu (){
		$subMenus = array();

		/*Woocomerce*/
		if (wwp_is_plugin_active('woocommerce/woocommerce.php')){
			$subMenus[] = array('woowhatspowers-woocommerce','Notificações Pedidos',array( $this, 'wwp_view_woocommerce' ),10);	
			//$subMenus[] = array('woowhatspowers-campanhas','Campanhas (BETA)',array( $this, 'wwp_view_woocommerce_campanha' ),10);

		}

		/*Contact*/
		if (wwp_is_plugin_active('contact-form-7/wp-contact-form-7.php')){
			$subMenus[] = array('woowhatspowers-contact-form-7','Contact Form 7',array( $this, 'wwp_view_contactform7' ),20);	
		}
		//JetEngine
		if (wwp_is_plugin_active('jet-engine/jet-engine.php')){
			$subMenus[] = array('woowhatspowers-jet-booking','Jet Engine Template',array( $this, 'wwp_view_jetbooking' ),20);
		}

		//Woofunnels
		if (wwp_is_plugin_active('wp-marketing-automations-connectors/wp-marketing-automations-connectors.php')){
			$subMenus[] = array('woowhatspowers-woofunnels','Woofunnels Template',array( $this, 'wwp_view_woofunnels' ),20);
		}


		$subMenus[] = array('woowhatspowers-settings','Configurações',array( $this, 'wwp_view_settings' ),100);

		add_menu_page( 'Auto WhatsApp', 'Auto WhatsApp', 'manage_options', 'woowhatspowers', $subMenus[0][2], 'dashicons-testimonial',10);


		foreach ($subMenus as $menu) {
			add_submenu_page( 'woowhatspowers', $menu[1], $menu[1],'manage_options', $menu[0],$menu[2],$menu[3]);
		}
	}

	/*Tela de configurações*/
	function wwp_view_settings (){
		wp_enqueue_script(  'wwp-admin-script', plugin_dir_url( __FILE__ ) . 'js/script.js', array('jquery'), rand(0,1000), true );
		wp_enqueue_style( 'wwp-admin-style', plugin_dir_url( __FILE__ ) . 'css/style.css', '', rand(0,1000), false );
		$wwp_licenca = $this->whatsapp->getLicenca();
		$wwp_key = $this->whatsapp->getKey();
		$wwp_code = $this->whatsapp->getCode();
		$wwp_testnumber = $this->whatsapp->getTestnumber();
		require_once plugin_dir_path(dirname(__FILE__)).'admin/views/wwp-settings.php';
	}
	public function wwp_save_settings (){
		if (!$_POST or empty($_POST['wwp_key']) or empty($_POST['wwp_code'])) { echo 'false'; exit(); }
		$setings = array(
			'wwp_licenca' =>  sanitize_text_field( trim($_POST['wwp_licenca'])),
			'wwp_key' =>  sanitize_text_field( trim($_POST['wwp_key'])),
			'wwp_code' =>  sanitize_text_field( trim($_POST['wwp_code'])),
			'wwp_testnumber' =>  sanitize_text_field( trim($_POST['wwp_testnumber']))
		);
		if ($this->whatsapp->saveSettings($setings)) {
			echo 'true';
			exit();
		}
		echo 'false';
		exit();
	}

	//Woocommerce
	function wwp_view_woocommerce (){
		if (!$this->whatsapp->isSettings()) {
			$this->wwp_view_settings();
			return; 
		}
		require_once plugin_dir_path(dirname(__FILE__)).'include/class-wwp-woocommerce.php';
		$woo = new Woowhatspowers_Woocommerce();
		if ($_POST) {
			$woo->wwp_save_triggers($_POST);
			$woo->wpp_load_triggers();
		}
		$triggers = $woo->triggers;
		wp_enqueue_media();
		wp_enqueue_script(  'wwp-admin-script', plugin_dir_url( __FILE__ ) . 'js/script.js', array('jquery'), rand(0,1000), true );
		wp_enqueue_style( 'wwp-admin-style', plugin_dir_url( __FILE__ ) . 'css/style.css', '', rand(0,1000), false );
		wp_enqueue_script( 'jquery-ui-accordion' );
		wp_enqueue_style( 'jquery-ui-style' );

		require_once plugin_dir_path(dirname(__FILE__)).'admin/views/wwp-woocommerce.php';
	}
	function wwp_woocommerce_chk_perms(){
		global $post;
		$wwp_woocommerce_chk_perms = get_post_meta($post->ID,'wwp_notify_whatsapp', true);
		$checked = $wwp_woocommerce_chk_perms==false ? '' : 'checked="checked"';
?>
	<div>
		<h3><input type="checkbox" value="1" <?php echo $checked; ?> name="wwp_woocommerce_chk_perms"> Permissão para enviar notificação por Whatsapp</h3>
		<input type="hidden" value="1" name="wwp_woocommerce_update_flag">
	</div>
<?php
	}

	function wwp_woocommerce_chk_perms_save($post_id, $post){
		$post_type = $post->post_type;
	    if($post_id && $post_type=='shop_order' and isset($_POST['wwp_woocommerce_update_flag'])) { 
	    	if(isset($_POST['wwp_woocommerce_chk_perms'])){
	    		update_post_meta($post_id,'wwp_notify_whatsapp',1);
	    	} else {
	    		delete_post_meta($post_id,'wwp_notify_whatsapp');
	    	}
	    }
	}


	//Woocommerce WooReminders
	function wwp_mod_wooreminders (){
		wp_enqueue_script('wwp-mod-wooreminders', plugin_dir_url( __FILE__ ) . 'js/mod_wooreminders.js', array('jquery'), rand(0,1000), true );
		wp_localize_script( 'wwp-mod-wooreminders', 'wwp_mod_wooreminders_object',
	        array( 
	            'wpp_enable' => get_option( 'wpp_enable_wooreminders', false ),
	        )
	    );
	}

	//Woocommerce Cartbounty
	function wwp_mod_cartbounty_abandoned_carts (){
		wp_enqueue_script(  'wwp-cartbounty-script-mod', plugin_dir_url( __FILE__ ) . 'js/mod_cartbounty_abandoned_carts.js', array('jquery'), rand(0,1000), true );	
		global $wwp_db_path;
		require_once $wwp_db_path.'include/class-wwp-cartbounty.php';
		$wwp_cartbounty = new Woowhatspowers_cartbounty();
		if ($_POST and !empty($_POST['wwp_textarea'])) {
			$wwp_cartbounty->wpp_save($_POST);
		}

		if ($_GET and (!empty($_GET['action']) or !empty($_GET['action2'])) and ($_GET['action']=='whatsapp' or $_GET['action2']=='whatsapp')  ) {
			$ids = $_GET['id'];
			if (!is_array($ids)) {
				$ids = array($ids);
			}
			$enviados = 0;
			$erros = 0;
			foreach ($ids as $v) {
				if ($wwp_cartbounty->wpp_send($v)) {
					$enviados++;
				} else{
					$erros++;
				}
			}
			add_action('admin_notices', function() use ($enviados, $erros){
			?>
			<div class="notice notice-info is-dismissible">
			    <p>
			    	Finalizado!
			    	<b><?php echo $enviados; ?></b> enviado(s) com sucesso.
			    	<b><?php echo $erros; ?></b> enviado(s) com erro.
			    </p>
			</div>
			<?php
			});
			

		}
		
		add_action( 'admin_footer', function(){
			
			echo ' <input type="hidden" id="wwp_template_cartbounty" value="'.$wwp_template_cartbounty.'" /> ';
		}, 10, 1 );
	}
	function wwp_view_cartbounty(){
		require_once plugin_dir_path(dirname(__FILE__)).'include/class-wwp-cartbounty.php';
		$wwp_cartbounty = new Woowhatspowers_cartbounty();
		$wwp_template_cartbounty = $wwp_cartbounty->acoes['template'];
		$wwp_cron_cartbounty = $wwp_cartbounty->acoes['cron'];
		require_once plugin_dir_path(dirname(__FILE__)).'admin/views/wwp-cartbounty.php';
		exit();
	}

	function wwp_send_from_wooreminders($wpp_enable = ''){
		if ( ( !$_POST or empty($_POST['wpp_enable']) ) and empty($wpp_enable)) { return;}
		if (!empty($_POST['wpp_enable'])) {
			$wpp_enable = $_POST['wpp_enable'];
		}
		
        if (update_option('wpp_enable_wooreminders', $wpp_enable, FALSE)) {
            $up = add_option('wpp_enable_wooreminders', $wpp_enable);
        }
				
		if ($up=== FALSE) {
			return false;
		}
    	return true;
	}

	function wwp_send_from_cartbounty($wwp_id = ''){
		if ( ( !$_POST or empty($_POST['wwp_id']) ) and empty($wwp_id)) { return;}
		if (!empty($_POST['wwp_id'])) {
			$wwp_id = $_POST['wwp_id'];
		}
		if (!class_exists('Woowhatspowers_cartbounty')) {
    		global $wwp_db_path;
    		require_once plugin_dir_path(dirname(__FILE__)).'include/class-wwp-cartbounty.php';
    	}
		$wwp_cartbounty = new Woowhatspowers_cartbounty();
		$send = $wwp_cartbounty->wpp_send($wwp_id);
		
		if ($send=== FALSE) {
			return false;
		}
    	return true;
	}


	/*JetEngine*/
	function wwp_view_jetbooking (){
		wp_enqueue_script(  'wwp-admin-script', plugin_dir_url( __FILE__ ) . 'js/script.js', array('jquery'), rand(0,1000), true );
		wp_enqueue_style( 'wwp-admin-style', plugin_dir_url( __FILE__ ) . 'css/style.css', '', rand(0,1000), false );
		wp_enqueue_script( 'jquery-ui-accordion' );
		wp_enqueue_style( 'jquery-ui-style' );
		require_once plugin_dir_path(dirname(__FILE__)).'include/class-wwp-jetbooking.php';
		$j = new Woowhatspowers_Jetbooking();

		if (isset($_POST) ) {
			if (!$j->wpp_saveAcoes($_POST) === false) {
				pac_admin_notices('Ações salvas com sucesso!');
			}
		}
		$acoes = $j->wpp_getAcoes();
		require_once plugin_dir_path(dirname(__FILE__)).'admin/views/wwp-jetbooking.php';
	}

	/*Woofunnels*/
	function wwp_view_woofunnels (){
		wp_enqueue_script(  'wwp-admin-script', plugin_dir_url( __FILE__ ) . 'js/script.js', array('jquery'), rand(0,1000), true );
		wp_enqueue_style( 'wwp-admin-style', plugin_dir_url( __FILE__ ) . 'css/style.css', '', rand(0,1000), false );
		wp_enqueue_script( 'jquery-ui-accordion' );
		wp_enqueue_style( 'jquery-ui-style' );
		require_once plugin_dir_path(dirname(__FILE__)).'include/class-wwp-woofunnels.php';
		$j = new Woowhatspowers_Woofunnels();

		if (isset($_POST) ) {
			if (!$j->wpp_saveAcoes($_POST) === false) {
				pac_admin_notices('Ações salvas com sucesso!');
			}
		}
		$acoes = $j->wpp_getAcoes();
		require_once plugin_dir_path(dirname(__FILE__)).'admin/views/wwp-woofunnels.php';
	}



	//contactform7
	public function wwp_view_contactform7 (){
		if (!$this->whatsapp->isSettings()) {
			$this->wwp_view_settings();
			return; 
		}
		require_once plugin_dir_path(dirname(__FILE__)).'include/class-wwp-contactform7.php';
		$modulo = new Woowhatspowers_Contactform7();
		if ($_POST) {
			$modulo->wwp_save_triggers($_POST);
			$modulo->wpp_load_triggers();
		}
		$triggers = $modulo->triggers;
		
		wp_enqueue_script(  'wwp-admin-script', plugin_dir_url( __FILE__ ) . 'js/script.js', array('jquery'), rand(0,1000), true );
		wp_enqueue_style( 'wwp-admin-style', plugin_dir_url( __FILE__ ) . 'css/style.css', '', rand(0,1000), false );
		wp_enqueue_script( 'jquery-ui-accordion' );
		wp_enqueue_style( 'jquery-ui-style' );

		require_once plugin_dir_path(dirname(__FILE__)).'admin/views/wwp-contactform7.php';
	}




	

	private function celular($telefone){
		if (empty($telefone)) {
			 return false;
		}
	    $telefone= trim(str_replace('/', '', str_replace(' ', '', str_replace('-', '', str_replace(')', '', str_replace('(', '', $telefone))))));

	    $regexCel = '/[0-9]{2}[6789][0-9]{3,4}[0-9]{4}/'; // Regex para validar somente celular
	    if (preg_match($regexCel, $telefone)) {
	        return true;
	    }else{
	        return false;
	    }
	}

	
}