<?php

	class APIApplicationUserSetting extends ElggObject {
		
		const SUBTYPE = "ws_pack_application_user_setting";
		
		// overrule / extend some parent functions
		protected function initializeAttributes() {
			parent::initializeAttributes();
				
			$this->attributes["subtype"] = self::SUBTYPE;
			$this->attributes["access_id"] = ACCESS_PRIVATE;
		}
		
		// own functions
		public function registerForPushNotifications($service_name, $settings) {
			$result = false;
			
			if(!empty($service_name) && !empty($settings)) {
				if(!is_array($settings)) {
					$settings = array($settings);
				}
				
				switch ($service_name) {
					case "appcelerator":
						if($this->getPushNotificationSettings($service_name)) {
							$this->unregisterFromPushNotifications($service_name);
						}
						
						$result = $this->annotate($service_name, json_encode($settings));
						break;
				}
			}
			
			return $result;
		}
		
		public function unregisterFromPushNotifications($service_name) {
			$result = false;
			
			if(!empty($service_name)) {
				
				switch ($service_name) {
					case "appcelerator":
						$result = $this->deleteAnnotations($service_name);
						break;
				}
			}
			
			return $result;
		}
		
		public function getPushNotificationSettings($service_name) {
			$result = false;
				
			if(!empty($service_name)) {
			
				switch ($service_name) {
					case "appcelerator":
						if ($settings = $this->getAnnotations($service_name, 1)) {
							$result = json_decode($settings[0]->value, true);
						}
						break;
				}
			}
				
			return $result;
		}
	}