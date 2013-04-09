<?php

	class WsPackAppcelerator extends WsPackPushNotificationService {
		
		const SERVICE_NAME = "appcelerator";
		
		protected $LOGIN_URL = "https://api.cloud.appcelerator.com/v1/users/login.json";
		protected $NOTIFY_URL = "https://api.cloud.appcelerator.com/v1/push_notification/notify.json";
		
		private $settings;
		private $login_cookie;
		private $app_key = "nGIQK9pwWuq498vIBozIL7qEAmpCVXkT";
		
		public function __construct($settings) {
			
			if(!empty($settings) && is_array($settings)) {
				$this->settings = $settings;
			}
		}
		
		public function sendMessage($text = "", $channel = "", $to_ids = array()) {
			$result = false;
			
			if(!empty($text) && !empty($channel) && !empty($to_ids)) {
				
				if(!is_array($to_ids)) {
					$to_ids = array($to_ids);
				}
				
				if ($this->login()) {
					$ch = curl_init($this->NOTIFY_URL . "?key=" . $this->getSetting("app_key"));
					
					$site = elgg_get_site_entity();
					
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_COOKIEJAR, $this->login_cookie);
					curl_setopt($ch, CURLOPT_COOKIEFILE, $this->login_cookie);
					curl_setopt($ch, CURLOPT_POSTFIELDS, array(
						"channel" => $channel,
						"to_ids" => implode(",", $to_ids),
						"payload" => json_encode(array(
							"title" => $site->name,
							"alert" => $text,
							"badge" => 0,
							"sound" => "default"
						))
					));
					
					$api_result = curl_exec($ch);
					
					if($this->validateApiResult($api_result)) {
						$result = true;
					}
					
					// log
					$this->log(array("notify", $result, count($to_ids)));
				}
			}
			
			return $result;
		}
		
		private function login() {
			$result = false;
			
			if(!isset($this->login_cookie)) {
				$this->login_cookie = tempnam(sys_get_temp_dir(), "Appcelerator");
				
				$ch = curl_init($this->LOGIN_URL . "?key=" . $this->getSetting("app_key"));
				
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_COOKIEJAR, $this->login_cookie);
				curl_setopt($ch, CURLOPT_COOKIEFILE, $this->login_cookie);
				curl_setopt($ch, CURLOPT_POSTFIELDS, array(
					"login" => $this->getSetting("username"),
					"password" => $this->getSetting("password")
				));
				
				$api_result = curl_exec($ch);
				
				if ($this->validateApiResult($api_result)) {
					$result = true;
				} else {
					$this->login_cookie = false;
				}
			} elseif ($this->login_cookie !== false) {
				$result = true;
			}
			
			return $result;
		}
		
		private function getSetting($setting) {
			$result = false;
			
			if(isset($this->settings) && is_array($this->settings)) {
				if(array_key_exists($setting, $this->settings)) {
					$result = $this->settings[$setting];
				}
			}
			
			return $result;
		}
		
		private function validateApiResult($api_result) {
			$result = false;
			
			if (!empty($api_result)) {
				if (is_string($api_result)) {
					$api_result = json_decode($api_result, true);
				}
				
				if (isset($api_result["meta"]) && isset($api_result["meta"]["status"])) {
					if($api_result["meta"]["status"] == "ok") {
						$result = true;
					}
				}
			}
			
			return $result;
		}
		
		protected function log(array $contents) {
			$dataroot = elgg_get_config("dataroot");
			$site = elgg_get_site_entity();
			
			// make sure the path is available
			$path = $dataroot . "ws_pack_logging/" . $site->getGUID() . "/" . self::SERVICE_NAME . "/";
			if (!is_dir($path)) {
				mkdir($path, 0644, true);
			}
			
			// make a file heading
			$filename = $path . date("Ymd") . ".log";
			if (!file_exists($filename)) {
				file_put_contents($filename, implode(";", array("date", "timestamp", "method", "result", "extras")));
			}
			
			// some default logging columns
			$defaults = array(
				date(DATE_RSS),
				time()
			);
			
			// merge defaults with actual data
			$contents = array_merge($defaults, $contents);
			
			// write logging
			return file_put_contents($filename, implode(";", $contents));
		}
	}