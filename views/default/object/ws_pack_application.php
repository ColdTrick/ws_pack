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
	
	// show the entity
	if ($full_view) {
		echo "ToDo";
	} else {
		$subtitle = "";
		
		// listing view
		$params = array(
			"entity" => $entity,
			"metadata" => $entity_menu,
			"title" => $entity->getTitle(),
			"subtext" => $subtitle,
			"content" => elgg_get_excerpt($entity->getDescription())
		);
		
		$params = $params + $vars;
		$summary = elgg_view("object/elements/summary", $params);
		
		echo elgg_view_image_block($entity_icon, $summary);
	}