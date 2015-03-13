<?php
/**
 * Events for ws_pack
 */

global $ws_pack_current_api_application;

/**
 * Return application for a given application id
 * 
 * @param string $application_id id of the applications
 * 
 * @return ElggEntity|boolean
 */
function ws_pack_get_application_from_id($application_id) {
	$result = false;
	
	if (!empty($application_id)) {
		$hidden = access_get_show_hidden_status();
		access_show_hidden_entities(true);
		
		$options = array(
			"type" => "object",
			"subtype" => APIApplication::SUBTYPE,
			"limit" => 1,
			"metadata_name_value_pairs" => array(
				"application_id" => $application_id
			)
		);
		
		if ($entities = elgg_get_entities_from_metadata($options)) {
			$result = $entities[0];
		}
		
		access_show_hidden_entities($hidden);
	}
	
	return $result;
}

/**
 * Create a new application
 * 
 * @param string $application_id   id op the app
 * @param string $title            name of the app
 * @param string $description      description of the app
 * @param string $icon_url         url to an icon
 * @param array  $application_info array containing additional data about the application
 * 
 * @return APIApplication|boolean
 */
function ws_pack_create_application($application_id, $title, $description = "", $icon_url = "", $application_info = array()) {
	$result = false;
	
	if (!($application = ws_pack_get_application_from_id($application_id))) {
		// check if api registration is allowed
		if (elgg_get_plugin_setting("allow_registration", "ws_pack") == "yes") {
			if (!empty($application_id) && !empty($title)) {
				$application = new APIApplication();
				
				$application->title = $title;
				$application->application_id = $application_id;
				
				// set a description
				if (!empty($description)) {
					$application->description = $description;
				}
				
				if (!empty($icon_url)) {
					$application->icon_url = $icon_url;
				}
				
				if (!empty($application_info) && is_array($application_info)) {
					$application->extended_information = json_encode($application_info);
				}
				
				// make sure we can save the application
				$ia = elgg_set_ignore_access(true);
				
				if ($application->save()) {
					$result = $application;
				}
				
				// restore access
				elgg_set_ignore_access($ia);
			}
		} else {
			// registration is not allowed
			$result = -1;
		}
	} else {
		// already existed, shouldn't happen
		$result = $application;
	}
	
	return $result;
}

/**
 * Returns the application related to a given api user
 * 
 * @param string $api_user_id id of the api user
 * 
 * @return APIApplication|boolean
 */
function ws_pack_get_application_from_api_user_id($api_user_id) {
	$result = false;
	
	$api_user_id = sanitise_int($api_user_id, false);
	
	if (!empty($api_user_id)) {
		$options = array(
			"type" => "object",
			"subtype" => APIApplication::SUBTYPE,
			"limit" => 1,
			"metadata_name_value_pairs" => array(
				"name" => "api_user_id",
				"value" => $api_user_id
			)
		);
		
		if ($entities = elgg_get_entities_from_metadata($options)) {
			$result = $entities[0];
		}
	}
	
	return $result;
}

/**
 * Returns the API user from a give api userid
 * 
 * @param string $api_user_id id of the api user
 * 
 * @return stdClass|boolean
 */
function ws_pack_get_api_user_from_id($api_user_id) {
	$result = false;
	
	$api_user_id = sanitise_int($api_user_id, false);
	
	if (!empty($api_user_id)) {
		$query = "SELECT *";
		$query .= " FROM " . elgg_get_config("dbprefix") . "api_users";
		$query .= " WHERE id = " . $api_user_id;
		
		if ($row = get_data_row($query)) {
			$result = $row;
		}
	}
	
	return $result;
}

/**
 * Deactivate an api user
 * 
 * @param string $api_user_id api user id
 * 
 * @return boolean
 */
function ws_pack_deactivate_api_user_from_id($api_user_id) {
	$result = false;
	
	$api_user_id = sanitise_int($api_user_id, false);
	
	if (!empty($api_user_id)) {
		$query = "UPDATE " . elgg_get_config("dbprefix") . "api_users";
		$query .= " SET active = 0";
		$query .= " WHERE id = " . $api_user_id;
			
		$result = update_data($query);
	}
	
	return $result;
}

