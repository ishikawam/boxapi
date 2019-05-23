<?php

namespace Maengkom\Box;

class BoxStandardUser {

	// Traits
	use BoxContent;
	use BoxUser;
	use BoxEvents;

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

	private $token	= array();  // @todo; errorの場合には記録しないようにする＝記録時にだけerrorのチェックをする

	protected $auth_header	= '';

	// These urls below used for Box Content API
	protected $token_url	 	= 'https://api.box.com/oauth2/token';
	protected $api_url 		= 'https://api.box.com/2.0';
	protected $upload_url 	= 'https://upload.box.com/api/2.0';
	protected $authorize_url 	= 'https://app.box.com/api/oauth2/authorize';

	// This url below used for get App User access_token in JWT
	protected $audience_url 	= 'https://api.box.com/oauth2/token';

	protected $is_refreshed = false;

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
	private function getTokenByRefreshToken($refreshToken)
	{
		$querystring = http_build_query(array(
			'grant_type' 	=> 'refresh_token',
			'refresh_token' => $refreshToken,
			'client_id' 	=> $this->config['su_client_id'],
			'client_secret' => $this->config['su_client_secret']));

		$token = json_decode(shell_exec(sprintf('curl %s -d "%s" -X POST', $this->token_url, $querystring)), true);

		// add timestamp
		$token['timestamp'] = time();

		return $token;
	}

	/*
	 * Second step for authentication [Gets the access_token and the refresh_token]
	 * @param string $code
	 * @return array
	 */
	public function getTokenByCode($code)
	{
		$querystring = http_build_query(array(
			'grant_type' 	=> 'authorization_code',
			'code' 			=> $code,
			'client_id' 	=> $this->config['su_client_id'],
			'client_secret' => $this->config['su_client_secret']));

		$token = json_decode(shell_exec(sprintf('curl %s -d "%s" -X POST', $this->token_url, $querystring)), true);
		// add timestamp
		$token['timestamp'] = time();

		return $token;
	}

	/*
	 * @return array
	 */
	public function getToken()
	{
		return $this->token;
	}

	/* Saves the token */
	/**
	 * @param array $token
	 * @return array
	 */
	public function setToken(array $token)
	{
		if (empty($token)) {
			throw new BoxapiException('token empty');
		}

		if (isset($token['error'])) {
			throw new BoxapiException($token['error_description']);
		}

		if ($refresh = $this->refreshTokenIfNeed($token)) {
			$token = $refresh;

			if (empty($token)) {
				throw new BoxapiException('token empty (refreshToken())');
			}

			if (isset($token['error'])) {
				throw new BoxapiException($token['error_description'] . ' (refreshToken())');
			}

			$this->is_refreshed = true;
		}

		$this->token = $token;

		$this->auth_header = sprintf('-H "Authorization: Bearer %s"', $token['access_token']);

		return $token;
	}

	/**
	 * 有効期限切れてたらtoken取り直してsetTokenする
	 * @return array|null
	 */
	private function refreshTokenIfNeed(array $token)
	{
		if ($this->expired($token['expires_in'], $token['timestamp'])) {
			return $this->getTokenByRefreshToken($token['refresh_token']);
		}
	}

	/**
	 * 有効期限切れ判定
	 * @return bool
	 */
	protected function expired($expires_in, $timestamp)
	{
		return (time() - $timestamp) >= $expires_in;
	}

	/**
	 * refreshTokenしたかどうか
	 * @return bool
	 */
	public function isRefreshed()
	{
		return $this->is_refreshed;
	}
}
