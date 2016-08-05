<?php
/**
 * Called when the plugin is deactivated
 */

// remove class handlers for subtypes
update_subtype('object', APIApplication::SUBTYPE);
update_subtype('object', APIApplicationUserSetting::SUBTYPE);
