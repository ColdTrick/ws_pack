<?php
/**
 * Community webservices for ws_pack
 */
ws_pack_community_expose_functions();

/**
 * Exposes the community functions
 *
 * @return void
 */
function ws_pack_community_expose_functions() {
	expose_function(
		"community.get_plugins",
		"ws_pack_get_plugins",
		array(),
		'',
		'GET',
		true,
		true
	);

	expose_function(
		"community.get_color_scheme",
		"ws_pack_get_color_scheme",
		array(),
		'',
		'GET',
		true,
		true
	);
}

/**
 * Returns the plugins
 * 
 * @return SuccessResult|ErrorResult
 */
function ws_pack_get_plugins() {
	$result = false;

	$user = elgg_get_logged_in_user_entity();
	$api_application = ws_pack_get_current_api_application();
	
	if (!empty($user) && !empty($api_application)) {
		
		$check_active = array("members", "groups", "messages", "messageboard", "site_notifications", "profile", "search", "thewire");
		
		$active = array();
		foreach ($check_active as $ca) {
			if (elgg_is_active_plugin($ca)) {
				$active[$ca] = "enabled";	
			} else {
				$active[$ca] = "disabled";	
			}
		}
		
		if ($active === false) {
			// error
		} else {
			$result = new SuccessResult($active);
		}
	}
	
	if ($result === false) {
		$result = new ErrorResult(elgg_echo("ws_pack:error:notfound"));
	}
	
	return $result;
}

/**
 * Returns the colorscheme
 * 
 * @return SuccessResult|ErrorResult
 */
function ws_pack_get_color_scheme() {
	$result = false;

	$user = elgg_get_logged_in_user_entity();
	$api_application = ws_pack_get_current_api_application();
	
	if (!empty($user) && !empty($api_application)) {
		
		if ($color_1 = elgg_get_plugin_setting('color_1', 'ws_pack')) {
			$result = $color_1;
		}
	
		if ($result === false) {
			$result = new ErrorResult(elgg_echo("notfound"));
		}
	}
	
	if ($result === false) {
		$result = new ErrorResult(elgg_echo("ws_pack:error:notfound"));
	}
	return $result;
}
