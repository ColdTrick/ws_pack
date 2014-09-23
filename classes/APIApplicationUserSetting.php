<?php
/**
 * API Application user settings object
 *
 * @package WsPack
 */
class APIApplicationUserSetting extends ElggObject {
	
	const SUBTYPE = "ws_pack_application_user_setting";
	
	/**
	 * overrule / extend some parent functions
	 * 
	 * @return void
	 */ 
	protected function initializeAttributes() {
		parent::initializeAttributes();
			
		$this->attributes["subtype"] = self::SUBTYPE;
		$this->attributes["access_id"] = ACCESS_PRIVATE;
	}
	
	/**
	 * Register for push notification service
	 * 
	 * @param string $service_name name of the service
	 * @param array  $settings     user settings related to the service
	 * 
	 * @return boolean
	 */
	public function registerForPushNotifications($service_name, $settings) {
		$result = false;
		
		if (!empty($service_name) && !empty($settings)) {
			if (!is_array($settings)) {
				$settings = array($settings);
			}
			
			switch ($service_name) {
				case "appcelerator":
					if ($this->getPushNotificationSettings($service_name)) {
						$this->unregisterFromPushNotifications($service_name);
					}
					
					$result = $this->annotate($service_name, json_encode($settings));
					break;
			}
		}
		
		return $result;
	}
	
	/**
	 * Unregister from a push notification service
	 * 
	 * @param string $service_name name of the service
	 * 
	 * @return boolean
	 */
	public function unregisterFromPushNotifications($service_name) {
		$result = false;
		
		if (!empty($service_name)) {
			switch ($service_name) {
				case "appcelerator":
					$result = $this->deleteAnnotations($service_name);
					break;
			}
		}
		
		return $result;
	}
	
	/**
	 * Returns an array of settings for a given notifcation service
	 * 
	 * @param string $service_name name of the service
	 * 
	 * @return array|boolean
	 */
	public function getPushNotificationSettings($service_name) {
		$result = false;
			
		if (!empty($service_name)) {
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
	
	/**
	 * Resets the push notification counter for the services
	 * 
	 * @return void
	 */
	public function resetPushNotificationCounter() {
		if ($appcelerator_settings = $this->getAnnotations("appcelerator", 1)) {
			$appcelerator_setting = $appcelerator_settings[0];
			
			if ($settings = json_decode($appcelerator_setting->value, true)) {
				$settings["count"] = 0;
				
				$appcelerator_setting->value = json_encode($settings);
				$appcelerator_setting->save();
			}
		}
	}
}
