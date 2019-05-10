<?php

namespace Maengkom\Box;

trait BoxUser {

	public function getMe()
	{
		$url = $this->api_url . "/users/me";
		return $this->get($url);
	}
}
