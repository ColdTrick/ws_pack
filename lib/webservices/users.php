<?php

	ws_pack_users_expose_functions();
	
	function ws_pack_users_expose_functions() {
		expose_function(
			"users.get_logged_in_user", 
			"ws_pack_users_get_logged_in_user",
			array(),
			elgg_echo("ws_pack:api:users:get_logged_in_user"),
			"GET",
			true,
			true
		);
	}
	
	function ws_pack_users_get_logged_in_user() {
		$result = false;
		
		if ($user = elgg_get_logged_in_user_entity()) {
			if ($export = ws_pack_export_entity($user)) {
				$result = new SuccessResult($export[0]);
			}
		}
		
		if ($result === false) {
			$result = new ErrorResult(elgg_echo("notfound"));
		}
		
		return $result;
	}