<?php

$guid = (int) get_input("guid");

if (!empty($guid)) {
	if ($entity = get_entity($guid)) {
		if (elgg_instanceof($entity, "object", APIApplication::SUBTYPE)) {
			$title = $entity->getTitle();
			
			if ($entity->deactivate()) {
				system_message(elgg_echo("ws_pack:action:application:deactivate:success", array($title)));
			} else {
				register_error(elgg_echo("ws_pack:action:application:deactivate:error", array($title)));
			}
		} else {
			register_error(elgg_echo("ClassException:ClassnameNotClass", array(elgg_echo("item:object:ws_pack_application"))));
		}
	} else {
		register_error(elgg_echo("InvalidParameterException:GUIDNotFound", array($guid)));
	}
} else {
	register_error(elgg_echo("InvalidParameterException:MissingParameter"));
}

forward(REFERER);
