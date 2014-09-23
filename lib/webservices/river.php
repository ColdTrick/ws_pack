<?php
/**
 * River webservices for ws_pack
 */
ws_pack_river_expose_functions();

/**
 * Exposes the river functions
 *
 * @return void
 */
function ws_pack_river_expose_functions() {
	expose_function(
		"river.get", 
		"ws_pack_river_get",
		array(
			"filter" => array(
				"type" => "string",
				"required" => true
			),
			"guids" => array(
				"type" => "array",
				"required" => false,
				"default" => array()
			),
			"offset" => array(
				"type" => "int",
				"required" => false,
				"default" => 0
			),
			"limit" => array(
				"type" => "int",
				"required" => false,
				"default" => 25
			),
			"posted_time_lower" => array(
				"type" => "int",
				"required" => false,
				"default" => 0
			)
		),
		elgg_echo("ws_pack:api:river:get"),
		"GET",
		true,
		true
	);
}

/**
 * Returns river data
 * 
 * @param string $filter            filter name
 * @param array  $guids             guids of groups
 * @param int    $offset            offset
 * @param int    $limit             limit
 * @param int    $posted_time_lower lower time stamp to limit results
 * 
 * @return array|ErrorResult
 */
function ws_pack_river_get($filter, $guids = array(), $offset = 0, $limit = 25, $posted_time_lower = 0) {
	$result = false;
	
	$dbprefix = elgg_get_config("dbprefix");
	
	// default options
	$options = array(
		"offset" => $offset,
		"limit" => $limit,
		"posted_time_lower" => $posted_time_lower,
		"joins" => array(
			"JOIN " . $dbprefix . "entities sue ON rv.subject_guid = sue.guid",
			"JOIN " . $dbprefix . "entities obe ON rv.object_guid = obe.guid"
		),
		"wheres" => array(
			"(sue.enabled = 'yes' AND obe.enabled = 'yes')"
		)
	);
	
	// what to return
	switch ($filter) {
		case "mine":
			$options["subject_guid"] = elgg_get_logged_in_user_guid();
			
			break;
		case "friends":
			$options["relationship_guid"] = elgg_get_logged_in_user_guid();
			$options["relationship"] = "friend";
			
			break;
		case "groups":
			if (empty($guids)) {
				// get group guids
				$group_options = array(
					"type" => "group",
					"relationship" => "member",
					"relationship_guid" => elgg_get_logged_in_user_guid(),
					"limit" => false,
					"callback" => "ws_pack_row_to_guid"
				);
				
				$guids = elgg_get_entities_from_relationship($group_options);
			}
			
			// check if there are groups
			if (!empty($guids)) {
				$options["joins"] = array("JOIN " . $dbprefix . "entities e ON rv.object_guid = e.guid");
				$options["wheres"] = array("(rv.object_guid IN (" . implode(",", $guids) . ") OR e.container_guid IN (" . implode(",", $guids) . "))");
			} else {
				// no groups found, so make sure not to return anything
				$options = false;
			}
			
			break;
		case "all":
		default:
			// list everything
			break;
	}
	
	// get river items
	if ($options && ($items = elgg_get_river($options))) {
		$result = ws_pack_export_river_items($items);
	}
	
	// did we get river items
	if ($result === false) {
		$result = new ErrorResult(elgg_echo("river:none"), WS_PACK_API_NO_RESULTS);
	}
	
	return $result;
}
