<?php
/**
 * Entities webservices for ws_pack
 */
ws_pack_entities_expose_functions();

/**
 * Exposes the entities functions
 *
 * @return void
 */
function ws_pack_entities_expose_functions() {
	expose_function(
		"entities.get_entity", 
		"ws_pack_get_entity", 
		array (
			"id" => array (
				"type" => "int",
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
 * Get Entity
 * 
 * @param int $id Entity GUID
 *
 * @return SuccessResult|ErrorResult
 */
function ws_pack_get_entity($id) {
	$result = false;

	$user = elgg_get_logged_in_user_entity();
	$api_application = ws_pack_get_current_api_application();
	
	if (!empty($user) && !empty($api_application)) {
		$options = array("guids" => explode(",", $id));
		$entity = elgg_get_entities($options);
		if ($entity === false) {
			// error
		} else {
			$entity["entity"] = ws_pack_export_entities($entity);
			$result = new SuccessResult($entity);
		}
	}
	
	if ($result === false) {
		$result = new ErrorResult(elgg_echo("ws_pack:error:notfound"));
	}
	
	return $result;
}
