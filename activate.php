<?php
/**
 * Called when the plugin is activated
 */

// set class handlers
if (get_subtype_id('object', APIApplication::SUBTYPE)) {
	update_subtype('object', APIApplication::SUBTYPE, 'APIApplication');
} else {
	add_subtype('object', APIApplication::SUBTYPE, 'APIApplication');
}

if (get_subtype_id('object', APIApplicationUserSetting::SUBTYPE)) {
	update_subtype('object', APIApplicationUserSetting::SUBTYPE, 'APIApplicationUserSetting');
} else {
	add_subtype('object', APIApplicationUserSetting::SUBTYPE, 'APIApplicationUserSetting');
}