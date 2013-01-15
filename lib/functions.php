<?php

	function ws_pack_get_application_from_id($application_id){
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
	