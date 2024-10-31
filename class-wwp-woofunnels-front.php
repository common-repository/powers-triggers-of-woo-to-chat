<?php
if (!class_exists('Woowhatspowers_Woofunnels')) {
    global $wwp_db_path;
    require_once($wwp_db_path . 'include/class-wwp-woofunnels.php');
}
class Woowhatspowers_woofunnels_Front extends  Woowhatspowers_Woofunnels{

	function __construct() {
		parent::__construct();
	}

	function setActions(){
		$acoes = parent::wpp_getAcoes();
        if (!empty($acoes)) {
            foreach ($acoes as $key => $v) {
                add_action( 'pac/'.$v['slug'], function($args) use ($v){
                	if ($v['slug']=='sendmetest') {
                		ob_start();
						var_dump($args);
						$var_d = ob_get_clean();

                		$to = get_bloginfo('admin_email');
					    $subject = 'Teste de ação do PAC';
					    $message = $var_d;
					    wp_mail($to, $subject, $message );
					    return ;
                	}
                    
                	$keys = ['whatsapp','phone','cell_phone'];
                	$destinatario = false;
                	foreach ($keys as $k) {
                		if (isset($args[$k]) and !empty($args[$k])) {
                			$destinatario = $args[$k];
                			break;
                		}
                	}

                	if ($destinatario !== FALSE ) {
                		$template = $v['template'];
						foreach ($args as $key => $value) {
							$template = str_replace('['.$key.']', $value, $template);
						}
						if (!class_exists('Woowhatspowers_Whastapp')) {
						    global $wwp_db_path;
						    require_once($wwp_db_path . 'include/class-wwp-whastapp.php');
						}
						$w = new Woowhatspowers_Whastapp();
						$f = $w->sendMessage($destinatario,$template);
                	}
                }, 10,3);
            }
        }
	}
}