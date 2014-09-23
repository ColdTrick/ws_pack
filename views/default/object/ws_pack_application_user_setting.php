<?php

$entity = elgg_extract("entity", $vars);
$full_view = elgg_extract("full_view", $vars, false);

// the api application of which this is the user settings object
$api_application = $entity->getContainerEntity();
$owner = $entity->getOwnerEntity();

if ($full_view) {
	// display information about the api application
	$summary = elgg_view_entity($api_application, array("full_view" => false));
	
	// list all the push notification services
	$body = "";
	if ($pns = $api_application->getPushNotificationServices()) {
		$pns_names = array_keys($pns);
		
		$pns_options = array(
			"guid" => $entity->getGUID(),
			"annotation_names" => $pns_names,
			"limit" => false,
			"owner_guid" => $owner->getGUID(),
			"pagination" => false
		);
		
		$body .= elgg_view_module("pns", elgg_echo("ws_pack:usersettings:push_notification_services"), elgg_list_annotations($pns_options));
	}
	
	echo elgg_view("object/elements/full", array(
		"summary" => $summary,
		"body" => $body
	));
} else {
	// listing view
	$owner_icon = elgg_view_entity_icon($owner, "small");
	$owner_link = elgg_view("output/url", array("text" => $owner->name, "href" => $owner->getURL(), "is_trusted" => true));
	
	$subtitle = elgg_echo("entity:default:strapline", array(elgg_get_friendly_time($entity->time_created), $owner_link));
	
	$params = array(
		"entity" => $entity,
		"metadata" => "", // no entity menu
		"title" => $api_application->getTitle(),
		"subtitle" => $subtitle,
		"content" => ""
	);
	$params = $params + $vars;
	$summary = elgg_view("object/elements/summary", $params);
	
	echo elgg_view_image_block($owner_icon, $summary);
}
