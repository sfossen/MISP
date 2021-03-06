<?php

class ComplexTypeTool {
	public function checkComplexRouter($input, $type) {
		switch ($type) {
			case 'File':
				return $this->checkComplexFile($input);
				break;
			case 'CnC':
				return $this->checkComplexCnC($input);
				break;
			case 'FreeText':
				return $this->checkFreetext($input);
				break;
			default:
				return false;
		}	
	}
	
	// checks if the passed input matches a valid file description attribute's pattern (filename, md5, sha1, sha256, filename|md5, filename|sha1, filename|sha256)		
	public function checkComplexFile($input) {
		$original = $input;
		$type = '';
		$composite = false;
		if (strpos($input, '|')) {
			$composite = true;
			$result = explode('|', $input);
			if (count($result) != 2) $type = 'other';
			if (!preg_match("#^.+#", $result[0])) $type = 'other';
			$type = 'filename|';
			$input = $result[1];
		}
		if (strlen($input) == 32 && preg_match("#[0-9a-f]{32}$#", $input)) $type .= 'md5';
		if (strlen($input) == 40 && preg_match("#[0-9a-f]{40}$#", $input)) $type .= 'sha1';
		if (strlen($input) == 64 && preg_match("#[0-9a-f]{64}$#", $input)) $type .= 'sha256';
		if ($type == '' && !$composite && preg_match("#^.+#", $input)) $type = 'filename';
		if ($type == '') $type = 'other';
		return array('type' => $type, 'value' => $original);
	}
	
	public function checkComplexCnC($input) {
		$type = '';
		$toReturn = array();
		// check if it's an IP address
		if (filter_var($input, FILTER_VALIDATE_IP)) return array('type' => 'ip-dst', 'value' => $input);
		if (preg_match("#^[A-Z0-9.-]+\.[A-Z]{2,4}$#i", $input)) {
			$result = explode('.', $input);
			if (count($result) > 2) {
				$toReturn['multi'][] = array('type' => 'hostname', 'value' => $input);
				 $pos = strpos($input, '.');
				 $toReturn['multi'][] = array('type' => 'domain', 'value' => substr($input, (1 + $pos)));
				 return $toReturn;
			}
			return array('type' => 'domain', 'value' => $input);
		}
		
		if (!preg_match("#\n#", $input)) return array('type' => 'url', 'value' => $input);
		return array('type' => 'other', 'value' => $input);
	}
	
	public function checkFreeText($input) {
		$iocArray = preg_split("/\r\n|\n|\r|\s|\s+|,|;/", $input);
		$resultArray = array();
		foreach ($iocArray as $ioc) {
			$ioc = trim($ioc);
			$ioc = trim($ioc, ',');
			$ioc = preg_replace('/\p{C}+/u', '', $ioc);
			if (empty($ioc)) continue;
			$typeArray = $this->__resolveType($ioc);
			if ($typeArray === false) continue;
			$temp = $typeArray;
			if (!isset($temp['value'])) $temp['value'] = $ioc;
			$resultArray[] = $temp;
		}
		return $resultArray;
	}
	
