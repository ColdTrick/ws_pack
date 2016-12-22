<?php

namespace ColdTrick\WsPack;

class SiteNotification {
	
	/**
	 * Send a push notification about a new site notification
	 *
	 * @param string      $event  the name of the event
	 * @param string      $type   the type of the event
	 * @param \ElggObject $object supplied object
	 *
	 * @return void
	 */
	public static function create($event, $type, $object) {
		
		if (!($object instanceof \SiteNotification)) {
			return;
		}
		
		$applications = ws_pack_get_applications();
		if (empty($applications)) {
			return;
		}
		
		foreach ($applications as $application) {
			$application->sendPushNotification($object->description, $object->getOwnerGUID());
		}
	}
}
