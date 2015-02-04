<?php
/**
 * System webservices for ws_pack
 */
ws_pack_system_expose_functions();

/**
 * Exposes the system functions
 *
 * @return void
 */
function ws_pack_system_expose_functions() {
	expose_function(
		"system.api.register_push_notification_service", 
		"ws_pack_system_api_register_push_notification_service",
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
		elgg_echo("ws_pack:api:system:api:register_push_notification_service"),
		"POST",
		true,
		false
	);
	
	expose_function(
		"system.api.unregister_push_notification_service", 
		"ws_pack_system_api_unregister_push_notification_service",
		array(
			"service_name" => array(
				"type" => "string",
				"required" => true
			)
		),
		elgg_echo("ws_pack:api:system:api:unregister_push_notification_service"),
		"POST",
		true,
		false
	);
	
}

/**
 * Register a push notification service to the current api application
 * 
 * @param string $service_name name of the notification service
 * @param array  $settings     settings for this service
 * 
 * @return ErrorResult|SuccessResult
 */
function ws_pack_system_api_register_push_notification_service($service_name, $settings) {
	$result = false;
	
	if ($api_application = ws_pack_get_current_api_application()) {
		
		switch ($service_name) {
			case "appcelerator":
				if ($api_application->registerPushNotificationService($service_name, $settings)) {
					$result = new SuccessResult($service_name);
				}
				break;
			default:
				$result = new ErrorResult(elgg_echo("ws_pack:push_notifications:error:unsupported_service", array($service_name)));
				break;
		}
	}
	
	if ($result === false) {
		$result = new ErrorResult(elgg_echo("ws_pack:system:api:register_push_notification_service:error"));
	}
	
	return $result;
}

/**
 * Unregister a push notifications from the current api application
 * 
 * @param string $service_name name of the service
 * 
 * @return ErrorResult|SuccessResult
 */
function ws_pack_system_api_unregister_push_notification_service($service_name) {
	$result = false;
	
	if ($api_application = ws_pack_get_current_api_application()) {
		
		switch ($service_name) {
			case "appcelerator":
				if ($api_application->unregisterPushNotificationService($service_name)) {
					$result = new SuccessResult($service_name);
				}
				break;
			default:
				$result = new ErrorResult(elgg_echo("ws_pack:push_notifications:error:unsupported_service", array($service_name)));
				break;
		}
	}
	
	if ($result === false) {
		$result = new ErrorResult(elgg_echo("ws_pack:system:api:unregister_push_notification_service:error"));
	}
	
	return $result;
}
