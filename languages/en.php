<?php

return array(
	// general
	'item:object:ws_pack_application' => "Webservice application",
	'item:object:ws_pack_application_user_setting' => "Webservice application user settings",
	
	'ws_pack:deactivate' => "Deactivate",
	'ws_pack:activate' => "Activate",
	
	// admin menus
	'admin:administer_utilities:ws_pack' => "Webservice applications",
	'ws_pack:menu:admin:applications:active' => "Active",
	'ws_pack:menu:admin:applications:pending' => "Pending approval",
	'ws_pack:menu:admin:applications:inactive' => "Inactive",
	'ws_pack:menu:admin:applications:disabled' => "Disabled",
	
	// admin listing
	'ws_pack:admin:listing:states:legend' => "<b>Status legend:</b>
Active: These applications are allowed to use the API.
Pending: These applications have requested access to the API, you need to approve or reject this request.
Inactive: These applications have been (temporaraly) disabled.
Disabled: These applications have been denied access to the API.",
	
	// plugin settings
	'ws_pack:settings:allow_application_registration' => "Allow new applications to register for API usage",

	// user settings
	'ws_pack:usersettings:none' => "No webservices were found",
	'ws_pack:usersettings:push_notification_services' => "Push notification services",
	'ws_pack:annotation:push_notification_service:delete_confirm' => "Are you sure you wish to delete this Push Notification Service, you will no longer get push notifications on you mobile device",
	'ws_pack:annotation:push_notification_service:settings' => "Show settings",
		
	// api
	'ws_pack:api:application:status:pending' => "The application is pending approval",
	'ws_pack:api:application:status:disabled' => "The application is disabled",
	'ws_pack:api:application:status:inactive' => "The application is temporaraly disabled",
	
	'ws_pack:api:application:api_keys:link' => "Elgg API keys",
	'ws_pack:api:application:api_keys:key' => "API key",
	'ws_pack:api:application:api_keys:secret' => "Secret",
	'ws_pack:api:application:push_service:delete' => "Delete the Push Notification Service settings",
	
	// auth
	'ws_pack:api:auth:get_api_keys' => "Get API keys for your application",
	'ws_pack:api:auth:get_api_keys:disabled' => "Registration of new API applications has been disabled by the system administrator",
	
	// groups
	'ws_pack:api:groups:get' => "Get a listing of groups. With to filter parameter you can tell which groups to get, currently only all is supported.",
	'ws_pack:api:groups:member_of' => "Get all the groups of the current user, or the supplied user",
	
	// river
	'ws_pack:api:river:get' => "Get a listing of the most recent river items. These can be filtered by supplying a filter: all, for all river items. mine for all my river activities. friends: for all river activities from my friends. groups: for all river activities of my groups, or of the supplied groups (using the guids argument).",
	
	//users
	'ws_pack:api:users:get_logged_in_user' => "Get the currently logged in user.",
	'ws_pack:api:users:register_for_push_notifications' => "Register the current user for push notifications at the provided Push Service Provider. Currently only Appcelerator is supported.",
	
	'ws_pack:users:register_for_push_notifications:error' => "An unknown error occurred while registering for push notifications",
	'ws_pack:users:unregister_from_push_notifications:error' => "An unknown error occurred while unregistering from push notifications",
	
	'ws_pack:user_settings:error:notfound' => "The user settings could not be found for the current user, please try again later",
	
	'ws_pack:push_notifications:error:unsupported_service' => "The provided Push Notification Service %s is not support at this time.",
	
	// system
	'ws_pack:api:system:api:register_push_notification_service' => "Register a Push Notification Service to the current API Application. Currently only appcelerator is supported.",
	'ws_pack:api:system:api:unregister_push_notification_service' => "Unregister a Push Notification Service from the current API Application. Currently only appcelerator is supported.",
	
	'ws_pack:system:api:register_push_notification_service:error' => "An unknown error occurred while registering the push notification service",
	
	'ws_pack:system:api:unregister_push_notification_service:error' => "An unknown error occurred while unregistering the push notification service",
	
	// actions
	'ws_pack:error:notfound' => "Error connecting to the web service",
	
	'ws_pack:action:application:activate:success' => "The application %s has been activated",
	'ws_pack:action:application:activate:error' => "An unknown error occured while activating %s",
	
	'ws_pack:action:application:deactivate:success' => "The application %s has been deactivated",
	'ws_pack:action:application:deactivate:error' => "An unknown error occured while deactivating %s",
	
	'ws_pack:action:application:disable:success' => "The application %s has been disabled",
	'ws_pack:action:application:disable:error' => "An unknown error occured while disabling %s",
	
	'ws_pack:action:push_service:delete:error' => "An unknown error occured while deleting the Push Notification Service settings",
	'ws_pack:action:push_service:delete:success' => "The Push Notification Service settings where deleted successfully",

	'ws_pack:action:push_service:delete_user:error:id' => "Please provide a valid ID in order to delete the settings",
	'ws_pack:action:push_service:delete_user:error:can_edit' => "You're not allowed to edit these settings",
	'ws_pack:action:push_service:delete_user:error:delete' => "An unknown error occured while deleting the Push Notification settings",
	'ws_pack:action:push_service:delete_user:success' => "The Push Notification settings have successfully been removed",
);
