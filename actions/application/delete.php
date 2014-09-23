<?php

$guid = (int) get_input("guid");

// make sure we can see every thing
$hidden = access_get_show_hidden_status();
access_show_hidden_entities(true);

if (!empty($guid)) {
	if ($entity = get_entity($guid)) {
		if (elgg_instanceof($entity, "object", APIApplication::SUBTYPE)) {
			$title = $entity->getTitle();
			
			if ($entity->delete()) {
				system_message(elgg_echo("entity:delete:success", array($title)));
			} else {
				register_error(elgg_echo("entity:delete:fail", array($title)));
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

access_show_hidden_entities($hidden);

forward(REFERER);
