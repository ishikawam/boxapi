<?php

namespace Maengkom\Box;

class BoxStandardUser {

	use BoxContent;

    /**
     * Config
     *
     * @var array
     */
    public $config = array(
        'su_client_id' 		=> '',
        'su_client_secret'	=> '',
        'redirect_uri'		=> '',
    );

	private $token	= array();

	protected $refresh_token	= '';
	protected $access_token	= '';
	protected $auth_header	= '';

	// These urls below used for Box Content API
	protected $token_url	 	= 'https://api.box.com/oauth2/token';
	protected $api_url 		= 'https://api.box.com/2.0';
	protected $upload_url 	= 'https://upload.box.com/api/2.0';
	protected $authorize_url 	= 'https://app.box.com/api/oauth2/authorize';

	// This url below used for get App User access_token in JWT
	protected $audience_url 	= 'https://api.box.com/oauth2/token';

	/**
	 * @param array $config
	 */
	public function __construct(array $config = array())
	{
		$this->configure($config);
	}

	/**
    * Overrides configuration settings
    *
    * @param array $config
    */
	protected function configure(array $config = array())
    {
        $this->config = array_replace($this->config, $config);
        return $this;
    }

	/**
	 * box oauth url
	 */
	public function getLoginUrl()
	{
		return $this->authorize_url . '?' . http_build_query(array(
			'response_type'	=> 'code',
			'client_id'		=> $this->config['su_client_id'],
			'redirect_uri'	=> $this->config['redirect_uri'],
		));
	}

	/*
	 * Second step for authentication [Gets the access_token and the refresh_token]
	 * @param string $code
	 * @return array
	 */
	public function getToken($code = null)
	{
		if (! empty($this->refresh_token)) {
			$querystring = http_build_query(array(
				'grant_type' 	=> 'refresh_token',
				'refresh_token' => $this->refresh_token,
				'client_id' 	=> $this->config['su_client_id'],
				'client_secret' => $this->config['su_client_secret']));
		} else {
			$querystring = http_build_query(array(
				'grant_type' 	=> 'authorization_code',
				'code' 			=> $code,
				'client_id' 	=> $this->config['su_client_id'],
				'client_secret' => $this->config['su_client_secret']));
		}

		return json_decode(shell_exec(sprintf('curl %s -d "%s" -X POST', $this->token_url, $querystring)), true);
	}

	/* Saves the token */
	/**
	 * @param array $token
	 * @return bool
	 */
	public function setToken(array $token)
	{
		if (isset($token['error'])) {
			$this->error = $token['error_description'];
			return false;
		}

		$this->token = $token;

		$this->auth_header = sprintf('-H "Authorization: Bearer %s"', $this->access_token);

		return true;
	}

	/**
	 * Loads the token
	 * tokenからaccess_token, refresh_tokenをセットする。
	 * もし期限切れていたらrefresh_tokenからtokenを再取得する。
	 * @return bool
	 */
	public function loadToken()
	{
		$token = $this->token;
		if (empty($token)) {
			return false;
		}

		if (isset($token['error'])) {
			$this->error = $token['error_description'];
			return false;
		}

		// ここで判定すべきじゃない。自前timestamp撤廃したい。
		if ($this->expired($token['expires_in'], $token['timestamp'])) {
			$this->refresh_token = $token['refresh_token'];
			$token = $this->getToken();
			if ($this->setToken($token)) {
				$this->refresh_token = $token['refresh_token'];
				$this->access_token = $token['access_token'];
				$this->token = $token;
				return true;
			}
			return false;
		}

		$this->refresh_token = $token['refresh_token'];
		$this->access_token = $token['access_token'];
		return true;
	}

	/**
	 * @return bool
	 */
	protected function expired($expires_in, $timestamp)
	{
		$ctimestamp = time();
		if (($ctimestamp - $timestamp) >= $expires_in) {
			return true;
		} else {
			return false;
		}
	}

}
