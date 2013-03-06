<?php

	function ws_pack_applications_menu_hook_handler($hook, $type, $returnvalue, $params){
		$result = $returnvalue;
		
		if (elgg_in_context("admin")) {
			$result[] = ElggMenuItem::factory(array(
				"name" => "active",
				"text" => elgg_echo("ws_pack:menu:admin:applications:active"),
				"href" => "/admin/administer_utilities/ws_pack",
				"priority" => 100
			));
			
			$result[] = ElggMenuItem::factory(array(
				"name" => "pending",
				"text" => elgg_echo("ws_pack:menu:admin:applications:pending"),
				"href" => "/admin/administer_utilities/ws_pack?tab=pending",
				"priority" => 200
			));
			
			$result[] = ElggMenuItem::factory(array(
				"name" => "inactive",
				"text" => elgg_echo("ws_pack:menu:admin:applications:inactive"),
				"href" => "/admin/administer_utilities/ws_pack?tab=inactive",
				"priority" => 300
			));
			
			$result[] = ElggMenuItem::factory(array(
				"name" => "disabled",
				"text" => elgg_echo("ws_pack:menu:admin:applications:disabled"),
				"href" => "/admin/administer_utilities/ws_pack?tab=disabled",
				"priority" => 400
			));
			
		}
		
		return $result;
	}
	
	function ws_pack_entity_menu_hook_handler($hook, $type, $returnvalue, $params){
		$result = $returnvalue;
		
		if (($entity = elgg_extract("entity", $params)) && elgg_instanceof($entity, "object", APIApplication::SUBTYPE)) {
			$result = array();
			
			$result[] = ElggMenuItem::factory(array(
				"name" => "delete",
				"text" => elgg_view_icon("delete"),
				"title" => elgg_echo("delete:this"),
				"href" => "action/ws_pack/application/delete?guid=" . $entity->getGUID(),
				"confirm" => elgg_echo("deleteconfirm"),
				"priority" => 300,
			));
			
			switch ($entity->getStatusCode()) {
				case SuccessResult::$RESULT_SUCCESS:
					// active
					$result[] = ElggMenuItem::factory(array(
						"name" => "status",
						"text" => elgg_echo("active"),
						"href" => false,
						"priority" => 50
					));
					
					$result[] = ElggMenuItem::factory(array(
						"name" => "deactivate",
						"text" => elgg_echo("ws_pack:deactivate"),
						"href" => "/action/ws_pack/application/deactivate?guid=" . $entity->getGUID(),
						"is_action" => true,
						"priority" => 100
					));
					$result[] = ElggMenuItem::factory(array(
						"name" => "disable",
						"text" => elgg_echo("disable"),
						"href" => "/action/ws_pack/application/disable?guid=" . $entity->getGUID(),
						"is_action" => true,
						"priority" => 200
					));
					
					break;
				case APIApplication::STATE_PENDING:
					// pending
					$result[] = ElggMenuItem::factory(array(
						"name" => "status",
						"text" => elgg_echo("ws_pack:api:application:status:pending"),
						"href" => false,
						"priority" => 50
					));
						
					$result[] = ElggMenuItem::factory(array(
						"name" => "activate",
						"text" => elgg_echo("ws_pack:activate"),
						"href" => "/action/ws_pack/application/activate?guid=" . $entity->getGUID(),
						"is_action" => true,
						"priority" => 100
					));
					$result[] = ElggMenuItem::factory(array(
						"name" => "disable",
						"text" => elgg_echo("disable"),
						"href" => "/action/ws_pack/application/disable?guid=" . $entity->getGUID(),
						"is_action" => true,
						"priority" => 200
					));
					
					break;
				case ErrorResult::$RESULT_FAIL_APIKEY_DISABLED:
					// disabled
					$result[] = ElggMenuItem::factory(array(
						"name" => "status",
						"text" => elgg_echo("ws_pack:api:application:status:disabled"),
						"href" => false,
						"priority" => 50
					));
					
					$result[] = ElggMenuItem::factory(array(
						"name" => "activate",
						"text" => elgg_echo("ws_pack:activate"),
						"href" => "/action/ws_pack/application/activate?guid=" . $entity->getGUID(),
						"is_action" => true,
						"priority" => 100
					));
					
					break;
				case ErrorResult::$RESULT_FAIL_APIKEY_INACTIVE:
					// inactive
					$result[] = ElggMenuItem::factory(array(
						"name" => "status",
						"text" => elgg_echo("ws_pack:api:application:status:inactive"),
						"href" => false,
						"priority" => 50
					));
					
					$result[] = ElggMenuItem::factory(array(
						"name" => "activate",
						"text" => elgg_echo("ws_pack:activate"),
						"href" => "/action/ws_pack/application/activate?guid=" . $entity->getGUID(),
						"is_action" => true,
						"priority" => 100
					));
					
					$result[] = ElggMenuItem::factory(array(
						"name" => "disable",
						"text" => elgg_echo("disable"),
						"href" => "/action/ws_pack/application/disable?guid=" . $entity->getGUID(),
						"is_action" => true,
						"priority" => 200
					));
					
					break;
			}
			
		}
		
		return $result;
	}
	
	/**
	* The REST API is being called,
	* check which library is needed
	*
	*/
	function ws_pack_rest_init_hook_handler($hook, $type, $returnvalue, $params) {
		
		// get the called method
		$method = get_input("method");
		
		if ($method === "system.api.list") {
			// load all, so the api list is populated with all calls
			elgg_load_library("ws_pack.auth");
			elgg_load_library("ws_pack.groups");
			elgg_load_library("ws_pack.river");
			elgg_load_library("ws_pack.users");
			elgg_load_library("ws_pack.system");
		} else {
			list($library, $dummy) = explode(".", $method);
		
			try {
				// try to match this to one of our libraries
				elgg_load_library("ws_pack." . $library);
			} catch (Exception $e){
				// do nothing
			}
		}
	}
	
	function ws_pack_api_key_use_hook_handler($hook, $type, $returnvalue, $params) {
		
		if (!empty($params) && is_string($params)) {
			$site = elgg_get_site_entity();
			
			// get the current api user
			if ($api_user = get_api_user($site->getGUID(), $params)) {
				// check if we're using our API application
				if ($api_application = ws_pack_get_application_from_api_user_id($api_user->id)) {
					// store the API application for later use 
					ws_pack_set_current_api_application($api_application);
				}
			}
		}
	}