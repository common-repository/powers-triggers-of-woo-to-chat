<?php
class Woowhatspowers_Contactform7_Front {
	public $settings;
	private $whatsapp;
	private $log;
	
	function __construct() {


		$this->whatsapp = new Woowhatspowers_Whastapp;
		$this->log = new Woowhatspowers_log;

		$this->wwp_load_action();
		
	}

	private function wwp_load_action() {

		if (empty($this->whatsapp->getKey())) {
			return false;
		}
		$settings = get_option( 'wwp_contactform7_triggers', '' );
		if (!empty($settings)) {
			$settings = json_decode($settings);
			$this->settings = $settings;
			add_action('wpcf7_before_send_mail', function ($cf7) use ($settings) {
				if (isset($settings->{$cf7->id}) and $settings->{$cf7->id}->ativo == 1) {

					$this->wwp_do_action($cf7, $settings->{$cf7->id});
				}
			},10, 2);
		}
	}


	public function wwp_do_action($form,$trigger) {
		
		$modelo = $trigger->modelo;
		$rData = array();

		//GET PHONE

		if (!isset($_POST[$trigger->campo_telefone])) {
			return;
		}
		$target_phone = sanitize_text_field ($_POST[$trigger->campo_telefone]);
		$target_phone = $this->phone_validation($target_phone,$this->whatsapp->getCode());
		if ($target_phone === FALSE) {
			return false;
		}

		//REPLACE MODELO
		$modelo  = $this->wwp_replace_modelo($form->id, $modelo);



		//SEND MESSAGE
		$res = $this->whatsapp->sendMessage($target_phone,$modelo);

		if ($res) {
			$this->log->lwrite('Enviado para '.$target_phone);
		} else {
			$erro = $this->whatsapp->lastResp;
			$this->log->lwrite('Erro ao enviar para '.$target_phone.'. Erro: '.$erro[1]);
		}
	}

	public function wwp_replace_modelo($form_id,$modelo) {

		//BASICS 
		$contactForm = WPCF7_ContactForm::get_instance( $form_id );


		$form_fields = $contactForm->scan_form_tags();
		$rData = array();
		foreach ($form_fields as $campo) {
			$rData[$campo->name] = isset($_POST[$campo->name]) ? sanitize_text_field($_POST[$campo->name]) : '';
		}
				
		//Replace Model
		foreach ($rData as $key => $value) {
    		$modelo = str_replace('{'.$key.'}', $value, $modelo);
		}

		return $modelo;
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