	private function __resolveType($input) {
		$result = array();
		$input = trim($input);
		if (strpos($input, '|')) {
			$compositeParts = explode('|', $input);
			if (count($compositeParts) == 2) {
				if ($this->__resolveFilename($compositeParts[0])) {
					if (strlen($compositeParts[1]) == 32 && preg_match("#[0-9a-f]{32}$#i", $compositeParts[1])) return array('types' => array('filename|md5', 'filename|imphash'), 'to_ids' => true, 'default_type' => 'filename|md5');
					if (strlen($compositeParts[1]) == 40 && preg_match("#[0-9a-f]{40}$#i", $compositeParts[1])) return array('types' => array('filename|sha1', 'filename|pehash'), 'to_ids' => true, 'default_type' => 'filename|sha1');
					if (strlen($compositeParts[1]) == 56 && preg_match("#[0-9a-f]{56}$#i", $compositeParts[1])) return array('types' => array('filename|sha512/224', 'filename|sha224'), 'to_ids' => true, 'default_type' => 'filename|sha224');
					if (strlen($compositeParts[1]) == 64 && preg_match("#[0-9a-f]{64}$#i", $compositeParts[1])) return array('types' => array('filename|sha256', 'filename|sha512/256', 'filename|authentihash'), 'to_ids' => true, 'default_type' => 'filename|sha256');
					if (strlen($compositeParts[1]) == 96 && preg_match("#[0-9a-f]{96}$#i", $compositeParts[1])) return array('types' => array('filename|sha384'), 'to_ids' => true, 'default_type' => 'filename|sha384');
					if (strlen($compositeParts[1]) == 128 && preg_match("#[0-9a-f]{128}$#i", $compositeParts[1])) return array('types' => array('filename|sha512', 'filename|sha'), 'to_ids' => true, 'default_type' => 'filename|sha512');
					if (preg_match('#^[0-9]+:.+:.+$#', $compositeParts[1])) return array('types' => array('ssdeep'), 'to_ids' => true, 'default_type' => 'filename|ssdeep');
				}
			}
		}
		
		// check for hashes
		if (strlen($input) == 32 && preg_match("#[0-9a-f]{32}$#i", $input)) return array('types' => array('md5', 'imhash'), 'to_ids' => true, 'default_type' => 'md5');
		if (strlen($input) == 40 && preg_match("#[0-9a-f]{40}$#i", $input)) return array('types' => array('sha1', 'pehash'), 'to_ids' => true, 'default_type' => 'sha1');
		if (strlen($input) == 56 && preg_match("#[0-9a-f]{56}$#i", $input)) return array('types' => array('sha224', 'sha512/224'), 'to_ids' => true, 'default_type' => 'sha224');
		if (strlen($input) == 64 && preg_match("#[0-9a-f]{64}$#i", $input)) return array('types' => array('sha256', 'sha512/256', 'authentihash'), 'to_ids' => true, 'default_type' => 'sha256');
		if (strlen($input) == 96 && preg_match("#[0-9a-f]{96}$#i", $input)) return array('types' => array('sha384'), 'to_ids' => true, 'default_type' => 'sha384');
		if (strlen($input) == 128 && preg_match("#[0-9a-f]{128}$#i", $input)) return array('types' => array('sha512'), 'to_ids' => true, 'default_type' => 'sha512');
		if (preg_match('#^[0-9]+:.+:.+$#', $input)) return array('types' => array('ssdeep'), 'to_ids' => true, 'default_type' => 'ssdeep');
		
		$inputRefanged = preg_replace('/^hxxp/i', 'http', $input);
		$inputRefanged = preg_replace('/\[\.\]/', '.' , $inputRefanged);
		$inputRefanged = rtrim($inputRefanged, ".");
		// note down and remove the port if it's a url / domain name / hostname / ip
		// input2 from here on is the variable containing the original input with the port removed. It is only used by url / domain name / hostname / ip
		$comment = false;
		if (preg_match('/(:[0-9]{2,5})$/', $inputRefanged, $port)) {
			$comment = 'On port ' . substr($port[0], 1);
			$inputRefangedNoPort = str_replace($port[0], '', $inputRefanged);
		} else $inputRefangedNoPort = $inputRefanged;		
		// check for IP
		if (filter_var($inputRefangedNoPort, FILTER_VALIDATE_IP)) return array('types' => array('ip-dst', 'ip-src', 'ip-src/ip-dst'), 'to_ids' => true, 'default_type' => 'ip-dst', 'comment' => $comment, 'value' => $inputRefangedNoPort);
		if (strpos($inputRefangedNoPort, '/')) {
			$temp = explode('/', $inputRefangedNoPort);
			if (count($temp == 2)) {
				if (filter_var($temp[0], FILTER_VALIDATE_IP) && is_numeric($temp[1])) return array('types' => array('ip-dst', 'ip-src', 'ip-src/ip-dst'), 'to_ids' => true, 'default_type' => 'ip-dst', 'comment' => $comment, 'value' => $inputRefangedNoPort);
			}
		}
		
		
		// check for domain name, hostname, filename
		if (strpos($inputRefanged, '.') !== false) {
			$temp = explode('.', $inputRefanged);
			//if (filter_var($input, FILTER_VALIDATE_URL)) {
			if (preg_match('/^([-\pL\pN]+\.)+([a-z][a-z]|biz|cat|com|edu|gov|int|mil|net|org|pro|tel|aero|arpa|asia|coop|info|jobs|mobi|name|museum|travel)(:[0-9]{2,5})?$/iu', $inputRefanged)) {
				if (count($temp) > 2) {
					return array('types' => array('hostname', 'domain', 'url'), 'to_ids' => true, 'default_type' => 'hostname', 'comment' => $comment, 'value' => $inputRefangedNoPort);
				} else {
					return array('types' => array('domain'), 'to_ids' => true, 'default_type' => 'domain', 'comment' => $comment, 'value' => $inputRefangedNoPort);
				}
			} else {
				// check if it is a URL
				// Adding http:// infront of the input in case it was left off. github.com/MISP/MISP should still be counted as a valid link
				if (count($temp) > 1 && (filter_var($inputRefangedNoPort, FILTER_VALIDATE_URL) || filter_var('http://' . $inputRefangedNoPort, FILTER_VALIDATE_URL))) {
					if (preg_match('/^https:\/\/www.virustotal.com\//i', $inputRefangedNoPort)) return array('types' => array('link'), 'to_ids' => true, 'default_type' => 'link', 'comment' => $comment, 'value' => $inputRefangedNoPort);
					return array('types' => array('url'), 'to_ids' => true, 'default_type' => 'url', 'comment' => $comment, 'value' => $inputRefangedNoPort);
				}
				if ($this->__resolveFilename($input)) return array('types' => array('filename'), 'to_ids' => true, 'default_type' => 'filename');
			}
		}
		
		if (strpos($input, '\\') !== false) {
			$temp = explode('\\', $input);
			if (strpos($temp[count($temp)-1], '.')) {
				if ($this->__resolveFilename($temp[count($temp)-1])) return array('types' => array('filename'), 'category' => 'Payload installation', 'to_ids' => false, 'default_type' => 'filename');
			} else {
				return array('types' => array('regkey'), 'to_ids' => false, 'default_type' => 'regkey');
			}
		}
		
		if (strpos($input, '@') !== false) {
			if (filter_var($input, FILTER_VALIDATE_EMAIL)) return array('types' => array('email-src', 'email-dst'), 'to_ids' => true, 'default_type' => 'email-src');
		}
		
		// check for CVE
		if (preg_match("#^cve-[0-9]{4}-[0-9]{4,9}$#i", $input)) return array('types' => array('vulnerability'), 'category' => 'External analysis', 'to_ids' => false, 'default_type' => 'vulnerability');
		
		return false;
	}
	
	private function __resolveFilename($input) {
		if (
			strpos($input, '.') != 0 &&
			strpos($input, '..') == 0 &&
			strpos($input, '.') != (strlen($input)-1) &&
			preg_match('/(.*)\.[^(\|\<\>\^\=\?\/\[\]\"\;\*)]*$/', $input) &&
			!preg_match('/[?:<>|\\*:\/@]/', $input)
		) return true;
		return false;
	}
}