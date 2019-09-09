<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/Steamapi.php';

if (empty(getenv('STEAM_API_KEY'))) {
    throw new \Exception('steam api key was not set!!!');
}

$steamapi = new Steamapi(getenv('STEAM_API_KEY'));

if (empty(getenv('USERS_TO_COMPARE'))) {
    throw new \Exception('users to compare are not set!!!');
}

$users = json_decode(getenv('USERS_TO_COMPARE') , true);

$accountstogames = [];
$gameinfos = [];

foreach ($users as $user => $accountid)
{
	$games = [];
	if ($games = $steamapi->ownedgames($accountid))
	{
		foreach ($games as $game)
		{
			$accountstogames[$game['appid']][$user] = $accountid;

			$gameinfos[$game['appid']]['name'] = $game['name'];
			$gameinfos[$game['appid']]['img'] = $steamapi->imgurl($game['appid'], $game['img_logo_url']);
		}
	}
	else
		echo "Konnte Benutzer $user nicht abrufen!!!<br>";
}

$maxgames = count($gameinfos);
$upcount = 1;
foreach($gameinfos as $appid => $infos)
{
	echo $upcount++ ." von ". $maxgames . " >> ";

	echo "ID_$appid >> ". $infos['name'] ." >> ismp=";
	if ($steamapi->isMultiplayer($appid))
		echo "TRUE";
	else
		echo "FALSE";
	
	echo "\n";
}
