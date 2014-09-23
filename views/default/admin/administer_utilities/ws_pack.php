<?php

// show a tabbed menu
echo elgg_view_menu("ws_pack:applications", array(
	"sort_by" => "priority",
	"class" => "elgg-tabs elgg-htabs"
));

// show content
$tab = get_input("tab");

$content = "";
$options = array(
	"type" => "object",
	"subtype" => APIApplication::SUBTYPE,
	"limit" => false,
	"pagination" => false,
	"full_view" => true
);

switch($tab){
	case "pending":
		$dbprefix = elgg_get_config("dbprefix");
		$api_user_id = elgg_get_metastring_id("api_user_id");
		
		$options["wheres"] = array("NOT EXISTS (
			SELECT 1
			FROM " . $dbprefix . "metadata md
			WHERE md.entity_guid = e.guid
				AND md.name_id = " . $api_user_id . ")"
		);
		
		$content = elgg_list_entities($options);
		
		break;
	case "inactive":
		$options["metadata_name"] = "api_user_id";
		
		if ($entities = elgg_get_entities_from_metadata($options)) {
			$inactive = array();
			
			foreach ($entities as $entity) {
				if (!$entity->getApiKeys()) {
					$inactive[] = $entity;
				}
			}
			
			unset($entities);
			
			$content = elgg_view_entity_list($inactive, $options);
		}
		break;
	case "disabled":
		$hidden = access_get_show_hidden_status();
		access_show_hidden_entities(true);
		
		$options["wheres"] = array("e.enabled = 'no'");
		
		$content = elgg_list_entities($options);
		
		access_show_hidden_entities($hidden);
		break;
	case "active":
	default:
		$options["metadata_name"] = "api_user_id";
			
		if ($entities = elgg_get_entities_from_metadata($options)) {
			$active = array();
		
			foreach ($entities as $entity) {
				if ($entity->getApiKeys()) {
					$active[] = $entity;
				}
			}
		
			unset($entities);
		
			$content = elgg_view_entity_list($active, $options);
		}
		break;
}

if (!empty($content)) {
	echo $content;
} else {
	echo elgg_echo("notfound");
}

// explain the different status
echo elgg_view("output/longtext", array("value" => elgg_echo("ws_pack:admin:listing:states:legend"), "class" => "mtm"));
