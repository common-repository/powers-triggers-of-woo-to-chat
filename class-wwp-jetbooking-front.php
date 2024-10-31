<?php
if (!class_exists('Woowhatspowers_Jetbooking')) {
    global $wwp_db_path;
    require_once($wwp_db_path . 'include/class-wwp-jetbooking.php');
}
class Woowhatspowers_jetbooking_Front extends  Woowhatspowers_Jetbooking{

	function __construct() {
		parent::__construct();
	}

	function setActions(){
		$acoes = parent::wpp_getAcoes();
        if (!empty($acoes)) {
            foreach ($acoes as $key => $v) {
                add_action( 'jet-engine-booking/powerful-auto-chat/'.$v['slug'], function($data,$form,$notifications ) use ($v){
                	if (isset($data['whatsapp']) and !empty($data['whatsapp'])) {
                		$template = $v['template'];
						foreach ($data as $key => $value) {
							$template = str_replace('['.$key.']', $value, $template);
						}
						if (!class_exists('Woowhatspowers_Whastapp')) {
						    global $wwp_db_path;
						    require_once($wwp_db_path . 'include/class-wwp-whastapp.php');
						}
						$w = new Woowhatspowers_Whastapp();
						$f = $w->sendMessage($data['whatsapp'],$template);
                	}
                }, 10,3);
            }
        }
	}
}