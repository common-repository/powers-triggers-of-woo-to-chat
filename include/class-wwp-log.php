<?php
class Woowhatspowers_log {
	private $log_file, $fp;
	
	public function __construct(){
		$path = plugin_dir_path(__FILE__);
		$path = str_replace('wp-content\plugins\powers-triggers-of-woo-to-chat\include/', '', $path);
		$this->lfile($path  . 'wwp_log.txt');
		
		
 	}

	public function lfile($path) {
	    $this->log_file = $path;
	}
	
	public function lwrite($message) {
	    if (!is_resource($this->fp)) {
	        $this->lopen();
	    }
	    $time = @date('[d/M/Y:H:i:s]');
	    fwrite($this->fp, "$time $message" . PHP_EOL);
	}
	
	public function lclose() {
		if (is_resource($this->fp)) {
	        fclose($this->fp);
	    }
	}
	
	private function lopen() {
	    $lfile = $this->log_file;
	    $this->fp = fopen($lfile, 'a') or exit("Can't open $lfile!");
	}

	public function __destruct() {
       $this->lclose();
   	}
}