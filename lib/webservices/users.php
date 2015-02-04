<?php
/**
 * Users webservices for ws_pack
 */
ws_pack_users_expose_functions();

/**
 * Exposes the users functions
 *
 * @return void
 */
function ws_pack_users_expose_functions() {
	expose_function(
		"users.get_logged_in_user", 
		"ws_pack_users_get_logged_in_user",
		array(),
		elgg_echo("ws_pack:api:users:get_logged_in_user"),
		"GET",
		true,
		true
	);
	
	expose_function(
		"users.register_for_push_notifications", 
		"ws_pack_users_register_for_push_notifications",
		array(
			"service_name" => array(
				"type" => "string",
				"required" => true
			),
			"settings" => array(
				"type" => "array",
				"required" => true
			)
		),
		elgg_echo("ws_pack:api:users:register_for_push_notifications"),
		"POST",
		true,
		true
	);
	
	expose_function(
		"users.unregister_from_push_notifications", 
		"ws_pack_users_unregister_from_push_notifications",
		array(
			"service_name" => array(
				"type" => "string",
				"required" => true
			)
		),
		elgg_echo("ws_pack:api:users:unregister_from_push_notifications"),
		"POST",
		true,
		true
	);
}

/**
 * Returns the logged in user entity
 * 
 * @return SuccessResult|ErrorResult
 */
function ws_pack_users_get_logged_in_user() {
	$result = false;
	
	if ($user = elgg_get_logged_in_user_entity()) {
		if ($export = ws_pack_export_entity($user)) {
			$result = new SuccessResult($export);
		}
	}
	
	if ($result === false) {
		$result = new ErrorResult(elgg_echo("notfound"));
	}
	
	return $result;
}

/**
 * Registers the current user to a given push notification service
 * 
 * @param string $service_name name of the service
 * @param array  $settings     settings related to the user
 * 
 * @return SuccessResult|ErrorResult
 */
function ws_pack_users_register_for_push_notifications($service_name, $settings) {
	$result = false;
	
	$user = elgg_get_logged_in_user_entity();
	$api_application = ws_pack_get_current_api_application();
	
	if (!empty($user) && !empty($api_application)) {
		
		if ($api_application_user_settings = ws_pack_get_application_user_settings($user, $api_application)) {
			
			switch ($service_name) {
				case "appcelerator":
					if ($api_application_user_settings->registerForPushNotifications($service_name, $settings)) {
						$result = new SuccessResult($service_name);
					}
					break;
				default:
					$result = new ErrorResult(elgg_echo("ws_pack:push_notifications:error:unsupported_service", array($service_name)));
					break;
			}
		} else {
			$result = new ErrorResult(elgg_echo("ws_pack:user_settings:error:notfound"));
		}
	}
	
	if ($result === false) {
		$result = new ErrorResult(elgg_echo("ws_pack:users:register_for_push_notifications:error"));
	}
	
	return $result;
}

/**
 * Unregisters the current user from a given push notification service
 * 
 * @param string $service_name name of the service
 * 
 * @return SuccessResult|ErrorResult
 */
function ws_pack_users_unregister_from_push_notifications($service_name) {
	$result = false;
	
	$user = elgg_get_logged_in_user_entity();
	$api_application = ws_pack_get_current_api_application();
	
	if (!empty($user) && !empty($api_application)) {
		
		if ($api_application_user_settings = ws_pack_get_application_user_settings($user, $api_application)) {
			
			switch ($service_name) {
				case "appcelerator":
					if ($api_application_user_settings->unregisterFromPushNotifications($service_name)) {
						$result = new SuccessResult($service_name);
					}
					break;
				default:
					$result = new ErrorResult(elgg_echo("ws_pack:push_notifications:error:unsupported_service", array($service_name)));
					break;
			}
		} else {
			$result = new ErrorResult(elgg_echo("ws_pack:user_settings:error:notfound"));
		}
	}
	
	if ($result === false) {
		$result = new ErrorResult(elgg_echo("ws_pack:users:unregister_from_push_notifications:error"));
	}
	
	return $result;
}