/**
 * Activate an api user
 * 
 * @param string $api_user_id api user id
 * 
 * @return boolean
 */
function ws_pack_activate_api_user_from_id($api_user_id) {
	$result = false;
	
	$api_user_id = sanitise_int($api_user_id, false);
	
	if (!empty($api_user_id)) {
		$query = "UPDATE " . elgg_get_config("dbprefix") . "api_users";
		$query .= " SET active = 1";
		$query .= " WHERE id = " . $api_user_id;
			
		$result = update_data($query);
	}
	
	return $result;
}

/**
 * Export entities to be used in webservices and adds additional data
 * 
 * @param array $entities entities to export
 * 
 * @return boolean|array
 */
function ws_pack_export_entities($entities) {
	$result = false;
	
	if (!empty($entities) && is_array($entities)) {
		$result = array();
		
		foreach ($entities as $entity) {
			if ($entity instanceof ElggEntity) {
				$tmp_result = array();
				
				// get general export values
				$export_values = $entity->getExportableValues();
				
				foreach ($export_values as $field_name) {
					$tmp_result[$field_name] = $entity->$field_name;
				}
				
				// get icon urls
				if ($icon_sizes = elgg_get_config("icon_sizes")) {
					$icon_urls = array();
					
					foreach ($icon_sizes as $size => $info) {
						$icon_urls[$size] = $entity->getIconURL($size);
					}
					
					$tmp_result["icon_urls"] = $icon_urls;
				}
				
				// add url to the entity
				$tmp_result["url"] = ws_pack_create_sso_url($entity->getURL());
				
				// check for additional information
				switch ($entity->getType()) {
					case "group":
						// get the group profile fields
						if ($group_fields = elgg_get_config("group")) {
							$field_data = array();
							
							foreach ($group_fields as $metadata_name => $type) {
								$field_data[$metadata_name] = $entity->$metadata_name;
							}
							
							$tmp_result["profile_fields"] = $field_data;
						}
						break;
					case "user":
						//get the user profile fields
						if ($profile_fields = elgg_get_config("profile_fields")) {
							$field_data = array();
						
							foreach ($profile_fields as $metadata_name => $type) {
								$field_data[$metadata_name] = $entity->$metadata_name;
							}
						
							$tmp_result["profile_fields"] = $field_data;
						}
						break;
					case "site":
						// sites have different urls
						$tmp_result["url"] = $entity->url;
						break;
				}
				
				// return everything
				$result[] = $tmp_result;
			}
		}
	}
	
	return $result;
}

/**
 * Export a single entity and add extra data
 * 
 * @param ElggEntity $entity entity to export
 * 
 * @return boolean|ElggEntity
 */
function ws_pack_export_entity(ElggEntity $entity) {
	$result = false;
	
	// make sure we have an entity
	if (!empty($entity) && ($entity instanceof ElggEntity)) {
		$temp = array($entity);
		
		if ($export = ws_pack_export_entities($temp)) {
			$result = $export[0];
		}
	}
	
	return $result;
}

/**
 * Export river items
 * 
 * @param array $items river items to export
 * 
 * @return boolean|array
 */
