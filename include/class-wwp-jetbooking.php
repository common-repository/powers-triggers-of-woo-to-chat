<?php
class Woowhatspowers_Jetbooking {
	private $acoes = array();

	function __construct() {
		$this->wpp_load_acoes();
	}

	function wpp_load_acoes(){
		$acoes = get_option( 'wwp_jetbooking_acoes', '' );
		if (!empty($acoes)) {
			$this->acoes = $acoes;
		}
	}

	function wpp_getAcoes(){
		return $this->acoes;
	}

	function wpp_saveAcoes($dados = array()){
		if (empty($dados)) { return false;	}
		if (!is_array($dados) ) { $dados[] = $dados; }
		$salvar = array();
		if (isset($dados['newaction']) and !empty($dados['newslug']) and !empty($dados['newtemplate']) ) {
			$salvar[] = array('slug' => $dados['newslug'], 'template' => $dados['newtemplate']);
		}
		global $wpdb;

		if (!empty(($dados['slug']))) {
			if (!is_array($dados['slug'])) {
				$dados['slug'] = array($dados['slug']);
			}

			foreach ($dados['slug'] as $key => $value) {
				if (isset($dados['slug'][$key]) and isset($dados['template'][$key])) {		
					$salvar[] = array('slug' => $dados['slug'][$key], 'template' => $dados['template'][$key]);
				}
			}
		}
		$up = update_option('wwp_jetbooking_acoes', $salvar,FALSE);
		if (!$up) {
			$up = add_option('wwp_jetbooking_acoes', $salvar);
		}
		$this->wpp_load_acoes();
		return $up;
	}

	

}