<?php 

$id = (int) get_input("id");
$guid = (int) get_input("guid");

if (!empty($id) && !empty($guid)) {
	$annotation = elgg_get_annotation_from_id($id);
	$entity = get_entity($guid);
	
	if (!empty($annotation) && !empty($entity)) {
		if (($annotation->entity_guid == $entity->getGUID()) && elgg_instanceof($entity, "object", APIApplication::SUBTYPE)) {
			if ($annotation->delete()) {
				system_message(elgg_echo("ws_pack:action:push_service:delete:success"));
			} else {
				register_error(elgg_echo("ws_pack:action:push_service:delete:error"));
			}
		} else {
			register_error(elgg_echo("ClassException:ClassnameNotClass", array($guid, elgg_echo("item:object:" . APIApplication::SUBTYPE))));
		}
	} else {
		register_error(elgg_echo("InvalidParameterException:NoEntityFound"));
	}
} else {
	register_error(elgg_echo("InvalidParameterException:MissingParameter"));
}

forward(REFERER);
