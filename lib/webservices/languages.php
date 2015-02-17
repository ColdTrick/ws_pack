<?php
/**
 * Languages webservices for ws_pack
 */
ws_pack_languages_expose_functions();

/**
 * Exposes the languages functions
 *
 * @return void
 */
function ws_pack_languages_expose_functions() {
	expose_function(
		"languages.get_lang_file", 
		"ws_pack_get_lang_file", 
		array (),
		'', 
		'GET', 
		true, 
		true
	);
}

function ws_pack_get_lang_file() {
	$result = false;

	$user = elgg_get_logged_in_user_entity();
	$api_application = ws_pack_get_current_api_application();
	
	if (!empty($user) && !empty($api_application)) {

		$translations = array();

		//kinds of translations
		$fields = array (
			"members",
			"search",
			"groups",
			"friends",
			"notifications",
			"messages",
			"messageboard",
			"likes",
			"invitefriends",
			"discussion",
			"profile",
			"user",
			"usersettings",
			"date",
			"email" 
		);

		//load and iterate the language cached by the site
		foreach ($GLOBALS["CONFIG"]->translations as $trans) {
			foreach ($trans as $k => $v) { 
				if (strpos($k, ":")) {
					$parts = explode(':', $k);
					$new_key = $parts[1];
					foreach ($fields as $field) {
						if ($parts[0] == $field) {
							$translations[$field][$new_key] = $v; 
						}
					}
				} else {
					$translations["general"][$k] = $v; 
				}
			}
		}

		$translations = json_encode($translations);
		$result = new SuccessResult($translations);	    
	}
	
	if ($result === false) {
		$result = new ErrorResult(elgg_echo("ws_pack:error:notfound"));
	}

	return $result;
}
