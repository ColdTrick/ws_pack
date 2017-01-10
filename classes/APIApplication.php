<?php

/**
 * API Application object
 *
 * @package WsPack
 */
class APIApplication extends ElggObject {
	
	const SUBTYPE = 'ws_pack_application';
	const STATE_PENDING = -100;
	
	protected $api_user;
	
	/**
	 * overrule / extend some parent functions
	 *
	 * @return void
	 *
	 * @see ElggObject::initializeAttributes()
	 */
	protected function initializeAttributes() {
		parent::initializeAttributes();
		
		$site = elgg_get_site_entity();
	
		$this->attributes['subtype'] = self::SUBTYPE;
		$this->attributes['access_id'] = ACCESS_PUBLIC;
		$this->attributes['owner_guid'] = $site->getGUID();
		$this->attributes['container_guid'] = $site->getGUID();
	}
	
	/**
	 * Also deactivate the api_user when disabling the API application
	 *
	 * @param string  $reason    reason for disabling
	 * @param boolean $recursive set to true to disable recursively
	 *
	 * @return bool
	 *
	 * @see ElggEntity::disable()
	 */
	public function disable($reason = '', $recursive = true) {
		if (isset($this->api_user_id)) {
			ws_pack_deactivate_api_user_from_id($this->api_user_id);
	
			unset($this->api_user);
		}
			
		return parent::disable($reason, $recursive);
	}
	
	/**
	 * Also activate the api_user when enabling the API application
	 *
	 * @param bool $recursive Recursively enable all entities disabled with the entity?
	 *
	 * @return bool
	 *
	 * @see ElggEntity::enable()
	 */
	public function enable($recursive = true) {
		$result = parent::enable($recursive);
		
		if (isset($this->api_user_id)) {
			ws_pack_activate_api_user_from_id($this->api_user_id);
		}
		
		return $result;
	}
	
	/**
	 * Returns icon url for the application
	 *
	 * @param string $size icon size
	 *
	 * @return string
	 *
	 * @see ElggEntity::getIconURL()
	 */
	public function getIconURL($size = 'medium') {
		if (isset($this->icon_url)) {
			return $this->icon_url;
		} else {
			return parent::getIconURL($size);
		}
	}
	
	/**
	 * Also remove API user when deleting the object
	 *
	 * @param boolean $recursive if the delete should be recursive
	 *
	 * @return bool
	 *
	 * @see ElggEntity::delete()
	 */
	public function delete($recursive = true) {
		
		$keys = $this->getApiKeys();
		if (!empty($keys)) {
			remove_api_user($this->site_guid, $keys['api_key']);
		}
		
		return parent::delete($recursive);
	}
	
	/**
	 *
	 * {@inheritDoc}
	 * @see ElggObject::getDisplayName()
	 */
	public function getDisplayName() {
		return $this->getTitle();
	}
	
	/**
	 * Returns the title of the application
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}
	
	/**
	 * Returns the description of the application
	 *
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}
	
	/**
	 * Returns the status code
	 *
	 * @return int
	 */
	public function getStatusCode() {
		
		// is this entity enabled
		if (!$this->isEnabled()) {
			// this application has been disabled
			return ErrorResult::$RESULT_FAIL_APIKEY_DISABLED;
		}
		
		// does it have a connected API user
		if (!isset($this->api_user_id)) {
			// no API user yet
			return self::STATE_PENDING;
		}
		
		// is the API user active
		if ($this->getApiKeys()) {
			return SuccessResult::$RESULT_SUCCESS;
		}
		
		// API user is inactive
		return ErrorResult::$RESULT_FAIL_APIKEY_INACTIVE;
	}
	
	/**
	 * Activates the API application
	 *
	 * @return boolean
	 */
	public function activate() {
		$result = false;
		
		// make sure this entity is enabled
		if (!$this->isEnabled()) {
			if (!$this->enable()) {
				return false;
			}
		}
		
		// now create API keys
		if (!isset($this->api_user_id)) {
			if ($api_user = create_api_user($this->site_guid)) {
				$this->api_user_id = sanitise_int($api_user->id);
				
				$result = true;
			}
		} else {
			$result = ws_pack_activate_api_user_from_id($this->api_user_id);
		}
		
		return $result;
	}
	
	/**
	 * Deactivates the API application
	 *
	 * @return boolean
	 */
	public function deactivate() {
		if (isset($this->api_user_id)) {
			$result = ws_pack_deactivate_api_user_from_id($this->api_user_id);
		} else {
			$result = true;
		}
		
		return $result;
	}
	
	/**
	 * Returns the API keys
	 *
	 * @return array|boolean
	 */
	public function getApiKeys() {
		$result = false;
		
		if ($this->isEnabled() && isset($this->api_user_id)) {
			if (!isset($this->api_user)) {
				$this->api_user = ws_pack_get_api_user_from_id($this->api_user_id);
			}
			
			if ($this->api_user->active) {
				$result = array(
					'api_key' => $this->api_user->api_key,
					'secret' => $this->api_user->secret
				);
			}
		}
		
		return $result;
	}
	
