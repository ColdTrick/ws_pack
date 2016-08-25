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
	elgg_ws_expose_function(
		'languages.get_lang_file',
		'ws_pack_get_lang_file',
		[],
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

		$translations = [];

		//kinds of translations
		$fields = [
			'members',
			'search',
			'groups',
			'friends',
			'notifications',
			'messages',
			'messageboard',
			'likes',
			'invitefriends',
			'discussion',
			'profile',
			'user',
			'usersettings',
			'date',
			'email',
		];
		
		global $_ELGG;
		
		$elgg_translations = $_ELGG->translations['en'];
		$user_lang = get_current_language();
		if ($user_lang !== 'en') {
			if (array_key_exists($user_lang, $_ELGG->translations)) {
				$elgg_translations = array_merge($elgg_translations, $_ELGG->translations[$user_lang]);
			}
		}
	
		//load and iterate the language cached by the site
		foreach ($elgg_translations as $k => $v) {
			if (strpos($k, ':')) {
				$parts = explode(':', $k);
				$new_key = $parts[1];
				foreach ($fields as $field) {
					if ($parts[0] == $field) {
						$translations[$field][$new_key] = $v;
					}
				}
			} else {
				$translations['general'][$k] = $v;
			}
		}

		$translations = json_encode($translations);
		$result = new SuccessResult($translations);
	}
	
	if ($result === false) {
		$result = new ErrorResult(elgg_echo('ws_pack:error:notfound'));
	}

	return $result;
}
