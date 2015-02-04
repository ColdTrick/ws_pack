<?php
/**
 * TheWire webservices for ws_pack
 */
ws_pack_thewire_expose_functions();

/**
 * Exposes the TheWire functions
 *
 * @return void
 */
function ws_pack_thewire_expose_functions() {
	expose_function(
		"thewire.post", 
		"my_post_to_wire", 
		array (
			"text" => array (
				"type" => "string",
				"required" => true 
			),
			"guid_user" => array (
				"type" => "int",
				"required" => true 
			),
			"parent" => array (
				"type" => "int",
				"required" => false 
			) 
		), 
		'Post to the wire. 140 characters or less', 
		'POST', 
		true, 
		true
	);

	expose_function(
		"thewire.get_wires", 
		"ws_pack_get_wires", 
		array (
			"user_guid" => array (
				"type" => "int",
				"required" => false 
			),
			"relationship" => array (
				"type" => "string",
				"required" => false 
			) 
		), 
		'', 
		'GET', 
		true, 
		true
	);

	expose_function(
		"thewire.get_thread", 
		"ws_pack_get_thread", 
		array (
			"user_guid" => array (
				"type" => "int",
				"required" => true 
			),
			"thread_id" => array (
				"type" => "string",
				"required" => false 
			) 
		), 
		'', 
		'GET', 
		true, 
		true
	);

	expose_function(
		"thewire.delete_wire", 
		"ws_pack_delete_wire", 
		array (
			"user_guid" => array (
				"type" => "int",
				"required" => true 
			),
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

function my_post_to_wire($text, $guid_user, $parent) {
	$result = false;

	$user = elgg_get_logged_in_user_entity();
	$api_application = ws_pack_get_current_api_application();
	
	if (!empty($user) && !empty($api_application)) {
		
		$text = substr($text, 0, 140);

		// returns guid of wire post
		$post_wire = thewire_save_post($text, $guid_user, 2, $parent);
		if ($post_wire === false) {
			// error
		} else {
			$result = new SuccessResult($post_wire);
		}
		
		if ($result === false) {
			$result = new ErrorResult(elgg_echo("ws_pack:error:notfound"));
		}
		
		return $result;
	}
}

function ws_pack_get_wires($guid_user, $relationship = false) {
	$result = false;

	$user = elgg_get_logged_in_user_entity();
	$api_application = ws_pack_get_current_api_application();
	
	if (!empty($user) && !empty($api_application)) {
		$wires = elgg_get_entities(array(
			'type' => 'object',
			'subtype' => 'thewire',
			'limit' => 50,
		));
		
		// returns guid of wire post
		if ($wires === false) {
			// error
		} else {
			
			$wires["entities"] = ws_pack_export_entities($wires);
			$guids = array();
			
			foreach ($wires["entities"] as $key => $wire) {
				$wire_guid = $wire["guid"];
				
				if (!in_array($wire_guid,$guids)) {
					
					$guids[] = $wire_guid;
					$wire_entity = get_entity($wire_guid);
					
					$owner = get_entity($wire["owner_guid"]);
					$wire["owner"] = ws_pack_export_entity($owner);
					$wire["thread_id"] = $wire_entity->wire_thread;
					/* Add threads to the wires
					$threads = elgg_get_entities_from_metadata(array(
						"metadata_name" => "wire_thread",
						"metadata_value" => $wire_guid,
						"type" => "object",
						"subtype" => "thewire",
						"limit" => 20,
					));
					
					$wire["thread"] = ws_pack_export_entities($threads);
					
					$guids_thread = array();
					
					foreach ($wire["thread"] as $k => $wt) {
						
						$wire_guid_th = $wt["guid"];
						
						if(!in_array($wire_guid_th,$guids_thread))	{
							
							$guids_thread[] = $wire_guid_th;
							$owner_thread = get_entity($wt["owner_guid"]);
							$wt["owner"] = ws_pack_export_entity($owner_thread);
							$wire["thread"][$k] = $wt;
						}
						else {
							unset($wire["thread"][$k]);
						}
					}
					*/
					
					$wires["entities"][$key] = $wire;
				} else {
					unset($wires["entities"][$key]);
				}
			}
				
			$result = new SuccessResult($wires);
		}
	
		if ($result === false) {
			$result = new ErrorResult(elgg_echo("ws_pack:error:notfound"));
		}
	}

	return $result;
}

function ws_pack_get_thread($guid_user, $thread_id = false) {
	$result = false;

	$user = elgg_get_logged_in_user_entity();
	$api_application = ws_pack_get_current_api_application();
	
	if (!empty($user) && !empty($api_application)) {
		$threads = elgg_get_entities_from_metadata(array(
			"metadata_name" => "wire_thread",
			"metadata_value" => $thread_id,
			"type" => "object",
			"subtype" => "thewire",
			"limit" => 20,
		));
		
		// returns guid of wire post
		if ($threads === false) {
			// error
		} else {
			$wires = array();
			$wires["entities"] = ws_pack_export_entities($threads);
					
			$guids_thread = array();
			
			foreach ($wires["entities"] as $k => $wt) {
				
				$wire_guid_th = $wt["guid"];
				
				if (!in_array($wire_guid_th, $guids_thread)) {
					
					$guids_thread[] = $wire_guid_th;
					$owner_thread = get_entity($wt["owner_guid"]);
					$wt["owner"] = ws_pack_export_entity($owner_thread);
					$parent = thewire_get_parent($wire_guid_th);
					$wt["parent"] = ws_pack_export_entity($parent);
					
					if ($parent !== false) {
						$parent_owner = get_entity($wt["parent"]["owner_guid"]);
						$wt["parent_owner"] = ws_pack_export_entity($parent_owner);
					}
					$wires["entities"][$k] = $wt;
				} else {
					unset($wires["entities"][$k]);
				}
			}
			
			$result = new SuccessResult($wires);
		}
	
		if ($result === false) {
			$result = new ErrorResult(elgg_echo("ws_pack:users:register_for_push_notifications:error"));
		}
	}
	
	return $result;
}

function ws_pack_delete_wire($guid_user, $guid) {
	$result = false;

	$user = elgg_get_logged_in_user_entity();
	$api_application = ws_pack_get_current_api_application();
	
	if (!empty($user) && !empty($api_application)) {
		$thewire = get_entity($guid);
		if ($thewire->getSubtype() == "thewire" && $thewire->canEdit()) {
		
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
			$rowsaffected = $thewire->delete();
			if ($rowsaffected > 0) {
				$result = new SuccessResult(true);
			} else {
				$result = new ErrorResult(elgg_echo("ws_pack:users:register_for_push_notifications:error"));
			}
		} else {
			$result = new ErrorResult(elgg_echo("ws_pack:users:register_for_push_notifications:error"));
		}
	}
	
	return $result;
}
