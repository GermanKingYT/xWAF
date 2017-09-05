<?php
/**
 *  xWAF 1.0 - Free Web Application Firewall, Open-Source.
 *
 *  @author Alemalakra
 *  @version 1.0
 */

class xWAF {
	function __construct() {
		$this->IPHeader = "REMOTE_ADDR";
	}
	function vulnDetectedHTML($Method, $BadWord, $DisplayName, $TypeVuln) {
		echo '<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>xWAF</title></head><body><table width="100%"><tbody><tr><td colspan="2" style="font-family:Trebuchet MS;font-weight:bold;color:white" bgcolor="red" align="center">Access has been denied </td></tr></tbody></table>';
		echo '<br>';
		echo '<font face="Helvetica">';
		echo 'Our Web Application Firewall detected something unusual in its input variables, and it was blocked.';
		echo '</font>';
		echo '<font face="Helvetica"><br><b><p id="urlp">' . "Vulnerability type: " . $TypeVuln . "<br>Variable Name: " . htmlentities($DisplayName) . "<br>String Vulnerable: " . htmlspecialchars($BadWord) . '<br>Method: '.htmlentities($Method);

		echo '<br>IPv4: ' . $_SERVER[$this->IPHeader] . '<br>';

		echo '</b></font>';

		echo '<font face="Helvetica" size="2"><br><br>To report some bug or somenthing contact <a href="mailto:a1ema1akra@cock.li">firewall developer</a>.</font>';
		echo '<hr>';
		echo '</body></html>';
		die(); // Important... Never remove this.
	}
	function sqlCheck($Value, $Method, $DisplayName) {
		// For false alerts.
		$Replace = array("can't" => "cant",
						"don't" => "dont");
		foreach ($Replace as $key => $value_rep) {
			$Value = str_replace($key, $value_rep, $Value);
		}
		$BadWords = array(
							"'",
							'SELECT FROM',
							'SELECT * FROM',
							'ONION',
							'union',
							'UNION',
							'UDPATE users SET',
							'WHERE username',
							'drop',
							'table',
							'0x50',
							'mid((select',
							'union(((((((',
							'concat(0x',
							'0x3c62723e3c62723e3c62723e',
							'0x3c696d67207372633d22',
							'+#1q%0AuNiOn all#qa%0A#%0AsEleCt',
							'unhex(hex(Concat(',
							'Table_schema,0x3e,'
							);

		foreach ($BadWords as $BadWord) {
			if (strpos(strtolower($Value), strtolower($BadWord)) !== false) {
			    // String contains some Vuln.
				$this->vulnDetectedHTML($Method, $BadWord, $DisplayName, 'SQL INYECTION');

			}
		}
	}
	function xssCheck($Value, $Method, $DisplayName) {
		// For false alerts.
		$Replace = array("<3" => ":heart:");
		foreach ($Replace as $key => $value_rep) {
			$Value = str_replace($key, $value_rep, $Value);
		}
		$BadWords = array('<img',
						'img>',
						'<imagen',
						'document.cookie',
						'onerror()',
						'script>',
						'<script',
						'alert(',
						'String.fromCharCode(',
						'javascript:',
						'onmouseover="',
						'<BODY onload');

		foreach ($BadWords as $BadWord) {
			if (strpos(strtolower($Value), strtolower($BadWord)) !== false) {
			    // String contains some Vuln.

				$this->vulnDetectedHTML($Method, $BadWord, $DisplayName, 'XSS');

			}
		}
	}
	function checkGET() {
		foreach ($_GET as $key => $value) {
			$this->sqlCheck($value, "_GET", $key);
			$this->xssCheck($value, "_GET", $key);
		}
	}
	function checkPOST() {
		foreach ($_POST as $key => $value) {
			$this->sqlCheck($value, "_POST", $key);
			$this->xssCheck($value, "_POST", $key);
		}
	}
	function checkCOOKIE() {
		foreach ($_COOKIE as $key => $value) {
			$this->sqlCheck($value, "_COOKIE", $key);
			$this->xssCheck($value, "_COOKIE", $key);
		}
	}
	function getCSRF() {
		if (isset($_SESSION['token'])) {
			$token_age = time() - $_SESSION['token_time'];
			if ($token_age <= 300){    /* Less than five minutes has passed. */
				return $_SESSION['token'];
			} else {
				$token = md5(uniqid(rand(), TRUE));
				$_SESSION['token'] = $token;
				$_SESSION['token_time'] = time();
				return $_SESSION['token'];
			}
		} else {
			$token = md5(uniqid(rand(), TRUE));
			$_SESSION['token'] = $token;
			$_SESSION['token_time'] = time();
			return $_SESSION['token'];
		}
	}
	function verifyCSRF($Value) {
		if (isset($_SESSION['token'])) {
			$token_age = time() - $_SESSION['token_time'];
			if ($token_age <= 300){    /* Less than five minutes has passed. */
				if ($Value == $_SESSION['token']) {
					// Validated, Done!
					unset($_SESSION['token']);
					unset($_SESSION['token_time']);
					return true;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	function useCloudflare() {
		$this->IPHeader = "CF-Connecting-IP";
	}
	function useBlazingfast() {
		$this->IPHeader = "X-Real-IP";
	}
	function start() {
		session_start();
		$this->checkGET();
		$this->checkPOST();
		$this->checkCOOKIE();
	}

}
?>