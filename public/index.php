<!DOCTYPE html>
<html>
<head>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/floatthead/1.4.5/jquery.floatThead.min.js"></script>




<title>Spieleübersicht</title>
<style>
	body {
		background-color: black;
		color: #f5f5f5;
	}
	.table td, .table th{
		text-align: center !important;
		vertical-align: middle !important;
	}

	.table th{
		background-color: #2E2E2E;
	}

    td:first-child {
        width: 184px !important;
        height: 69px !important;
        padding: 0 !important;
        margin: 0 !important;
    }
</style>
</head>
<body>
<?php


require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../Steamapi.php';
require  __DIR__ . '/../helper_functions.php';

if (empty(getenv('STEAM_API_KEY'))) {
    throw new \Exception('steam api key was not set!!!');
}

$steamapi = new Steamapi(getenv('STEAM_API_KEY'));

$users = getUsersArray();

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
			$gameinfos[$game['appid']]['img'] = $steamapi->imgurl($game['appid']);
		}
	}
	else
		echo "Konnte Benutzer $user nicht abrufen!!!<br>";
}

//filtere Singleplayeroder unbekannt Spiele
foreach ($accountstogames as $appid => $owners)
{
	if (!$steamapi->isMultiplayer($appid))
		unset($accountstogames[$appid]);
}

echo "Spiele: " . count($accountstogames). "<br>";

echo '<table class="table table-bordered"><thead><tr><th>Spiel</th>';

foreach ($users as $user => $accountid)
{
	echo "<th>$user</th>";
}

echo '</tr></thead><tbody>';

arsort($accountstogames);

foreach ($accountstogames as $game => $owners)
{
	$nameorimage = $gameinfos[$game]['name'];
	if (!empty($gameinfos[$game]['img']))
		$nameorimage = "<img title=\"". $gameinfos[$game]['name'] ."\" src=\"". $gameinfos[$game]['img'] ."\" />";

	echo "<tr><td>$nameorimage</td>";
		foreach ($users as $user => $accountid)
		{
			if (in_array($accountid, $owners))
				echo '<td style="background-color:#21610B;"></td>';
			else
				echo '<td style="background-color:#8A0808;"></td>';
		}
	echo '</tr>';
}

echo '<tr>';

echo '</tr></tbody><table>';


?>

<script>
	$( document ).ready(function() {
		$('table').floatThead();
	});
</script>

</body>
</html>
