<?php
if (! function_exists('server_url')) {
	
	function server_url ($uri = '', string $protocol = null) : string {
		
		// convert segment array to string
		if (is_array($uri)) {
			$uri = implode('/', $uri);
		}
		$uri = trim($uri, '/');
		
		// We should be using the configured baseURL that the user set;
		// otherwise get rid of the path, because we have
		// no way of knowing the intent...
		$config = \Config\Services::request()->config;
		
		// If baseUrl does not have a trailing slash it won't resolve
		// correctly for users hosting in a subfolder.
		$serverUrl = ! empty($config->serverURL) && $config->serverURL !== '/'
				? rtrim($config->serverURL, '/ ') . '/'
				: $config->serverURL;
				
		$url = new \CodeIgniter\HTTP\URI($serverUrl);
		unset($config);
		
		// Merge in the path set by the user, if any
		if (! empty($uri))
		{
			$url = $url->resolveRelativeURI($uri);
		}
		
		// If the scheme wasn't provided, check to
		// see if it was a secure request
		if (empty($protocol) && \Config\Services::request()->isSecure())
		{
			$protocol = 'https';
		}
		
		if (! empty($protocol))
		{
			$url->setScheme($protocol);
		}
		
		return rtrim((string) $url, '/ ');
	}
}