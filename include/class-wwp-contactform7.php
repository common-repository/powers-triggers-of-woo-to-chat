<?php
class Woowhatspowers_Contactform7 {
	public $triggers;

	function __construct() {
		$this->wpp_load_triggers();
	}

	function wpp_load_triggers(){
		$this->triggers = array();
		$posts = get_posts(array(
	        'post_type'     => 'wpcf7_contact_form',
	        'numberposts'   => -1
	    ));
		foreach ( $posts as $form ) {
			
			$ContactForm = WPCF7_ContactForm::get_instance( $form->ID );
			$form_fields = $ContactForm->scan_form_tags();
			$this->triggers[$form->ID] = 
				array(
					'ativo' => '',
					'titulo' => $form->post_title,
					'campos' => $form_fields,
					'campo_telefone' => '',
					'descricao' => '',
					'modelo' => '',
				);
		}

		$settings = get_option( 'wwp_contactform7_triggers', '' );
		if (!empty($settings)) {
			$settings = json_decode($settings);
			foreach ($this->triggers as $tKey => $tValue) {
				$this->triggers[$tKey]['ativo'] = isset($settings->$tKey->ativo) ? $settings->$tKey->ativo : '';
				$this->triggers[$tKey]['campo_telefone'] = isset($settings->$tKey->campo_telefone) ? $settings->$tKey->campo_telefone : '';
				$this->triggers[$tKey]['modelo'] = isset($settings->$tKey->modelo) ? $settings->$tKey->modelo : '';	
			}	
		}	
	}

	function wwp_save_triggers ($dados){
		if (!empty($dados)) {
			$saveTriggers = array();
			foreach ($this->triggers as $tKey => $tValue) {
				$saveTriggers[$tKey]['ativo'] = isset($dados[$tKey.'_ativo']) ? $dados[$tKey.'_ativo'] : '';
				$saveTriggers[$tKey]['campo_telefone'] = isset($dados[$tKey.'_campo_telefone']) ? $dados[$tKey.'_campo_telefone'] : '';
				$saveTriggers[$tKey]['modelo'] = isset($dados[$tKey.'_modelo']) ? $dados[$tKey.'_modelo'] : '';
			}
			$saveTriggers = json_encode($saveTriggers);
			$up = update_option('wwp_contactform7_triggers', $saveTriggers,FALSE);
			if (!$up) {
				$up = add_option('wwp_contactform7_triggers', $saveTriggers);
			}
		}
	}
}