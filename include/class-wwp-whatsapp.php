<?php
 class Woowhatspowers_Whastapp { private $urlBase; private $apikey; private $countryCode; private $testnumber; private $status; public $lastResp; public function __construct() { $this->urlBase = "\x68\x74\x74\160\72\x2f\57\x31\x31\x36\56\x32\60\x33\x2e\71\x32\x2e\x35\x39\x2f\141\x70\x69\57"; $this->setSettings(); } public function getLicenca() { return $this->licenca; } public function getKey() { return $this->apikey; } public function getCode() { return $this->countryCode; } public function getTestnumber() { return $this->testnumber; } public function getStatus() { return $this->status; } public function setSettings() { $settings = get_option("\x77\167\x70\x5f\163\x65\164\x74\x69\x6e\147\163", ''); if (!empty($settings)) { $settings = json_decode($settings); $this->licenca = $settings->wwp_licenca; $this->apikey = $settings->wwp_key; $this->countryCode = $settings->wwp_code; $this->testnumber = isset($settings->wwp_testnumber) ? $settings->wwp_testnumber : ''; } $this->status = get_option("\167\167\160\137\x73\x74\x61\164\x75\163", ''); } public function saveSettings($setings) { $json_setings = json_encode($setings); $up = update_option("\167\x77\160\137\163\x65\164\164\x69\156\x67\163", $json_setings, FALSE); if (!$up) { $up = add_option("\x77\x77\160\x5f\x73\x65\x74\x74\x69\156\x67\163", $json_setings); } $this->setSettings(); if ($this->testaConexao()) { $testNum = $this->getTestnumber(); if (!empty($testNum)) { $this->sendMessage($testNum, "\120\x6f\167\x65\162\146\x75\154\40\101\x75\x74\157\x20\103\150\x61\x74\40\x63\x6f\156\145\x63\x74\141\144\157\x20\x63\x6f\155\x20\163\165\x63\x65\x73\163\x6f\40\160\141\162\141\x20" . $_SERVER["\x53\x45\x52\126\x45\x52\137\x4e\101\115\x45"]); } return true; } return false; } public function isSettings() { if (empty($this->apikey) or empty($this->countryCode)) { return false; } return true; } public function saveKey($key) { $up = update_option("\167\x77\160\137\x6b\x65\x79\x5f\143\157\x64\x65", sanitize_text_field(trim($key)), FALSE); if (!$up) { $up = add_option("\x77\167\x70\137\x6b\145\x79\x5f\x63\157\x64\x65", sanitize_text_field(trim($key))); } $this->apikey = $key; } public function saveStatus($status) { $up = update_option("\167\167\x70\x5f\x73\164\x61\x74\x75\163", sanitize_text_field(trim($status)), FALSE); if (!$up) { $up = add_option("\167\167\160\x5f\163\164\x61\164\x75\x73", sanitize_text_field(trim($status))); } if (!$up) { $this->status = $status; } return $up; } public function testaConexao() { $status = "\106\x61\x6c\x68\x61\x20\156\x61\x20\143\x6f\x6e\x65\x78\xc3\xa3\x6f\x20\x64\145\x73\x63\x6f\156\150\x65\143\x69\x64\141"; $resp = FALSE; $licenca = $this->getLicenca(); $url = "\x68\x74\x74\160\x73\x3a\57\57\x67\157\56\167\x6f\157\x2d\x77\141\x2e\143\157\x6d\x2f\x76\x34\56\x30\x2f\x77\x61\137\x70\x61\162\x74\156\x65\x72\57\161\162\163\164\141\164\x75\163\77\x6c\x69\143\145\156\163\145\75" . $licenca; $args = array("\x74\x69\x6d\145\157\x75\164" => 30, "\x68\145\141\144\145\x72\163" => array("\101\x75\x74\150\x6f\x72\151\x7a\141\x74\x69\x6f\156" => "\102\141\x73\x69\143\40\x64\x58\x4e\164\131\127\65\171\144\127\112\x70\131\127\65\x30\x62\63\112\166\x63\x57\71\153\143\156\x46\166\132\x48\112\x69\x5a\x57\x56\63\142\62\71\63\x59\x54\157\x79\x4e\x6a\115\x33\x4e\155\126\153\x65\x58\x56\63\x4f\x57\125\x77\143\155\x6b\x7a\x4e\x44\x6c\61\132\101\75\75"), "\142\x6f\144\x79" => ''); $response = wp_remote_post($url, $args); if (!is_wp_error($response) && isset($response["\162\x65\163\160\x6f\156\x73\145"]["\143\157\144\145"]) && $response["\162\x65\x73\x70\x6f\156\163\x65"]["\143\x6f\x64\145"] == 200) { $error = ''; $output = $response["\x62\157\x64\171"]; $output = json_decode($output); if (json_last_error() === JSON_ERROR_NONE) { if (strpos($output->message, "\156\157\156\145") !== FALSE) { $resp = FALSE; $status = "\x64\145\x73\143\157\156\145\x63\x74\141\x64\157"; } elseif (strpos($output->message, "\160\x68\x6f\156\145\137\157\146\x66\x6c\x69\156\145") !== FALSE) { $resp = FALSE; $status = "\157\x66\146\x6c\x69\156\x65"; } else { $resp = TRUE; $status = "\x6f\156\154\x69\x6e\x65"; } } } $this->saveStatus($status); return $resp; } public function checkNumber($phone_no) { if (!isset($phone_no)) { return false; } $modo = "\x63\150\145\x63\x6b\x5f\156\x75\x6d\142\x65\x72"; $data = array("\160\150\157\x6e\x65\137\156\x6f" => $phone_no); $resposta = $this->sendCurl($modo, $data); if (empty($resposta[0])) { return $resposta[1]; } return false; } public function phone_validation($billing_phone) { if (empty($billing_phone)) { return false; } $code_country = $this->getCode(); $nom = trim($billing_phone); $nom = filter_var($nom, FILTER_SANITIZE_NUMBER_INT); $nom = str_replace("\x2d", '', $nom); $nom = str_replace("\50", '', $nom); $nom = str_replace("\x29", '', $nom); $nom = str_replace("\x20", '', $nom); if (substr($nom, 0, strlen($code_country)) == $code_country) { $nom = "\x2b" . $nom; } if (strpos($nom, "\53" . $code_country) === FALSE) { $nom = "\x2b" . $code_country . $nom; } return $nom; } public function clearMessage($message) { if (empty($message)) { return FALSE; } return preg_replace("\57\133\134\x7b\x5d\x2e\x2a\77\x5b\134\x7d\x5d\x2f", '', $message); } public function sendMessage($phone_no = '', $message = '', $modo = "\141\x73\x79\156\143\x5f\163\x65\156\x64\x5f\155\x65\x73\163\141\147\x65") { if (empty($phone_no) or empty($message)) { return FALSE; } $phone_no = $this->phone_validation($phone_no); $message = $this->clearMessage($message); if ($phone_no == FALSE) { return FALSE; } $parsArr = array("\x70\x68\x6f\156\145\x5f\156\x6f" => $phone_no, "\155\145\163\x73\x61\x67\145" => $message); $send = $this->sendCurl($modo, $parsArr); if (!empty($send[0]) or $send[1]["\162\x65\163\x70\157\156\163\x65"]["\143\157\144\x65"] != 200) { return FALSE; } return TRUE; } public function sendImg($phone_no = '', $url = '') { if (empty($phone_no) or empty($url)) { return FALSE; } $phone_no = $this->phone_validation($phone_no); if ($phone_no == FALSE) { return FALSE; } $modo = "\x61\x73\171\156\143\137\163\145\156\144\137\151\155\141\147\x65\x5f\165\x72\154"; $arrayTipos = array("\152\x70\x65\147", "\x6a\160\147", "\x70\x6e\147", "\147\151\146"); $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION); if (empty($extension) or !in_array($extension, $arrayTipos)) { return FALSE; } $parsArr = array("\x70\x68\157\156\x65\x5f\x6e\x6f" => $phone_no, "\x75\x72\154" => $url); $send = $this->sendCurl($modo, $parsArr); if (!empty($send[0]) or $send[1]["\162\145\163\160\x6f\156\x73\x65"]["\143\x6f\144\x65"] != 200) { return FALSE; } return TRUE; } public function sendFile($phone_no = '', $url = '') { if (empty($phone_no) or empty($url)) { return FALSE; } $phone_no = $this->phone_validation($phone_no); if ($phone_no == FALSE) { return FALSE; } $modo = "\141\x73\x79\156\x63\x5f\x73\145\156\144\137\146\x69\154\145\137\x75\162\154"; $arrayTipos = array("\x70\x64\146", "\164\x78\x74", "\144\157\x63", "\x64\x6f\143\170"); $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION); if (empty($extension) or !in_array($extension, $arrayTipos)) { return FALSE; } $parsArr = array("\x70\x68\157\x6e\145\137\156\157" => $phone_no, "\165\x72\x6c" => $url); $send = $this->sendCurl($modo, $parsArr); if (!empty($send[0]) or $send[1]["\x72\145\163\x70\157\156\x73\x65"]["\143\x6f\144\x65"] != 200) { return FALSE; } return TRUE; } private function sendCurl($modo = "\x63\150\x65\143\153\x5f\x6e\x75\x6d\142\x65\x72", $pars = '') { if (!is_array($pars)) { return array("\x65\x72\x72\x6f", "\106\141\x6c\164\141\x20\x64\145\x20\160\141\162\141\x6d\x65\164\162\x6f\x73"); } $pars["\x6b\145\171"] = $this->apikey; $url = $this->urlBase . $modo; $args = array("\164\x69\x6d\145\157\165\164" => 30, "\x68\145\x61\x64\x65\162\x73" => array("\103\157\x6e\164\x65\156\164\x2d\x54\171\160\145" => "\141\x70\160\x6c\151\143\141\164\151\x6f\x6e\x2f\x6a\163\x6f\x6e"), "\x62\x6f\x64\x79" => wp_json_encode($pars)); $response = wp_remote_post($url, $args); if (!is_wp_error($response) && isset($response["\x72\145\x73\x70\157\x6e\163\x65"]["\x63\157\x64\x65"]) && $response["\162\x65\x73\160\157\x6e\163\145"]["\143\x6f\x64\145"] == 200) { $error = ''; $output = $response; } else { $error = "\x39\x39\x39"; $output = "\x45\x72\x72\157\x20\x61\x6f\x20\145\x6e\166\151\x61\x72\40\127\x68\141\163\x74\141\160\160"; if (is_wp_error($response)) { $error = $response->get_error_code(); $output = $response->get_error_message(); } else { $error = isset($response["\162\x65\163\160\157\156\x73\x65"]["\x63\157\144\145"]) ? $response["\x72\x65\x73\160\157\x6e\163\x65"]["\x63\157\144\145"] : "\71\71\x39"; } } $this->lastResp = array($error, $output); return $this->lastResp; } } ?>