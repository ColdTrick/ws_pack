<?php

	ws_pack_search_expose_functions();
	
	function ws_pack_search_expose_functions() {
		expose_function(
			"search.query", 
			"ws_pack_search",
			array(
				"query" => array(
					"type" => "string",
					"required" => true
				)
			),
			elgg_echo("ws_pack:api:system:api:register_push_notification_service"),
			"GET",
			true,
			false
		);
	}

	function ws_pack_search($query) {

		$return_results = array();

		$current_params = array(
			'query' => $query,
			'offset' => 0,
			'limit' => 20,
		);

		$current_params['search_type'] = 'entities';		

		$types = get_registered_entity_types();
		$custom_types = elgg_trigger_plugin_hook('search_types', 'get_types', $current_params, array());

		foreach ($types as $type => $subtypes) {

			if (is_array($subtypes) && count($subtypes)) {			
				foreach ($subtypes as $subtype) {			
					$current_params['subtype'] = $subtype;
					$current_params['type'] = $type;

					$results = elgg_trigger_plugin_hook('search', "$type:$subtype", $current_params, NULL);

					if ($results === FALSE) {
						// someone is saying not to display these types in searches.
						continue;
					} elseif (is_array($results) && !count($results)) {
						// no results, but results searched in hook.
					} elseif (!$results) {
						// no results and not hooked.  use default type search.
						// don't change the params here, since it's really a different subtype.
						// Will be passed to elgg_get_entities().
						$results = elgg_trigger_plugin_hook('search', $type, $current_params, array());
					}

					if (is_array($results['entities']) && $results['count']) {
						$return_results = array_merge($return_results, $results['entities']);
					}
				}
			}

			// pull in default type entities with no subtypes
			$current_params['type'] = $type;
			$current_params['subtype'] = ELGG_ENTITIES_NO_VALUE;

			$results = elgg_trigger_plugin_hook('search', $type, $current_params, array());
			if ($results === FALSE) {
				// someone is saying not to display these types in searches.
				continue;
			}

			if (is_array($results['entities']) && $results['count']) {
				$return_results = array_merge($return_results, $results['entities']);
			}

			$access_ids = array(ACCESS_PUBLIC, ACCESS_LOGGED_IN);
			if ($site = elgg_get_site_entity()) {
				if ($subsite_acl = $site->getPrivateSetting("subsite_acl")) {
					$access_ids[] = $subsite_acl;
				}
			}

			foreach($return_results as $key => $result) {
				if (!in_array($result->access_id, $access_ids)) {
					unset($return_results[$key]);
				}
			}
		}
		
		$result = array();
		$result['total'] = count($return_results);
		if ($result['total'] > 0) {
			$result['results'] = ws_pack_export_entities($return_results);
		} else {
			$result['results'] = array();
		}

		return $result;
	}