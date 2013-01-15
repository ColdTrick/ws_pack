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
	}
	