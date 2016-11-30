<?php
/**
 * API Application user settings object
 *
 * @package WsPack
 */
class APIApplicationUserSetting extends ElggObject {
	
	const SUBTYPE = "ws_pack_application_user_setting";
	
	/**
	 *
	 * {@inheritDoc}
	 * @see ElggObject::initializeAttributes()
	 */
	protected function initializeAttributes() {
		parent::initializeAttributes();
			
		$this->attributes["subtype"] = self::SUBTYPE;
		$this->attributes["access_id"] = ACCESS_PRIVATE;
	}
	
	/**
	 *
	 * {@inheritDoc}
	 *
	 * @return \APIApplication
	 */
	public function getContainerEntity() {
		return parent::getContainerEntity();
	}
	
	/**
	 * Register for push notification service
	 *
	 * @param string $service_name name of the service
	 * @param array  $settings     user settings related to the service
	 *
	 * @return bool
	 */
	public function registerForPushNotifications($service_name, $settings) {
		
		if (empty($service_name) || empty($settings)) {
			return false;
		}
		
		if (!is_array($settings)) {
			$settings = [$settings];
		}
		
		if ($this->getPushNotificationSettings($service_name)) {
			// remove previous settings
			$this->unregisterFromPushNotifications($service_name);
		}
		
		return (bool) $this->annotate($service_name, json_encode($settings));
	}
	
	/**
	 * Unregister from a push notification service
	 *
	 * @param string $service_name name of the service
	 *
	 * @return bool
	 */
	public function unregisterFromPushNotifications($service_name) {
		
		if (empty($service_name)) {
			return false;
		}
		
		return $this->deleteAnnotations($service_name);
	}
	
	/**
	 * Returns an array of settings for a given notifcation service
	 *
	 * @param string $service_name      name of the service
	 * @param bool   $return_annotation return the annotation, not just the value
	 *
	 * @return false|array|\ElggAnnotation
	 */
	public function getPushNotificationSettings($service_name, $return_annotation = false) {
			
		if (!empty($service_name)) {
			return false;
		}
		
		$settings = $this->getAnnotations([
			'annotation_name' => $service_name,
			'limit' => 1,
		]);
		if (empty($settings)) {
			return false;
		}
		
		if ($return_annotation) {
			return $settings[0];
		}
		
		return @json_decode($settings[0]->value, true);
	}
	
	/**
	 * Resets the push notification counter for the services
	 *
	 * @return void
	 */
	public function resetPushNotificationCounter() {
		
		$api_application = $this->getContainerEntity();
		
		$services = $api_application->getPushNotificationServices();
		if (empty($services)) {
			return;
		}
		
		foreach ($services as $service_name => $service_settings) {
			$user_settings = $this->getPushNotificationSettings($service_name, true);
			if (!($user_settings instanceof ElggAnnotation)) {
				continue;
			}
			
			$settings = $user_settings->value;
			if (empty($settings)) {
				continue;
			}
			$settings = json_decode($settings, true);
			if (empty($settings)) {
				continue;
			}
			
			$settings['count'] = 0;
			$user_settings->value = json_encode($settings);
			$user_settings->save();
		}
	}
}
