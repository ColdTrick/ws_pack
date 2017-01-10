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
	elgg_ws_expose_function(
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

	elgg_ws_expose_function(
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

	elgg_ws_expose_function(
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

	elgg_ws_expose_function(
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
	$user = elgg_get_logged_in_user_entity();
	if (empty($user)) {
		return new ErrorResult(elgg_echo('ws_pack:error:notfound'));
	}
		
	$topic = new ElggObject();

	$topic->title = $title;
	$tags = explode(',', $tags);
	$topic->tags = $tags;
	
	$topic->description = $content;
	$topic->subtype = 'discussion';
	$topic->owner_guid = $user->guid;
	$topic->access_id = $access;
	$topic->container_guid = $group_guid;
	
	$post_discussion = $topic->save();
	
	if (!$post_discussion) {
		return new ErrorResult(elgg_echo('ws_pack:error:notfound'));
	}
	
	return new SuccessResult($post_discussion);
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
	
	$user = elgg_get_logged_in_user_entity();
	if (empty($user)) {
		return new ErrorResult(elgg_echo('ws_pack:error:notfound'));
	}
		
	$topic = new ElggObject();

	$topic->description = $content;
	$topic->subtype = "discussion_reply";
	$topic->owner_guid = $user->guid;
	$topic->container_guid = $parent_guid;
	
	$post_discussion = $topic->save();

	if (!$post_discussion) {
		return new ErrorResult(elgg_echo('ws_pack:error:notfound'));
	}
	
	return new SuccessResult($post_discussion);
}

/**
 * Get Discussions from a group
 *
 * @param int $group_guid Group GUID
 *
 * @return SuccessResult|ErrorResult
 */
function ws_pack_get_discussions($group_guid) {
	
	$user = elgg_get_logged_in_user_entity();
	if (empty($user)) {
		return new ErrorResult(elgg_echo('ws_pack:error:notfound'));
	}
	
	$discussions = elgg_get_entities([
		'type' => 'object',
		'subtype' => array('discussion'),
		'limit' => 50,
		'order_by' => 'e.last_action desc',
		'container_guid' => $group_guid,
	]);
	
	if ($discussions === false) {
		return new ErrorResult(elgg_echo('ws_pack:error:notfound'));
	}
	
	$result['entities'] = ws_pack_export_entities($discussions);
	$guids = [];
	
	foreach ($result['entities'] as $key => $discussion) {
		$discussion_guid = $discussion['guid'];
		
		if (!in_array($discussion_guid, $guids)) {
			$guids[] = $discussion_guid;
			$owner = get_entity($discussion['owner_guid']);
			$discussion['owner'] = ws_pack_export_entity($owner);
			$result['entities'][$key] = $discussion;

		} else {
			unset($result['entities'][$key]);
		}
	}
	
	return new SuccessResult($result);
}

/**
 * Get Discussion
 *
 * @param int $guid Discussion GUID
 *
 * @return SuccessResult|ErrorResult
 */
function ws_pack_get_discussion($guid) {
	$user = elgg_get_logged_in_user_entity();
	if (empty($user)) {
		return new ErrorResult(elgg_echo('ws_pack:error:notfound'));
	}
	
	$entity = get_entity($guid);
	if (empty($entity) || $entity->getSubtype() !== 'discussion') {
		return new ErrorResult(elgg_echo('ws_pack:error:notfound'));
	}
	
	$discussion = ws_pack_export_entity($entity);
	$discussion['owner'] = ws_pack_export_entity($entity->getOwnerEntity());

	$discussion['replies'] = [];
	
	$replies = elgg_get_entities([
		'type' => 'object',
		'subtype' => 'discussion_reply',
		'limit' => false,
		'container_guid' => $guid,
	]);
	
	if ($replies) {
		foreach ($replies as $reply) {
			$reply_object = ws_pack_export_entity($reply);
			$reply_object['owner'] = ws_pack_export_entity($reply->getOwnerEntity());
			
			$discussion['replies'][] = $reply_object;
		}
	}
	
	$result['entities'][] = $discussion;
	
	return new SuccessResult($result);
}
