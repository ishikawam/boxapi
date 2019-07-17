<?php

namespace Maengkom\Box;

trait BoxEvents {

	public function getEvents($option = null)
	{
		$url = $this->api_url . "/events";
		if ($option) {
			$url .= '?' . http_build_query($option, '', '\&');
		}
		return $this->get($url);
	}
}
