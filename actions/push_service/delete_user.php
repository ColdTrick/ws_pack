<?php

$id = (int) get_input("id");

if ($annotation = elgg_get_annotation_from_id($id)) {
	if (($entity = $annotation->getEntity()) && elgg_instanceof($entity, "object", APIApplicationUserSetting::SUBTYPE)) {
		if ($annotation->canEdit() && $entity->canEdit()) {
			if ($annotation->delete()) {
				system_message(elgg_echo("ws_pack:action:push_service:delete_user:success"));
			} else {
				register_error(elgg_echo("ws_pack:action:push_service:delete_user:error:delete"));
			}
		} else {
			register_error(elgg_echo("ws_pack:action:push_service:delete_user:error:can_edit"));
		}
	} else {
		register_error(elgg_echo("InvalidParameterException:NoEntityFound"));
	}
} else {
	register_error(elgg_echo("ws_pack:action:push_service:delete_user:error:id"));
}

forward(REFERER);
