<?php

$annotation = elgg_extract("annotation", $vars);

$icon = "";
$annotation_menu = "";
if ($annotation->canEdit()) {
	$annotation_menu = elgg_view_menu("annotation", array(
		"annotation" => $annotation,
		"sort_by" => "priority",
		"class" => "elgg-menu-hz float-alt"
	));
}

$settings_list = "";
if (elgg_is_admin_logged_in()) {
	if (($value = $annotation->value) && ($settings = json_decode($value, true))) {
		$settings_list = "<div id='ws-pack-annotation-" . $annotation->id . "' class='hidden plm'>";
		
		foreach ($settings as $key => $value) {
			$settings_list .= "<label>" . $key . ": </label>" . $value . "<br />";
		}
		
		$settings_list .= "</div>";
	}
}

$body = "<div class='mbn'>";
$body .= $annotation_menu;
$body .= ucfirst($annotation->name);
$body .= $settings_list;
$body .= "</div>";

echo elgg_view_image_block($icon, $body);
