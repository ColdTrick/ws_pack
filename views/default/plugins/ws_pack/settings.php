<?php

$plugin = elgg_extract('entity', $vars);

$noyes_options = [
	'no' => elgg_echo('option:no'),
	'yes' => elgg_echo('option:yes'),
];

echo elgg_view_input('select', [
	'label' => elgg_echo('ws_pack:settings:allow_application_registration'),
	'name' => 'params[allow_registration]',
	'options_values' => $noyes_options,
	'value' => $plugin->allow_registration,
]);

// IonicCloud settings
$ionic = '';

$ionic .= elgg_view_input('text', [
	'label' => elgg_echo('ws_pack:settings:ionic:api_token'),
	'help' => elgg_echo('ws_pack:settings:ionic:api_token:help'),
	'name' => 'params[ionic_cloud_api_token]',
	'value' => $plugin->ionic_cloud_api_token,
]);

echo elgg_view_module('inline', elgg_echo('ws_pack:settings:ionic'), $ionic);
