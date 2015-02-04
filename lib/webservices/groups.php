<?php
/**
 * Groups webservices for ws_pack
 */
if (elgg_is_active_plugin("groups")) {
	ws_pack_groups_expose_functions();
}
	
/**
 * Exposes the groups functions
 *
 * @return void
 */
function ws_pack_groups_expose_functions() {
	// get (all) groups
	/*
	expose_function(
		"groups.get",
		"ws_pack_groups_get",
		array(
			"filter" => array(
				"type" => "string",
				"required" => true
			),
			"offset" => array(
				"type" => "int",
				"required" => false,
				"default" => 0
			),
			"limit" => array(
				"type" => "int",
				"required" => false,
				"default" => 10
			)
		),
		elgg_echo("ws_pack:api:groups:get"),
		"GET",
		true,
		false
	);
	*/
		
	// get groups user_guid is a member of
	expose_function(
		"groups.member_of",
		"ws_pack_groups_member_of",
		array(
			"user_guid" => array(
				"type" => "int",
				"required" => false,
				"default" => 0
			),
			"offset" => array(
				"type" => "int",
				"required" => false,
				"default" => 0
			),
			"limit" => array(
				"type" => "int",
				"required" => false,
				"default" => 10
			)
		),
		elgg_echo("ws_pack:api:groups:member_of"),
		"GET",
		true,
		true
	);
}
	
/**
 * Returns group entities for a given filter
 * 
 * @param string $filter name of the filter
 * @param int    $offset offset
 * @param int    $limit  limit
 * 
 * @return ErrorResult|array
 */
function ws_pack_groups_get($filter, $offset = 0, $limit = 10) {
	$result = false;
	
	//default options
	$options = array(
		"type" => "group",
		"offset" => $offset,
		"limit" => $limit
	);
	
	// what to do
	switch ($filter) {
		case "all":
			if ($entities = elgg_get_entities($options)) {
				$result = ws_pack_export_entities($entities);
			}
	}
	
	if ($result === false) {
		$result = new ErrorResult(elgg_echo("notfound"), WS_PACK_API_NO_RESULTS);
	}
	
	return $result;
}

/**
 * Returns the groups a user is member of
 * 
 * @param int $user_guid user guid
 * @param int $offset    offset
 * @param int $limit     limit
 * 
 * @return array|ErrorResult
 */
function ws_pack_groups_member_of($user_guid = 0, $offset = 0, $limit = 10) {
	$result = false;
	
	// default to the current logged in user
	if (empty($user_guid)) {
		$user_guid = elgg_get_logged_in_user_guid();
	}
	
	if (!empty($user_guid)) {
		$options = array(
			"type" => "group",
			"relationship" => "member",
			"relationship_guid" => $user_guid,
			"offset" => $offset,
			"limit" => $limit
		);
		
		if ($entities = elgg_get_entities_from_relationship($options)) {
			$result = ws_pack_export_entities($entities);
		}
	}
	
	if ($result === false) {
		$result = new ErrorResult(elgg_echo("notfound"), WS_PACK_API_NO_RESULTS);
	}
	
	return $result;
}