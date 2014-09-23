<?php

$options = array(
	"type" => "object",
	"subtype" => APIApplicationUserSetting::SUBTYPE,
	"limit" => false,
	"owner_guid" => elgg_get_page_owner_guid(),
	"full_view" => true,
	"pagination" => false
);

if (!($content = elgg_list_entities($options))) {
	$content = elgg_echo("ws_pack:usersettings:none");
}

echo $content;
