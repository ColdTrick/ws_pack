<?php

namespace ColdTrick\WsPack;

class Upgrade {
	
	/**
	 * Check the class assosiation
	 *
	 * @param string $event  the name of the event
	 * @param string $type   the type of the event
	 * @param mixed  $object supplied params
	 *
	 * @return void
	 */
	public static function checkClasses($event, $type, $object) {
		
		if (get_subtype_id('object', \APIApplication::SUBTYPE)) {
			update_subtype('object', \APIApplication::SUBTYPE, 'APIApplication');
		} else {
			add_subtype('object', \APIApplication::SUBTYPE, 'APIApplication');
		}
		
		if (get_subtype_id('object', \APIApplicationUserSetting::SUBTYPE)) {
			update_subtype('object', \APIApplicationUserSetting::SUBTYPE, 'APIApplicationUserSetting');
		} else {
			add_subtype('object', \APIApplicationUserSetting::SUBTYPE, 'APIApplicationUserSetting');
		}
	}
}
