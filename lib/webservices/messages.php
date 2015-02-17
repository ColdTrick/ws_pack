<?php
/**
 * Messages webservices for ws_pack
 */
ws_pack_messages_expose_functions();

/**
 * Exposes the messages functions
 *
 * @return void
 */
function ws_pack_messages_expose_functions() {
	expose_function(
		"messages.send_message", 
		"ws_pack_send_message", 
		array (
			"subject" => array (
				"type" => "string",
				"required" => true 
			),
			"message" => array (
				"type" => "string",
				"required" => true 
			),
			"recipient" => array (
				"type" => "int",
				"required" => true 
			) 
		), 
		'', 
		'POST', 
		true, 
		true
	);

	expose_function(
		"messages.get_messages", 
		"ws_pack_get_messages",
		array (), 
		'', 
		'GET', 
		true, 
		true
	);

	expose_function(
		"messages.get_conversation", 
		"ws_pack_get_conversation", 
		array (
			"user_guid" => array (
				"type" => "int",
				"required" => false 
			),
			"fromto_guid" => array (
				"type" => "int",
				"required" => false 
			),
			"relationship" => array (
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
		"messages.get_last_conversations",
		"ws_pack_get_last_conversations", 
		array (
			"user_guid" => array (
				"type" => "int",
				"required" => false 
			) 
		), 
		'', 
		'GET', 
		true, 
		true
	);

	expose_function(
		"messages.delete_message", 
		"ws_pack_delete_message", 
		array (
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
 * @param string $subject   Subject of the message
 * @param string $message   Content of message
 * @param int    $recipient Recipient GUID
 * @param string $sender    User GUID
 *
 * @return SuccessResult|ErrorResult
 */
function ws_pack_send_message($subject, $message, $recipient, $sender) {
	$result = false;

	$user = elgg_get_logged_in_user_entity();
	$api_application = ws_pack_get_current_api_application();
	
	if (!empty($user) && !empty($api_application)) {
		$send_message = messages_send($subject, $message, $recipient, $user->guid);
		if ($send_message !== false) {
			$result = new SuccessResult($send_message);
		}
	}
	if ($result === false) {
		$result = new ErrorResult(elgg_echo("ws_pack:error:notfound"));
	}
	
	return $result;
}

/**
 * Get Messages
 *
 * @return SuccessResult|ErrorResult
 */
function ws_pack_get_messages() {
	$result = false;

	$user = elgg_get_logged_in_user_entity();
	$api_application = ws_pack_get_current_api_application();
	
	if (!empty($user) && !empty($api_application)) {
		$messages = messages_get_unread($user->guid);
		if ($messages !== false) {
			$messages["entities"] = ws_pack_export_entities($messages);
			$result = new SuccessResult($messages);
		}
	}
	if ($result === false) {
		$result = new ErrorResult(elgg_echo("ws_pack:error:notfound"));
	}
	
	return $result;
}

/**
 * Get Conversation
 * 
 * @param int $user_guid    User GUID
 * @param int $fromto_guid  From User GUID 
 * @param int $relationship Relationship
 * @param int $limit        Limit of Conversations
 * @param int $offset       Offset
 * @param int $count        Count
 *
 * @return SuccessResult|ErrorResult
 */
function ws_pack_get_conversation($user_guid, $fromto_guid, $relationship, $limit = 100, $offset = 0, $count = false) {
	$result = false;

	$user = elgg_get_logged_in_user_entity();
	$api_application = ws_pack_get_current_api_application();
	
	if (!empty($user) && !empty($api_application)) {
		
		if (!$user_guid) {
			$user_guid = elgg_get_logged_in_user_guid();
		}
		
		$db_prefix = elgg_get_config('dbprefix');
		
		$strings = array('toId', $user_guid, 'msg', 1, 'fromId', $fromto_guid);
		
		$map = array();
		foreach ($strings as $string) {
			$id = elgg_get_metastring_id($string);
			$map[$string] = $id;
		}
		
		$options = array(
			'joins' => array(
				"JOIN {$db_prefix}metadata msg_toId on e.guid = msg_toId.entity_guid",
				"JOIN {$db_prefix}metadata msg_msg on e.guid = msg_msg.entity_guid",
				"JOIN {$db_prefix}metadata msg_fromId on e.guid = msg_fromId.entity_guid",
			),
			'owner_guid' => $user_guid,
			'limit' => $limit,
			'offset' => $offset,
			'count' => $count,
			'order_by' => 'time_updated',
		);
		
		if ($relationship == "from") {
			$options["wheres"] = array(
				"msg_toId.name_id='{$map['toId']}' AND msg_toId.value_id='{$map[$user_guid]}'",
				"msg_msg.name_id='{$map['msg']}' AND msg_msg.value_id='{$map[1]}'",
				"msg_fromId.name_id='{$map['fromId']}' AND msg_fromId.value_id='{$map[$fromto_guid]}'",
			);
		} elseif ($relationship == "to") {
			$options["wheres"] = array(
				"msg_fromId.name_id='{$map['fromId']}' AND msg_fromId.value_id='{$map[$user_guid]}'",
				"msg_msg.name_id='{$map['msg']}' AND msg_msg.value_id='{$map[1]}'",
				"msg_toId.name_id='{$map['toId']}' AND msg_toId.value_id='{$map[$fromto_guid]}'",
			);
		}
		
		$messages = elgg_get_entities_from_metadata($options);

		if ($messages !== false) {
			$messages["entities"] = ws_pack_export_entities($messages);
			$result = new SuccessResult($messages);
		}
	}
	
	if ($result === false) {
		$result = new ErrorResult(elgg_echo("ws_pack:error:notfound"));
	}
	
	return $result;
}

/**
 * Get Last Conversation
 * 
 * @param int $user_guid    User GUID
 * @param int $limit        Limit of Conversations
 * @param int $offset       Offset
 * @param int $count        Count
 *
 * @return SuccessResult|ErrorResult
 */
function ws_pack_get_last_conversations($user_guid, $limit = 100, $offset = 0, $count = false) {
	$result = false;

	$user = elgg_get_logged_in_user_entity();
	$api_application = ws_pack_get_current_api_application();
	
	if (!empty($user) && !empty($api_application)) {
		
		if (!$user_guid) {
			$user_guid = elgg_get_logged_in_user_guid();
		}
		
		$db_prefix = elgg_get_config('dbprefix');
		
		$strings = array('toId', $user_guid, 'msg', 1, 'fromId', $fromto_guid);
		
		$map = array();
		foreach ($strings as $string) {
			$id = elgg_get_metastring_id($string);
			$map[$string] = $id;
		}
		
		$options = array(
			'selects' => array(
				"MAX(e.guid) as guid",
				"MAX(e.time_created) as time_created",
			),
			'joins' => array(
				"JOIN {$db_prefix}metadata msg_toId on e.guid = msg_toId.entity_guid",
				"JOIN {$db_prefix}metadata msg_msg on e.guid = msg_msg.entity_guid",
				"JOIN {$db_prefix}metadata msg_fromId on e.guid = msg_fromId.entity_guid",
			),
			'owner_guid' => $user_guid,
			'limit' => $limit,
			'offset' => $offset,
			'count' => $count,
		);
		
		$options["wheres"] = array(
			"msg_msg.name_id='{$map['msg']}' AND msg_msg.value_id='{$map[1]}'"
		);

		$options["group_by"] = "msg_fromId.value_id, msg_toId.value_id";

		$messages = elgg_get_entities_from_metadata($options);

		if ($messages === false) {
			$result = new ErrorResult(elgg_echo("ws_pack:error:notfound"));
		} else {

			$messages["entities"] = ws_pack_export_entities($messages);
			$guids = array();
			foreach ($messages["entities"] as $key => $message) {
				$message_guid = $message["guid"];
				
				if (!in_array($message_guid,$guids)) {
					
					$guids[] = $message_guid;
					$message_entity = get_entity($message_guid);

					$recipient_id = $message_entity->toId;
					if ($recipient_id === $user_guid) {
						$recipient_id = $message_entity->fromId;
					}

					$recipient = get_entity($recipient_id);
					$message["recipient"] = ws_pack_export_entity($recipient);

					$messages["entities"][$key] = $message;
				} else {
					unset($messages["entities"][$key]);
				}
			}
			$result = new SuccessResult($messages);
		}
	}
	
	if ($result === false) {
		$result = new ErrorResult(elgg_echo("ws_pack:error:notfound"));
	}
	
	return $result;
}

/**
 * Delete Message
 * 
 * @param int $guid    Message GUID
 *
 * @return SuccessResult|ErrorResult
 */
function ws_pack_delete_message($guid) {
	$result = false;

	$user = elgg_get_logged_in_user_entity();
	$api_application = ws_pack_get_current_api_application();
	
	if (!empty($user) && !empty($api_application)) {
		$guid = (int) get_input('guid');
		$message = get_entity($guid);
		
		if (!elgg_instanceof($message, 'object', 'messages') || !$message->canEdit()) {
			$result = new ErrorResult(elgg_echo("messages:error:delete:single"));
		}
		
		if (!$message->delete()) {
			$result = new ErrorResult(elgg_echo("messages:error:delete:single"));
		} else {
			$result = new SuccessResult(true);
		}
	} else {
		$result = new ErrorResult(elgg_echo("ws_pack:error:notfound"));
	}
	
	return $result;
}
