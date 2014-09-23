<?php

$entity = elgg_extract("entity", $vars);
$full_view = (bool) elgg_extract("full_view", $vars, false);

// build the entity menu
$entity_menu = "";
if (!elgg_in_context("widgets")) {
	$entity_menu = elgg_view_menu("entity", array(
		"entity" => $entity,
		"handler" => "ws_pack",
		"sort_by" => "priority",
		"class" => "elgg-menu-hz"
	));
}

// entity icon
$entity_icon = elgg_view_entity_icon($entity, "small", $vars);

$subtitle = "";

// summary
$params = array(
	"entity" => $entity,
	"metadata" => $entity_menu,
	"title" => $entity->getTitle(),
	"subtext" => $subtitle,
	"content" => $entity->getDescription()
);

$params = $params + $vars;
$summary = elgg_view("object/elements/summary", $params);

// show the entity
if ($full_view) {
	// full view
	$body_links = array();
	$body_content = "";
	
	// show the api keys
	if ($api_keys = $entity->getApiKeys()) {
		$body_links[] = elgg_view("output/url", array("text" => elgg_echo("ws_pack:api:application:api_keys:link"), "href" => "#ws-pack-api-keys-" . $entity->getGUID(), "rel" => "toggle"));
		
		$body_content .= "<div class='hidden mvs' id='ws-pack-api-keys-" . $entity->getGUID() . "'>";
		$body_content .= "<label>" . elgg_echo("ws_pack:api:application:api_keys:key") . "</label>: " . $api_keys["api_key"] . "<br />";
		$body_content .= "<label>" . elgg_echo("ws_pack:api:application:api_keys:secret") . "</label>: " . $api_keys["secret"];
		$body_content .= "</div>";
	}
	
	if ($annotations = $entity->getPushNotificationServices(true)) {
		foreach ($annotations as $annotation) {
			if ($value = $annotation->value) {
				if ($value = json_decode($value, true)) {
					foreach ($value as $service_name => $settings) {
						if (!empty($settings)) {
							$body_links[] = elgg_view("output/url", array("text" => ucfirst($service_name), "href" => "#ws-pack-push-services-" . $annotation->id, "rel" => "toggle"));
							
							$body_content .= "<div class='hidden mvs' id='ws-pack-push-services-" . $annotation->id . "'>";
							
							foreach ($settings as $setting => $value) {
								$body_content .= "<label>" . $setting . "</label>: " . $value . "<br />";
							}
							
							$body_content .= elgg_view("output/confirmlink", array(
								"text" => elgg_echo("ws_pack:api:application:push_service:delete"),
								"confirm" => elgg_echo("deleteconfirm"),
								"href" => "action/ws_pack/push_service/delete?id=" . $annotation->id . "&guid=" . $entity->getGUID()
							));
							
							$body_content .= "</div>";
						}
					}
				}
			}
		}
	}
	
	$body = "<div>";
	$body .= implode(" | ", $body_links);
	$body .= "</div>";
	$body .= $body_content;
	
	echo elgg_view("object/elements/full", array(
		"icon" => $entity_icon,
		"summary" => $summary,
		"body" => $body
	));
} else {
	// listing view
	echo elgg_view_image_block($entity_icon, $summary);
}
