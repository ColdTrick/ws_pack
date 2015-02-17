<?php
/**
 * Notifications webservices for ws_pack
 */
ws_pack_notifications_expose_functions();

/**
 * Exposes the notifications functions
 *
 * @return void
 */
function ws_pack_notifications_expose_functions() {
	expose_function(
		"notifications.get_notifications", 
		"ws_pack_get_notifications", 
		array (), 
		'', 
		'GET', 
		true, 
		true
	);
}

/**
 * Get Notifications
 *
 * @return SuccessResult|ErrorResult
 */
function ws_pack_get_notifications() {
	$result = false;

	$user = elgg_get_logged_in_user_entity();
	$api_application = ws_pack_get_current_api_application();
	
	if (!empty($user) && !empty($api_application)) {
		
		if (!$user_guid) {
			$user_guid = elgg_get_logged_in_user_guid();
		}
		
		$options = array(
			'type' => 'object',
			'subtype' => 'site_notification',
			'limit' => 50,
			'order_by' => 'e.last_action desc',
			'owner_guid' => $user->guid,
			'full_view' => false,
			'relationship' => 'hasActor',
			'no_results' => elgg_echo('site_notifications:empty'),
		);
		
		$notifications = elgg_get_entities_from_metadata($options);
		
		if ($notifications === false) {
			$result = new ErrorResult(elgg_echo("ws_pack:error:notfound"));
		} else {
			
			$notifications["entities"] = ws_pack_export_entities($notifications);
			
			$guids = array();
			
			foreach ($notifications["entities"] as $key => $notification) {
				$notification_guid = $notification["guid"];
				
				if (!in_array($notification_guid,$guids)) {

					$guids[] = $notification_guid;
					$notification_entity = get_entity($notification_guid);
					
					$owner = get_entity($notification["owner_guid"]);
					$notification["owner"] = ws_pack_export_entity($owner);
					$notification["parent_guid"] = $notification_entity->getURL();
					
					$notifications["entities"][$key] = $notification;
				} else {
					unset($notifications["entities"][$key]);
				}
			}
			$result = new SuccessResult($notifications);
		}
		if ($result === false) {
			$result = new ErrorResult(elgg_echo("ws_pack:error:notfound"));
		}
	}
	
	return $result;
}
