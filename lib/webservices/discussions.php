<?php
/**
 * Discussions webservices for ws_pack
 */
ws_pack_discussions_expose_functions();

/**
 * Exposes the discussions functions
 *
 * @return void
 */
function ws_pack_discussions_expose_functions() {
	expose_function(
		"discussions.post",
		"post_discussion",
		array(	
			"title" => array(
				"type" => "string",
				"required" => false
			),
			"content" => array(
				"type" => "string",
				"required" => true
			),
			"group_guid" => array(
				"type" => "int",
				"required" => true
			),
			"tags" => array(
				"type" => "string",
				"required" => false
			),
			"access" => array(
				"type" => "int",
				"required" => false
			)
		),
		'Post to discussion',
		'POST',
		true,
		true
	);

	expose_function(
		"discussions.post_reply",
		"post_reply",
		array(	
			"content" => array(
				"type" => "string",
				"required" => true
			),
			"parent_guid" => array(
				"type" => "int",
				"required" => true
			)
		),
		'Post reply',
		'POST',
		true,
		true
	);

	expose_function(
		"discussions.get_discussions",
		"ws_pack_get_discussions",
		array(
			"group_guid" => array(
				"type" => "int",
				"required" => true
			)
		),
		'',
		'GET',
		true,
		true
	);

	expose_function(
		"discussions.get_discussion",
		"ws_pack_get_discussion",
		array(
			"guid" => array(
				"type" => "int",
				"required" => true
			)
		),
		'',
		'GET',
		true,
		true
	);

	expose_function(
		"discussions.delete_discussion",
		"ws_pack_delete_discussion", 
		array(
			"guid" => array (
				"type" => "int",
				"required" => true 
			) 
		), 
		'', 
		'POST', 
		true, 
		true
	);
}

/**
 * Post a discussion
 * 
 * @param string $title      Title of the discussion
 * @param string $content    Content of the discussion
 * @param int    $group_guid Group GUID
 * @param string $tags       Tags of the discussion
 * @param int    $access     Access value
 *
 * @return SuccessResult|ErrorResult
 */
function post_discussion($title = false, $content, $group_guid, $tags = false, $access) {
	$result = false;

	$user = elgg_get_logged_in_user_entity();
	$api_application = ws_pack_get_current_api_application();
	
	if (!empty($user) && !empty($api_application)) {
		
		$topic = new ElggObject();

		$topic->title = $title;
		$tags = explode(",", $tags);
		$topic->tags = $tags;
		
		$topic->description = $content;
		$topic->subtype = "groupforumtopic";
		$topic->owner_guid = $user->guid;
		$topic->access_id = $access;
		$topic->container_guid = $group_guid;
		
		$post_discussion = $topic->save();
		
		if ($post_discussion !== false) {
			$result = new SuccessResult($post_discussion);
		} else {
			$result = new ErrorResult(elgg_echo("ws_pack:error:notfound"));
		}
		
		return $result;
	}
}

/**
 * Post a reply
 * 
 * @param string $content    Content of the discussion
 * @param int    $group_guid Group GUID
 *
 * @return SuccessResult|ErrorResult
 */
function post_reply($content, $parent_guid) {
	
	$result = false;

	$user = elgg_get_logged_in_user_entity();
	$api_application = ws_pack_get_current_api_application();
	
	if (!empty($user) && !empty($api_application)) {
		
		$topic = new ElggObject();

		$topic->description = $content;
		$topic->subtype = "discussion_reply";
		$topic->owner_guid = $user->guid;
		$topic->container_guid = $parent_guid;
		
		$post_discussion = $topic->save();

		if ($post_discussion !== false) {
			$result = new SuccessResult($post_discussion);
		} else {
			$result = new ErrorResult(elgg_echo("ws_pack:error:notfound"));
		}
		
		return $result;
	}
}

/**
 * Get Discussions from a group
 * 
 * @param int $group_guid   Group GUID
 *
 * @return SuccessResult|ErrorResult
 */
