<?php
/**
 * Events for ws_pack
 */

/**
 * Send push notifications if event is added to the activity river
 * 
 * @param string        $event      name of the event
 * @param string        $type       type of the event
 * @param ElggRiverItem $river_item river item object
 * 
 * @return void
 */
function ws_pack_created_river_event_handler($event, $type, $river_item) {
	
	if (!empty($river_item) && ($river_item instanceof ElggRiverItem)) {
		
		if (!function_exists('str_get_html')) {
			elgg_load_library("simple_html_dom");
		}
		
		$message = "";
		$html_view = elgg_view_river_item($river_item);
		if ($res = str_get_html($html_view)) {
			// get the river summary
			if ($summary_element = $res->find("div.elgg-river-summary")) {
				$summary_element = $summary_element[0];
					
				$text = $summary_element->innertext();
				list($left, $right) = explode("<span class=\"elgg-river-timestamp\">", $text);
					
				$message = trim(elgg_strip_tags($left));
			}
		}
		
		if (!empty($message)) {
			$user_guids = false;
			
			switch ($river_item->access_id) {
				case ACCESS_PRIVATE:
					// do nothing
					break;
				case ACCESS_PUBLIC:
				case ACCESS_LOGGED_IN:
					// notify everyone
					$site_user_options = array(
						"type" => "user",
						"limit" => false,
						"relationship" => "member_of_site",
						"relationship_guid" => elgg_get_site_entity()->getGUID(),
						"inverse_relationship" => true,
						"callback" => "ws_pack_row_to_guid"
					);
					
					$user_guids = elgg_get_entities_from_relationship($site_user_options);
					break;
				case ACCESS_FRIENDS:
					// notify friends of subject_guid
					$friends_options = array(
						"type" => "user",
						"limit" => false,
						"relationship" => "friend",
						"relationship_guid" => $river_item->subject_guid,
						"callback" => "ws_pack_row_to_guid",
						"joins" => array("JOIN " . elgg_get_config("dbprefix") . "entity_relationships r2 ON e.guid = r2.guid_one"),
						"wheres" => array("(r2.relationship = 'member_of_site' AND r2.guid_two = " . elgg_get_site_entity()->getGUID() . ")")
					);
					
					$user_guids = elgg_get_entities_from_relationship($friends_options);
					break;
				default:
					// probably some acl, so notify members of the acl
					$user_guids = get_members_of_access_collection($river_item->access_id, true);
					break;
			}
			
			// we found potential interested users, so push
			if (!empty($user_guids) && is_array($user_guids)) {
				$api_application_options = array(
					"type" => "object",
					"subtype" => APIApplication::SUBTYPE,
					"limit" => false,
					"annotation_name" => "push_notification_service"
				);
				
				if ($api_applications = elgg_get_entities_from_annotations($api_application_options)) {
					foreach ($api_applications as $api_application) {
						$api_application->sendPushNotification($message, $user_guids);
					}
				}
			}
		}
	}
}
