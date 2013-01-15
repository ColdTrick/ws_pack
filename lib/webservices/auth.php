<?php

	ws_pack_auth_expose_functions();
	
	function ws_pack_auth_expose_functions(){
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
		
	}
	
	/**
	 * Get API keys for your application,
	 * 
	 * @param string $application_id => a unique id for your application
	 * @param string $title => the title/name of your application
	 * @param string $description => an optional description of your application
	 * @param string $icon_url => an optional URL to the icon for your application
	 * @param array $application_info => more information in a key => value array
	 * 
	 * @return request status || API keys
	 */
	function ws_pack_auth_get_api_keys($application_id, $title, $description = "", $icon_url = "", $application_info = array()){
		$result = false;
		$application = false;
		
		if($application = ws_pack_get_application_from_id($application_id)) {
			// we found an application, check the status
		} elseif($application = ws_pack_create_application($application_id, $title, $description, $icon_url, $application_info)) {
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
	