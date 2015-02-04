<?php
/**
 * Authentication webservices for ws_pack
 */
ws_pack_auth_expose_functions();

/**
 * Exposes the authentication functions
 * 
 * @return void
 */
function ws_pack_auth_expose_functions() {
	expose_function(
		"auth.get_api_keys",
		"ws_pack_auth_get_api_keys",
		array(
			"application_id" => array(
				"type" => "string",
				"required" => true
			),
			"title" => array(
				"type" => "string",
				"required" => true
			),
			"description" => array(
				"type" => "string",
				"required" => false,
				"default" => ""
			),
			"icon_url" => array(
				"type" => "string",
				"required" => false,
				"default" => ""
			),
			"application_info" => array(
				"type" => "array",
				"required" => false,
				"default" => array()
			)
		),
		elgg_echo("ws_pack:api:auth:get_api_keys"),
		"GET",
		false,
		false
	);
	
	// reregister login function to allow login by email
	unexpose_function("auth.gettoken");
	expose_function(
		"auth.gettoken",
		"ws_pack_auth_gettoken",
		array(
			"username" => array ("type" => "string"),
			"password" => array ("type" => "string"),
		),
		elgg_echo("auth.gettoken"),
		"POST",
		false,
		false
	);
	
}

/**
 * Get API keys for your application
 *
 * @param string $application_id   a unique id for your application
 * @param string $title            the title/name of your application
 * @param string $description      an optional description of your application
 * @param string $icon_url         an optional URL to the icon for your application
 * @param array  $application_info more information in a key => value array
 *
 * @return SuccessResult|ErrorResult
 */
function ws_pack_auth_get_api_keys($application_id, $title, $description = "", $icon_url = "", $application_info = array()) {
	$result = false;
	$application = false;
	
	if ($application = ws_pack_get_application_from_id($application_id)) {
		// we found an application, check the status
	} elseif ($application = ws_pack_create_application($application_id, $title, $description, $icon_url, $application_info)) {
		// an application was created, check the status
		if ($application === -1) {
			// no application was created, because this has been disabled
			$application = false;
			
			$result = new ErrorResult(elgg_echo("ws_pack:api:auth:get_api_keys:disabled"), WS_PACK_API_REGISTRATION_DISABLED);
		}
	}
	
	// check the application satus
	if (!empty($application)) {
		
		switch ($application->getStatusCode()) {
			case SuccessResult::$RESULT_SUCCESS:
				$api_keys = $application->getApiKeys();
				
				$result = new SuccessResult($api_keys);
				break;
			case ErrorResult::$RESULT_FAIL_APIKEY_DISABLED:
				
				$result = new ErrorResult(elgg_echo("ws_pack:api:application:status:disabled"), ErrorResult::$RESULT_FAIL_APIKEY_DISABLED);
				break;
			case ErrorResult::$RESULT_FAIL_APIKEY_INACTIVE:
				$result = new ErrorResult(elgg_echo("ws_pack:api:application:status:inactive"), ErrorResult::$RESULT_FAIL_APIKEY_INACTIVE);
				break;
			case APIApplication::STATE_PENDING:
				$result = new ErrorResult(elgg_echo("ws_pack:api:application:status:pending"), APIApplication::STATE_PENDING);
				break;
		}
	}

	if ($result === false) {
		$result = new ErrorResult(elgg_echo("APIException:ApiResultUnknown"));
	}
	
	return $result;
}

/**
 * Allow login by username/email and password
 *
 * @param string $username username
 * @param string $password password
 * 
 * @throws SecurityException
 * 
 * @return void|string
 */
function ws_pack_auth_gettoken($username, $password) {
	// check if username is an email address
	if (is_email_address($username)) {
		$users = get_user_by_email($username);
		
		// check if we have a unique user
		if (is_array($users) && (count($users) == 1)) {
			$username = $users[0]->username;
		}
	}
	
	// validate username and password
	if (true === elgg_authenticate($username, $password)) {
		$token = create_user_token($username);
		
		if ($token) {
			return $token;
		}
	}
	
	throw new SecurityException(elgg_echo("SecurityException:authenticationfailed"));
}
	