function ws_pack_get_discussions($group_guid) {
	$result = false;

	$user = elgg_get_logged_in_user_entity();
	$api_application = ws_pack_get_current_api_application();
	
	if (!empty($user) && !empty($api_application)) {
		$options = array(
			'type' => 'object',
			'subtype' => array('groupforumtopic','discussion_reply'),
			'limit' => 50,
			'order_by' => 'e.last_action desc',
			'container_guid' => $group_guid,
			'full_view' => false,
			'no_results' => elgg_echo('discussion:none'),
		);
		
		$discussions = elgg_get_entities($options);
		
		if ($discussions === false) {
			$result = new ErrorResult(elgg_echo("ws_pack:error:notfound"));
		} else {
			$discussions["entities"] = ws_pack_export_entities($discussions);
			$guids = array();
			
			foreach ($discussions["entities"] as $key => $discussion) {
				$discussion_guid = $discussion["guid"];
				
				if (!in_array($discussion_guid,$guids)) {
					
					$guids[] = $discussion_guid;
					$owner = get_entity($discussion["owner_guid"]);
					$discussion["owner"] = ws_pack_export_entity($owner);
					$discussions["entities"][$key] = $discussion;

				} else {
					unset($discussions["entities"][$key]);
				}
			}
			$result = new SuccessResult($discussions);
		}
		if ($result === false) {
			$result = new ErrorResult(elgg_echo("ws_pack:error:notfound"));
		}
	}
	
	return $result;
}

/**
 * Get Discussion
 * 
 * @param int $guid   Discussion GUID
 *
 * @return SuccessResult|ErrorResult
 */
function ws_pack_get_discussion($guid) {
	$result = false;

	$user = elgg_get_logged_in_user_entity();
	$api_application = ws_pack_get_current_api_application();
	
	if (!empty($user) && !empty($api_application)) {
		$options = array(
			'type' => 'object',
			'order_by' => 'e.last_action desc',
			'guid' => $guid,
			'full_view' => false,
			'no_results' => elgg_echo('discussion:none'),
		);
		
		$discussions = elgg_get_entities($options);

		// returns guid of wire post
		if ($discussions === false) {
			$result = new ErrorResult(elgg_echo("ws_pack:error:notfound"));
		} else {
			
			$discussions["entities"] = ws_pack_export_entities($discussions);
			$guids = array();
			
			foreach ($discussions["entities"] as $key => $discussion) {
				$discussion_guid = $discussion["guid"];
				
				if (!in_array($discussion_guid, $guids)) {
					
					$guids[] = $discussion_guid;
					
					$owner = get_entity($discussion["owner_guid"]);
					$discussion["owner"] = ws_pack_export_entity($owner);
					
					$discussions["entities"][$key] = $discussion;
				} else {
					unset($discussions["entities"][$key]);
				}
			}
				
			$result = new SuccessResult($discussions);
		}
	
		if ($result === false) {
			$result = new ErrorResult(elgg_echo("ws_pack:error:notfound"));
		}
	}
	
	return $result;
}

/**
 * Delete Discussions from a group
 * 
 * @param int $guid Discussion GUID
 *
 * @return SuccessResult|ErrorResult
 */
function ws_pack_delete_discussion($guid) {
	$result = false;

	$user = elgg_get_logged_in_user_entity();
	$api_application = ws_pack_get_current_api_application();
	
	if (!empty($user) && !empty($api_application)) {
		$discussion = get_entity($guid);
		if ($discussion->canEdit()) {

			// unset reply metadata on children
			$children = elgg_get_entities_from_relationship(array(
				'relationship' => 'parent',
				'relationship_guid' => $guid,
				'inverse_relationship' => true,
			));
			if ($children) {
				foreach ($children as $child) {
					$child->reply = false;
				}
			}
		
			// Delete it
			$rowsaffected = $discussion->delete();
			if ($rowsaffected > 0) {
				$result = new SuccessResult(true);
			} else {
				$result = new ErrorResult(elgg_echo("ws_pack:error:notfound"));
			}
		} else {
			$result = new ErrorResult(elgg_echo("ws_pack:error:notfound"));
		}
	}
	
	return $result;
}
