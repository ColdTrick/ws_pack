<?php
/**
 * Members webservices for ws_pack
 */
ws_pack_members_expose_functions();

/**
 * Exposes the members functions
 *
 * @return void
 */
function ws_pack_members_expose_functions() {
	expose_function(
		"members.get_friends",
		"ws_pack_get_friends",
		array(), 
		'', 
		'GET', 
		true, 
		true
	);
	
	expose_function(
		"members.search_members", 
		"ws_pack_search_members", 
		array(
			"search_str" => array(
				"type" => "string",
				"required" => true 
			) 
		), 
		'', 
		'GET', 
		true, 
		true
	);
	
	expose_function(
		"members.get_member", 
		"ws_pack_get_member", 
		array(
			"guid" => array (
				"type" => "string",
				"required" => true 
			) 
		), 
		'', 
		'GET', 
		true, 
		true
	);
}

/**
 * Get Friends
 * 
 * @return SuccessResult|ErrorResult
 */
function ws_pack_get_friends() {
	$result = false;

	$user = elgg_get_logged_in_user_entity();
	$api_application = ws_pack_get_current_api_application();
	
	if (!empty($user) && !empty($api_application)) {

		$search_results = $user->getFriends(array("limit" => false));

		if ($search_results !== false) {
			$search_results["entities"] = ws_pack_export_entities($search_results);
			$result = new SuccessResult($search_results);
		}
	}
	if ($result === false) {
		$result = new ErrorResult(elgg_echo("ws_pack:error:notfound"));
	}
	
	return $result;
}

/**
 * Serch members
 * 
 * @param string $search_str String to find
 *
 * @return SuccessResult|ErrorResult
 */
function ws_pack_search_members($search_str) {
	$result = false;

	$user = elgg_get_logged_in_user_entity();
	$api_application = ws_pack_get_current_api_application();
	
	if (!empty($user) && !empty($api_application)) {
		$params = array();
		$params["query"] = $search_str;
		$params["type"] = "user";

		$search_results = elgg_trigger_plugin_hook("search", "user", $params, array());
		if ($search_results === false) {
			// error
		} else {
			$search_results["entities"] = ws_pack_export_entities($search_results["entities"]);
			$result = new SuccessResult($search_results);
		}
	}
	
	if ($result === false) {
		$result = new ErrorResult(elgg_echo("ws_pack:error:notfound"));
	}
	
	return $result;
}

/**
 * Serch members
 * 
 * @param int $guid Member GUID
 *
 * @return SuccessResult|ErrorResult
 */
function ws_pack_get_member($guid) {
	$result = false;

	$user = elgg_get_logged_in_user_entity();
	$api_application = ws_pack_get_current_api_application();
	
	if (!empty($user) && !empty($api_application)) {
		
		$member_result = get_entity($guid);
		if ($member_result !== false) {
			$member_result = ws_pack_export_entity($member_result);
			$result = new SuccessResult($member_result);
		}
	}
	
	if ($result === false) {
		$result = new ErrorResult(elgg_echo("ws_pack:error:notfound"));
	}
	
	return $result;
}
