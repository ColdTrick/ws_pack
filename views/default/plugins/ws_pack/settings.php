<?php

$plugin = elgg_extract("entity", $vars);

$noyes_options = array(
	"no" => elgg_echo("option:no"),
	"yes" => elgg_echo("option:yes")
);

echo "<div>";
echo elgg_echo("ws_pack:settings:allow_application_registration");
echo elgg_view("input/dropdown", array("name" => "params[allow_registration]", "options_values" => $noyes_options, "value" => $plugin->allow_registration, "class" => "mls"));
echo "</div>";
