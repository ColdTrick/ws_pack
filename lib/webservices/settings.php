<?php
/**
 * Settings webservices for ws_pack
 */
ws_pack_settings_expose_functions();

/**
 * Exposes the settings functions
 *
 * @return void
 */
function ws_pack_settings_expose_functions() {
	
	expose_function(
		"settings.get_logged_in_user", 
		"ws_pack_settings_get_logged_in_user",
		array(),
		elgg_echo("ws_pack:api:settings:get_logged_in_user"),
		"GET",
		true,
		true
	);

	expose_function(
		"settings.get_settings",
		"ws_pack_get_settings",
		array(
			"service_name" => array(
				"type" => "string",
				"required" => false
			),
			"settings" => array(
				"type" => "array",
				"required" => false
			)
		),
		elgg_echo("ws_pack:api:settings:get_settings"),
		"GET",
		true,
		true
	);
}

function ws_pack_settings_get_logged_in_user() {
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

function ws_pack_get_settings($service_name, $settings) {
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