	/**
	 * Registers a push notification service to the API application
	 *
	 * @param string $service_name name of the service
	 * @param array  $settings     additional settings to be save with the notification service
	 *
	 * @return bool
	 */
	public function registerPushNotificationService($service_name, $settings) {
		
		if (empty($service_name) || empty($settings)) {
			return false;
		}
		
		if (!is_array($settings)) {
			$settings = [$settings];
		}
		
		$handler = $this->getPushNotificationServiceHandler($service_name);
		if (empty($handler)) {
			// not supported
			return false;
		}
		
		if ($this->getPushNotificationService($service_name)) {
			// already registered
			return true;
		}
		
		$value = [$service_name => $settings];
		
		return (bool) $this->annotate('push_notification_service', json_encode($value), ACCESS_PUBLIC);
	}
	
	/**
	 * Check if a push notification service is registered
	 *
	 * @param string $service_name the name of the push notification service
	 *
	 * @return bool
	 */
	public function isRegisteredPushNotificationService($service_name) {
		
		$services = $this->getPushNotificationServices();
		if (empty($services)) {
			return false;
		}
		
		return isset($services[$service_name]);
	}
	
	/**
	 * Returns a notification service settings
	 *
	 * @param string $service_name service name
	 *
	 * @return false|array
	 */
	public function getPushNotificationService($service_name) {
		
		if (empty($service_name)) {
			return false;
		}
		
		$services = $this->getPushNotificationServices();
		if (empty($services)) {
			return false;
		}
		
		return elgg_extract($service_name, $services, false);
	}
	
	/**
	 * Returns all push notifcation services
	 *
	 * @param bool $get_annotations set to true to return the annotations instead of an array with settings
	 *
	 * @return false|array
	 */
	public function getPushNotificationServices($get_annotations = false) {
		
		$services = $this->getAnnotations([
			'annotation_name' => 'push_notification_service',
			'limit' => false,
		]);
		if (empty($services)) {
			return false;
		}
		
		if (!empty($get_annotations)) {
			return $services;
		}
		
		$tmp_result = [];
		
		foreach ($services as $service) {
			$value = $service->value;
			if (empty($value)) {
				continue;
			}
			
			$value = json_decode($value, true);
			if (empty($value)) {
				continue;
			}
			
			foreach ($value as $service_name => $settings) {
				$tmp_result[$service_name] = $settings;
			}
		}
		
		if (!empty($tmp_result)) {
			return $tmp_result;
		}
		
		return false;
	}
	
	/**
	 * Unregister a push notification service
	 *
	 * @param string $service_name name of the service
	 *
	 * @return bool
	 */
	public function unregisterPushNotificationService($service_name) {
		
		if (empty($service_name)) {
			return false;
		}
		
		$services = $this->getPushNotificationServices(true);
		if (empty($services)) {
			// nothing to remove
			return true;
		}
		
		foreach ($services as $service) {
			$value = $service->value;
			if (empty($value)) {
				continue;
			}
			
			$value = json_decode($value, true);
			if (empty($value)) {
				continue;
			}
			
			if (isset($value[$service_name])) {
				return $service->delete();
			}
		}
		
		return false;
	}
	
	/**
	 * Send a push notification
	 *
	 * @param string $message              text of the message
	 * @param array  $potential_user_guids potential user guids
	 *
	 * @return void
	 */
	public function sendPushNotification($message, $potential_user_guids) {
		
		if (empty($message) || empty($potential_user_guids)) {
			return;
		}
		
		if (!is_array($potential_user_guids)) {
			$potential_user_guids = [$potential_user_guids];
		}
		
		$push_services = $this->getPushNotificationServices();
		if (empty($push_services)) {
			return;
		}
		
		foreach ($push_services as $service_name => $settings) {
			
			$classname = $this->getPushNotificationServiceHandler($service_name);
			if (empty($classname) || !class_exists($classname)) {
				continue;
			}
			
			$notification_service = new $classname($settings);
			
			$notify_options = [
				'type' => 'object',
				'subtype' => APIApplicationUserSetting::SUBTYPE,
				'limit' => false,
				'owner_guids' => $potential_user_guids,
				'container_guid' => $this->getGUID(),
				'annotation_name' => $service_name,
			];
			
			$annotations = elgg_get_annotations($notify_options);
			if (empty($annotations)) {
				continue;
			}
			
			switch ($service_name) {
				case 'ionic_cloud':
					
					foreach ($annotations as $service_setting) {
						if (empty($service_setting->value)) {
							continue;
						}
						
						$user_setting = @json_decode($service_setting->value, true);
						if (!is_array($user_setting)) {
							continue;
						}
						
						$device_token = elgg_extract('device_token', $user_setting);
						if (empty($device_token)) {
							continue;
						}
						
						$notification_service->setSetting('device_token', $device_token);
						$notification_service->sendMessage($message);
					}
					
					break;
			}
		}
	}
	
	/**
	 * Get all available push notification services and their class handlers
	 *
	 * @return []
	 */
	protected function getPushNotificationServiceHandlers() {
		
		$result = [
			'ionic_cloud' => 'WsPackIonicCloud',
		];
		
		return elgg_trigger_plugin_hook('push_notification_services', 'ws_pack', [], $result);
	}
	
	/**
	 * Get the class handler for a push notification service
	 *
	 * @param string $service_name the name of the push service
	 *
	 * @return false|string
	 */
	protected function getPushNotificationServiceHandler($service_name) {
		
		if (empty($service_name)) {
			return false;
		}
		
		$handlers = $this->getPushNotificationServiceHandlers();
		return elgg_extract($service_name, $handlers, false);
	}
}
