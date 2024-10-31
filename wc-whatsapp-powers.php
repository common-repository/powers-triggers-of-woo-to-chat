<?php 
/**
 * Plugin Name: Powerful Auto Chat
 * Plugin URI:	https://wordpress.org/plugins/powers-triggers-of-woo-to-chat/
 * Description:	Automatiza envio de mensagens Whatsapp.
 * Version:		1.9.8
 * Author:		Felipe Peixoto
 * Author URI:	http://felipepeixoto.tecnologia.ws/projetos/plugins-para-wordpress/notificacoes-de-pedidos-por-whatsapp/
 */
if ( ! defined( 'WPINC' ) ) { die; }


include('include/class-wwp-whatsapp.php');
include('include/class-wwp-log.php');



register_activation_hook( __FILE__, 'pac_active_plugin' );
register_deactivation_hook( __FILE__, 'wwp_deactive_plugin' );
add_filter( 'postmeta_form_limit', function( $limit ) {
    return 100;
});



//FUNCOES
function wwp_is_plugin_active( $plugin ) {
    return in_array( $plugin, (array) get_option( 'active_plugins', array() ) );
}
function pac_admin_notices($text = ''){
    add_action('pac_admin_notices', function() use ($text) {
    ?>
        <div class="notice notice-info is-dismissible"><p><?php echo $text ?></p></div>
    <?php
    });
}


//BD VERSION
global $wwp_db_version;
global $wwp_db_path;
$wwp_db_path = plugin_dir_path(__FILE__);
$wwp_db_version = '0.2';
function wwp_update_db_check() {
    global $wwp_db_version;
    if ( get_site_option( 'wwp_db_version' ) != $wwp_db_version ) {
        //wwp_create_tables();
    }
}
add_action( 'plugins_loaded', 'wwp_update_db_check',0 );

function wwp_create_tables() {

    add_option( "wwp_db_version", $wwp_db_version );
}

if (!function_exists('pac_active_plugin')) {
    function pac_active_plugin() {
        global $wwp_db_version;
        $args = array( true );
        if (! wp_next_scheduled ( 'wwp_reminder_action', $args )) {
            wp_schedule_event( time(), 'hourly', 'wwp_reminder_action', $args );
        }
        if (! wp_next_scheduled ( 'wwp_status_check', $args )) {
            wp_schedule_event( time(), 'daily', 'wwp_status_check', $args );
        }
        wwp_update_db_check();
        
    }
}

function pac_deactivation_plugin() {
    wp_clear_scheduled_hook( 'wwp_reminder_action' );
}


$args = array( true );
if (! wp_next_scheduled ( 'wwp_reminder_action', $args )) {
    wp_schedule_event( time(), 'hourly', 'wwp_reminder_action', $args );
}
if (! wp_next_scheduled ( 'wwp_status_check', $args )) {
    wp_schedule_event( time(), 'daily', 'wwp_status_check', $args );
}
add_action( 'wwp_status_check', array( new Woowhatspowers_Whastapp, 'testaConexao' ) );



//ADMIN
$wwp_path = plugin_dir_path(__FILE__);
if (is_admin()){
	add_action( 'init', 'wwp_init_admin',  PHP_INT_MAX  );
}
function wwp_init_admin() {
    require plugin_dir_path( __FILE__ ) . 'admin/class-wwp-admin.php';
	$wwp_admin = new Woowhatspowers_Admin();
}
function wwp_status_notice() {
    $msg = '';
    switch (get_option( 'wwp_status', '' )) {
        case 'offline':
            $msg = 'O celular aparece estar sem internet. Tente ligar a tela do celular e verifique se esta conectado a rede.';
            break;
        case 'desconectado':
            $msg = 'Será necessário ler o QRCode novamente, entre em contato com o suporte.';
            break;
    }
?>
    <div class="notice notice-error" style="display: flex; justify-content: left; align-items: center;"> 
        <div class="notice-image">
            <img style="max-width: 90px;" src="https://ps.w.org/powers-triggers-of-woo-to-chat/assets/icon-128x128.png?rev=2460034" alt="Powerful Auto Chat" >
        </div>
        <div class="notice-content" style="margin-left: 15px;">
            <p><strong>Whatsapp desconectado!</strong> <?php echo $msg; ?> Depois disso, acesse as configurações e salve novamente para testar a conexão.</p>
        </div>
    </div>
<?php     
}
if (get_option( 'wwp_status', '' ) != 'online') {
    add_action('admin_notices', 'wwp_status_notice');
}



add_action('init', function() {

	if (wwp_is_plugin_active('woocommerce/woocommerce.php')){
		include_once('class-wwp-woocommerce-front.php');
		new Woowhatspowers_Woocommerce_Front();
        
        
        if (wwp_is_plugin_active('woo-save-abandoned-carts/cartbounty-abandoned-carts.php')
            or 
            wwp_is_plugin_active('woo-save-abandoned-carts-pro/cartbounty-pro-abandoned-carts.php')){
            

            global $wwp_db_path;
            require_once($wwp_db_path.'class-wwp-cartbounty-front.php');
            $wwp_cartbounty = new Woowhatspowers_cartbounty_Front();
            //Recovery cart
            if (isset($_GET['wwp_r'])) {
                $wwp_id = $_GET['wwp_r'];
                if ($wwp_cartbounty->recoverCart($wwp_id)) {
                    wp_redirect(wc_get_cart_url());
                    exit();
                }
            }
        }
        if (wwp_is_plugin_active('woo-reminder/woo-reminder.php')){
            include_once('class-wwp-wooreminder.php');
             add_action('wrmdr_mail_time', array(new Woowhatspowers_wooreminder_Front, 'wwp_send_cron'),1);
          }

	}

    if (wwp_is_plugin_active('contact-form-7/wp-contact-form-7.php')){
        include_once('class-wwp-contactform7-front.php');
        new Woowhatspowers_Contactform7_Front();
    }

    //JetEngine
    if (wwp_is_plugin_active('jet-engine/jet-engine.php')){
        if (!class_exists('Woowhatspowers_jetbooking_Front')) {
            global $wwp_db_path;
            require_once($wwp_db_path . 'class-wwp-jetbooking-front.php');
        }
        $j = new Woowhatspowers_jetbooking_Front();
        $j->setActions();
        
    }

    //Woofunnels ss
    if (wwp_is_plugin_active('wp-marketing-automations-connectors/wp-marketing-automations-connectors.php')){
        if (!class_exists('Woowhatspowers_woofunnels_Front')) {
            global $wwp_db_path;
            require_once($wwp_db_path . 'class-wwp-woofunnels-front.php');
        }
        $j = new Woowhatspowers_woofunnels_Front();
        $j->setActions();
        
    }
    if (isset($_GET['remind-test'])) {
        do_action( 'wwp_reminder_action' );
        exit();
    }
});








?>