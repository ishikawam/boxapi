<?php

namespace Maengkom\Box;

trait BoxUser {

	public function getMe()
	{
		$url = $this->api_url . "/users/me";
		return $this->get($url);
	}

	public function getUser($user_id) {
		$url = $this->api_url . "/users/$user_id";
		return $this->get($url);
	}
}
