<?php

$guid = (int) get_input("guid");

if (!empty($guid)) {
	// show hidden (disabled) entities
	$hidden = access_get_show_hidden_status();
	access_show_hidden_entities(true);
	
	if ($entity = get_entity($guid)) {
		if (elgg_instanceof($entity, "object", APIApplication::SUBTYPE)) {
			$title = $entity->getTitle();
			
			if ($entity->activate()) {
				system_message(elgg_echo("ws_pack:action:application:activate:success", array($title)));
			} else {
				register_error(elgg_echo("ws_pack:action:application:activate:error", array($title)));
			}
		} else {
			register_error(elgg_echo("ClassException:ClassnameNotClass", array(elgg_echo("item:object:ws_pack_application"))));
		}
	} else {
		register_error(elgg_echo("InvalidParameterException:GUIDNotFound", array($guid)));
	}
	
	// restore hidden settings
	access_show_hidden_entities($hidden);
	
} else {
	register_error(elgg_echo("InvalidParameterException:MissingParameter"));
}

forward(REFERER);
