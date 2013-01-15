<?php

	$english = array(
		// general
		'item:object:ws_pack_application' => "Webservice application",
		
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
Disabled: These applications have been denied access to teh API.",
		
		// plugin settings
		'ws_pack:settings:allow_application_registration' => "Allow new applications to register for API usage",

		// api
		'ws_pack:api:application:status:pending' => "The application is pending approval",
		'ws_pack:api:application:status:disabled' => "The application is disabled",
		'ws_pack:api:application:status:inactive' => "The application is temporaraly disabled",
		
		// auth
		'ws_pack:api:auth:get_api_keys' => "Get API keys for your application",
		'ws_pack:api:auth:get_api_keys:disabled' => "Registration of new API applications has been disabled by the system administrator",
		
		// actions
		'ws_pack:action:application:activate:success' => "The application %s has been activated",
		'ws_pack:action:application:activate:error' => "An unknown error occured while activating %s",
		
		'ws_pack:action:application:deactivate:success' => "The application %s has been deactivated",
		'ws_pack:action:application:deactivate:error' => "An unknown error occured while deactivating %s",
		
		'ws_pack:action:application:disable:success' => "The application %s has been disabled",
		'ws_pack:action:application:disable:error' => "An unknown error occured while disabling %s",
		
		'' => "",
	);
	
	add_translation("en", $english);