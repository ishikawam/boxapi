<?php

namespace Maengkom\Box;

trait BoxEvents {

	public function getEvents($limit = 100)
	{
		$url = $this->api_url . "/events?limit=$limit";
		return $this->get($url);
	}
}
