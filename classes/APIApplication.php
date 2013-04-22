<?php

	class APIApplication extends ElggObject {
		
		const SUBTYPE = "ws_pack_application";
		const STATE_PENDING = -100;
		
		protected $api_user;
		
		// overrule / extend some parent functions
		protected function initializeAttributes() {
			parent::initializeAttributes();
			
			$site = elgg_get_site_entity();
		
			$this->attributes["subtype"] = self::SUBTYPE;
			$this->attributes["access_id"] = ACCESS_PUBLIC;
			$this->attributes["owner_guid"] = $site->getGUID();
			$this->attributes["container_guid"] = $site->getGUID();
		}
		
		function disable($reason = "", $recursive = true) {
			if(isset($this->api_user_id)){
				ws_pack_deactivate_api_user_from_id($this->api_user_id);
		
				unset($this->api_user);
			}
				
			return parent::disable($reason, $recursive);
		}
		
		function enable() {
			$result = parent::enable();
				
			if (isset($this->api_user_id)) {
				ws_pack_activate_api_user_from_id($this->api_user_id);
			}
				
			return $result;
		}
		
		function getIconURL($size = "medium") {
			if(isset($this->icon_url)){
				return $this->icon_url;
			} else {
				return parent::getIconURL($size);
			}
		}
		
		function delete($recursive = true) {
			
			if($keys = $this->getApiKeys()) {
				remove_api_user($this->site_guid, $keys["api_key"]);
			}
			
			return parent::delete($recursive);
		}
		
		// new functions
		function getTitle() {
			return $this->title;
		}
		
		function getDescription() {
			return $this->description;
		}
		
		function getStatusCode() {
			$result = false;
			
			// is this entity enabled
			if ($this->isEnabled()) {
				// does it have a connected API user
				if (isset($this->api_user_id)) {
					// is the API user active
					if ($this->getApiKeys()) {
						$result = SuccessResult::$RESULT_SUCCESS;
					} else {
						// API user is inactive
						$result = ErrorResult::$RESULT_FAIL_APIKEY_INACTIVE;
					}
				} else {
					// no API user yet
					$result = self::STATE_PENDING;
				}
			} else {
				// this application has been disabled
				$result = ErrorResult::$RESULT_FAIL_APIKEY_DISABLED;
			}
			
			return $result;
		}
		
		function activate() {
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
		
		function deactivate() {
			$result = false;
			
			if (isset($this->api_user_id)) {
				$result = ws_pack_deactivate_api_user_from_id($this->api_user_id);
			} else {
				$result = true;
			}
			
			return $result;
		}
		
		function getApiKeys() {
			$result = false;
			
			if ($this->isEnabled() && isset($this->api_user_id)) {
				if (!isset($this->api_user)) {
					$this->api_user = ws_pack_get_api_user_from_id($this->api_user_id);
				}
				
				if ($this->api_user->active) {
					$result = array(
						"api_key" => $this->api_user->api_key,
						"secret" => $this->api_user->secret
					);
				}
			}
			
			return $result;
		}
		
		function registerPushNotificationService($service_name, $settings) {
			$result = false;
			
			if (!empty($service_name) && !empty($settings)) {
				if (!is_array($settings)) {
					$settings = array($settings);
				}
				
				switch ($service_name) {
					case "appcelerator":
						if ($this->getPushNotificationService($service_name)) {
							// already registered
							$result = true;
						} else {
							$value = array($service_name => $settings);
							
							$result = $this->annotate("push_notification_service", json_encode($value), ACCESS_PUBLIC);
						}
						break;
				}
				
			}
			
			return $result;
		}
		
		function getPushNotificationService($service_name) {
			$result = false;
			
			if (!empty($service_name)) {
				if ($services = $this->getAnnotations("push_notification_service", false)) {
					foreach($services as $service) {
						if ($value = $service->value) {
							if ($value = json_decode($value, true)) {
								if (array_key_exists($service_name, $value)) {
									$result = $value[$service_name];
									break;
								}
							}
						}
					}
				}
			}
			
			return $result;
		}
		
		function getPushNotificationServices($get_annotations = false) {
			$result = false;
			
			if ($services = $this->getAnnotations("push_notification_service", false)) {
				
				if(empty($get_annotations)) {
					$tmp_result = array();
					
					foreach ($services as $service) {
						if ($value = $service->value) {
							if ($value = json_decode($value, true)) {
								foreach ($value as $service_name => $settings) {
									$tmp_result[$service_name] = $settings;
								}
							}
						}
					}
					
					if (!empty($tmp_result)) {
						$result = $tmp_result;
					}
				} else {
					$result = $services;
				}
			}
			
			return $result;
		}
		
		function unregisterPushNotificationService($service_name) {
			$result = false;
			
			if (!empty($service_name)) {
				if ($services = $this->getAnnotations("push_notification_service", false)) {
					foreach($services as $service) {
						if ($value = $service->value) {
							if ($value = json_decode($value, true)) {
								if (array_key_exists($service_name, $value)) {
									$result = $service->delete();
									break;
								}
							}
						}
					}
				}
			}
			
			return $result;
		}
		
		function sendPushNotification($message, $potential_user_guids) {
			
			if(!empty($message) && !empty($potential_user_guids)) {
				if(!is_array($potential_user_guids)) {
					$potential_user_guids = array($potential_user_guids);
				}
				
				if($push_services = $this->getPushNotificationServices()) {
					
					foreach($push_services as $service_name => $settings) {
						$classname = "WsPack" . ucfirst($service_name);
						
						if (class_exists($classname)) {
							$notify_options = array(
								"type" => "object",
								"subtype" => APIApplicationUserSetting::SUBTYPE,
								"limit" => false,
								"owner_guids" => $potential_user_guids,
								"container_guid" => $this->getGUID(),
								"annotation_name" => $service_name
							);
							
							if ($annotations = elgg_get_annotations($notify_options)) {
								
								switch ($service_name) {
									case "appcelerator":
										$channels = array();
										
										foreach($annotations as $annotation) {
											if ($data = json_decode($annotation->value, true)) {
												$channel = elgg_extract("channel", $data);
												$user_id = elgg_extract("user_id", $data);
												
												if(!empty($channel) && !empty($user_id)) {
													if (!array_key_exists($channel, $channels)) {
														$channels[$channel] = array();
													}
													
													$channels[$channel][] = $user_id;
												}
											}
										}
										
										if(!empty($channels)) {
											$push_service = new $classname($settings);
											
											foreach ($channels as $channel => $to_ids) {
												$push_service->sendMessage($message, $channel, $to_ids);
											}
										}
										
										break;
								}
							}
						}
					}
				}
			}
		}
	}
	