function ws_pack_export_river_items($items) {
	elgg_load_library("simple_html_dom");
	
	$result = false;
	
	if (!empty($items) && is_array($items)) {
		$result = array();
		
		foreach ($items as $item) {
			if ($item instanceof ElggRiverItem) {
				$tmp_result = array();
				
				// default export values
				$export_values = array("id", "subject_guid", "object_guid", "annotation_id", "type", "subtype", "action_type", "posted");
				
				foreach ($export_values as $field_name) {
					$tmp_result[$field_name] = $item->$field_name;
				}
				
				// add object and subject entities
				$tmp_result["object"] = ws_pack_export_entity($item->getObjectEntity());
				$tmp_result["subject"] = ws_pack_export_entity($item->getSubjectEntity());
				
				// add some html views
				// set viewtype to default
				$viewtype = elgg_get_viewtype();
				elgg_set_viewtype("default");
				
				$tmp_result["html_view"] = elgg_view_river_item($item);
				
				// parse the html to get some usefull information
				if ($res = str_get_html($tmp_result["html_view"])) {
					// get the river summary
					if ($summary_element = $res->find("div.elgg-river-summary")) {
						$summary_element = $summary_element[0];
						
						$text = $summary_element->innertext();
						list($left, $right) = explode("<span class=\"elgg-river-timestamp\">", $text);
						
						$tmp_result["summary"] = trim(elgg_strip_tags($left));
					}
					
					// get the river message (optional)
					if ($message_element = $res->find("div.elgg-river-message")) {
						$message_element = $message_element[0];
						
						$tmp_result["message"] = trim(elgg_strip_tags($message_element->innertext()));
					}
					
					// get river attachments (optional)
					if ($attachment_element = $res->find("div.elgg-river-attachments")) {
						$attachment_element = $attachment_element[0];
						$tmp_result["attachments"] = array();
						
						// find images
						if ($images = $attachment_element->find("img")) {
							$image_urls = array();
						
							foreach ($images as $img) {
								$image_urls[] = $img->src;
							}
						
							$tmp_result["attachments"]["images"] = $image_urls;
						}
						
						// find links
						if ($links = $attachment_element->find("a")) {
							$link_urls = array();
						
							foreach ($links as $link) {
								$link_urls[] = $link->href;
							}
						
							$tmp_result["attachments"]["links"] = $link_urls;
						}
					}
				}
				
				// add friendly time
				$friendly_time = elgg_view_friendly_time($item->posted);
				$tmp_result["friendly_time"] = trim(elgg_strip_tags($friendly_time));
				
				// restore viewtype
				elgg_set_viewtype($viewtype);
				
				// add this item to the result set
				$result[] = $tmp_result;
			}
		}
	}
	
	return $result;
}

/**
 * Converts rows to guids
 * 
 * @param stdClass $row database row
 * 
 * @return int
 */
function ws_pack_row_to_guid($row) {
	return (int) $row->guid;
}

/**
 * Sets the global current API application
 * 
 * @param APIApplication $application application to set
 * 
 * @return boolean
 */
function ws_pack_set_current_api_application(APIApplication $application) {
	global $ws_pack_current_api_application;
	$result = false;
	
	if (!empty($application) && elgg_instanceof($application, "object", APIApplication::SUBTYPE)) {
		$ws_pack_current_api_application = $application;
		$result = true;
	}
	
	return $result;
}

/**
 * Returns the global API Application
 * 
 * @return APIApplication
 */
function ws_pack_get_current_api_application() {
	global $ws_pack_current_api_application;
	
	return $ws_pack_current_api_application;
}

/**
 * Returns application specific user settings
 * 
 * @param ElggUser       $user            user entity
 * @param APIApplication $api_application application entity
 * 
 * @return boolean|APIApplicationUserSetting
 */
function ws_pack_get_application_user_settings(ElggUser $user, APIApplication $api_application) {
	$result = false;
	
	if (!empty($user) && !empty($api_application)) {
		if (elgg_instanceof($user, "user") && elgg_instanceof($api_application, "object", APIApplication::SUBTYPE)) {
			$options = array(
				"type" => "object",
				"subtype" => APIApplicationUserSetting::SUBTYPE,
				"limit" => 1,
				"owner_guid" => $user->getGUID(),
				"container_guid" => $api_application->getGUID()
			);
			
			if ($entities = elgg_get_entities($options)) {
				$result = $entities[0];
			} else {
				$entity = new APIApplicationUserSetting();
				$entity->owner_guid = $user->getGUID();
				$entity->container_guid = $api_application->getGUID();
				
				if ($entity->save()) {
					$result = $entity;
				}
			}
		}
	}
	
	return $result;
}

/**
 * Modify a given URL to be a single sign on URL
 * 
 * @param string $url url to be transformed
 * 
 * @return string
 */
