<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;

Class Steamapi{
private $key;
private $client;
private $cache;
private $cache_file;

	public function __construct($key)
	{
		if (empty($key))
			exit('steamkey nötig!!!');
		$this->key = $key;

		//lade cache wenn file vorhanden
		$this->cache = [];
		$this->cache_file = __DIR__ . '/cache/Steamapi_'. md5($this->key);

		if (file_exists($this->cache_file))
		{
			$cache_raw = file_get_contents($this->cache_file);
			$cache_raw = gzuncompress($cache_raw);
			$this->cache = unserialize($cache_raw);
		}

		$this->client = new Client([
		    // Base URI is used with relative requests
		    'base_uri' => 'https://api.steampowered.com/',
		    // You can set any number of default request options.
		    'timeout'  => 20.0,
		]);
	}

	public function __destruct()
	{
			$cache_raw = serialize($this->cache);
			$cache_raw = gzcompress($cache_raw);
			
			file_put_contents($this->cache_file, $cache_raw);
	}

	private function execute($method, $params = [])
	{
		$params_string = '';
		foreach ($params as $param => $value)
		{
			if ($value === true)
				$value = '1';
			$params_string .= '&'. $param .'='. $value;
		}

		$response = $this->client->request('GET', $method .'/?key='. $this->key . $params_string .'&format=json');
		$body = json_decode($response->getBody(), true);
		if (!empty($body['response']))
			return $body['response'];

		return false;
	}

	public function gameslist()
	{
		return $this->execute('ISteamApps/GetAppList/v2');
	}

	public function ownedgames($steamid, $include_appinfo = true, $include_played_free_games = true)
	{
		return $this->execute('IPlayerService/GetOwnedGames/v1', compact('steamid', 'include_appinfo', 'include_played_free_games'))['games'];
	}

	public function imgurl($appid)
	{
		if (empty($appid))
			return false;
		return "//cdn.akamai.steamstatic.com/steam/apps/$appid/capsule_184x69.jpg";
	}

	public function isMultiplayer($appid)
	{
		if (isset($this->cache['ismp'][$appid]))
			return $this->cache['ismp'][$appid]; //wenn nicht leer dann gib gecachtes ergebnis zurück

		//bei fehlschlag von api nochmal probieren
		do {
			try {
			    $response = $this->client->request('GET', 'https://store.steampowered.com/api/appdetails?appids='. $appid);
			}
			catch (RequestException $e) {
				sleep(5);
				continue;
			}

			break;
		} while (true);

		$body = json_decode($response->getBody(), true);

		if (isset($body[$appid]['success']))
		{
			if ($body[$appid]['success'] == true && !empty($body[$appid]['data']['categories']))
			{
		
				foreach ($body[$appid]['data']['categories'] as $category)
				{
					if (in_array($category['id'], [1,36,9,38])) //1,36,9,38 sind multiplayer kategorien
					{
						return $this->cache['ismp'][$appid] = true;
					}
				}
			}
		return $this->cache['ismp'][$appid] = false;
		}

		exit('Fehler beim connecten zur Steam-Store API');
	}
}