function ws_pack_create_sso_url($url) {
	$result = $url;
	
	if ($user = elgg_get_logged_in_user_entity()) {
		$timestamp = time();
		if ($secret = ws_pack_generate_sso_secret($user, $timestamp)) {
			$url_parts = parse_url($url);
			
			if (isset($url_parts["query"])) {
				$url_parts["query"] .= "&u=" . $user->getGUID() . "&s=" . $secret . "&t=" . $timestamp;
			} else {
				$url_parts["query"] = "u=" . $user->getGUID() . "&s=" . $secret . "&t=" . $timestamp;
			}
			
			if (is_callable("http_build_url")) {
				$result = http_build_url($url_parts);
			} else {
				$result = "";
				if (isset($url_parts["scheme"])) {
					$result .= $url_parts["scheme"] . "://";
				}
				if (isset($url_parts["host"])) {
					$result .= $url_parts["host"];
				}
				if (isset($url_parts["path"])) {
					$result .= $url_parts["path"];
				}
				if (isset($url_parts["query"])) {
					$result .= "?" . $url_parts["query"];
				}
				if (isset($url_parts["fragment"])) {
					$result .= "#" . $url_parts["fragment"];
				}
			}
		}
	}
	
	return $result;
}

/**
 * Generates a Single Sign On secret for a give user
 *  
 * @param ElggUser $user      user to generate the secret for
 * @param int      $timestamp timestamp to limit the durability of the secret
 * 
 * @return boolean|string
 */
function ws_pack_generate_sso_secret(ElggUser $user, $timestamp) {
	static $running_cache;
	
	$result = false;
	
	if (!empty($user) && elgg_instanceof($user, "user", null, "ElggUser")) {
		if (!isset($running_cache)) {
			$running_cache = array();
		}
		
		if (!isset($running_cache[$user->getGUID()])) {
			$running_cache[$user->getGUID()] = md5($user->getGUID() . get_site_secret() . $user->salt . $timestamp);
		}
		
		$result = $running_cache[$user->getGUID()];
	}
	
	return $result;
}

/**
 * Validate a Single Sign On secret for a given user
 * 
 * @param string $user_guid guid of the user to check
 * @param string $secret    secret code to validate
 * 
 * @return boolean
 */
function ws_pack_validate_sso_secret($user_guid, $secret, $timestamp) {
	global $CONFIG;
	
	if (empty($user_guid) || empty($secret) || empty($timestamp)) {
		return false;
	}
	
	// check user
	$user = get_user($user_guid);
	if (!$user) {
		return false;
	}
	
	// validate timestamp
	// keeping this in line with the remember me cookie lifetime
	$timestamp = sanitize_int($timestamp);
	$expiration_timestamp = $CONFIG->cookies['remember_me']['expire'];
	if ($timestamp < $expiration_timestamp) {
		return false;
	}
	
	// generate secret
	$correct_secret = ws_pack_generate_sso_secret($user, $timestamp);
	if (!$correct_secret) {
		return false;
	}
	
	// validate provided secret
	if ($correct_secret === $secret) {
		return true;
	}
	
	return false;
}

/**
 * Shutdown function to reset the counter on the pushnotification service
 * 
 * @return void
 */
function ws_pack_shutdown_user_counter() {
	
	if ($user = elgg_get_logged_in_user_entity()) {
		$options = array(
			"type" => "object",
			"subtype" => APIApplicationUserSetting::SUBTYPE,
			"owner_guid" => $user->getGUID(),
			"limit" => false
		);
		
		if ($api_user_settings = elgg_get_entities($options)) {
			foreach ($api_user_settings as $api_user_setting) {
				$api_user_setting->resetPushNotificationCounter();
			}
		}
	}
}

/**
 * Strips secret stuff from current url and forwards
 * 
 * @return void
 */
function ws_pack_forward_without_secret() {
	$url_parts = parse_url(current_page_url());
		
	parse_str($url_parts["query"], $query);
	unset($query['u']);
	unset($query['s']);
	unset($query['t']);
	$url_parts["query"] = http_build_query($query);	

	if (empty($url_parts['query'])) {
		unset($url_parts['query']);
	}
	
	if (is_callable("http_build_url")) {
		$href = http_build_url($url_parts);
	} else {
		$href = "";
		if (isset($url_parts["scheme"])) {
			$href .= $url_parts["scheme"] . "://";
		}
		if (isset($url_parts["host"])) {
			$href .= $url_parts["host"];
		}
		if (isset($url_parts["path"])) {
			$href .= $url_parts["path"];
		}
		if (isset($url_parts["query"])) {
			$href .= "?" . $url_parts["query"];
		}
		if (isset($url_parts["fragment"])) {
			$href .= "#" . $url_parts["fragment"];
		}
	}
	
	forward($href);
}
