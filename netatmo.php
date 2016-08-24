<?php
// Original script by Cédric Locqueneux. http://maison-et-domotique.com

// Edit these
$password='xxx';
$username='xxx';
$client_id = 'xxx';
$client_secret = 'xxx';

// You should not edit anything below this line

$token_url = "https://api.netatmo.net/oauth2/token";
$postdata = http_build_query(
        array(
            'grant_type' => "password",
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'username' => $username,
            'password' => $password
    )
);

$opts = array('http' =>
        array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata
        )
);

$context  = stream_context_create($opts);
$response = file_get_contents($token_url, false, $context);

$params = null;
$params = json_decode($response, true);
$api_url = "https://api.netatmo.net/api/getuser?access_token=" . $params['access_token'];
$requete = @file_get_contents($api_url);

$url_devices = "https://api.netatmo.net/api/devicelist?access_token=" .  $params['access_token'];
$resulat_device = @file_get_contents($url_devices);

$json_devices = json_decode($resulat_device,true);
$module_interne = $json_devices["body"]["devices"][0]["_id"];
$module_externe = $json_devices["body"]["modules"][0]["_id"];

$url_mesures_internes = "https://api.netatmo.net/api/getmeasure?access_token=" .  $params['access_token'] . "&device_id=" . $module_interne . "&scale=max&type=Temperature,CO2,Humidity,Pressure,Noise&date_end=last";
$mesures_internes = @file_get_contents($url_mesures_internes);

$url_mesures_externes = "https://api.netatmo.net/api/getmeasure?access_token=" .  $params['access_token'] . "&device_id=" . $module_interne . "&module_id=" . $module_externe . "&scale=max&type=Temperature,Humidity&date_end=last";
$mesures_externes = @file_get_contents($url_mesures_externes);

$json_mesures_internes = json_decode($mesures_internes, true);
$json_mesures_externes = json_decode($mesures_externes, true);

$temperature_interne = $json_mesures_internes["body"][0]["value"][0][0];
$co2 = $json_mesures_internes["body"][0]["value"][0][1];
$humidite_interne = $json_mesures_internes["body"][0]["value"][0][2];
$pression = $json_mesures_internes["body"][0]["value"][0][3];
$bruit = $json_mesures_internes["body"][0]["value"][0][4];
$temperature_externe = $json_mesures_externes["body"][0]["value"][0][0];
$humidite_externe = $json_mesures_externes["body"][0]["value"][0][1];

// output
echo '<?xml version="1.0"?><items>';
echo '<item uid="temperature" arg="temperature"><title>Indoor: ' . $temperature_interne . '°C / Outdoor: ' . $temperature_externe . '°C</title><subtitle>Temperature</subtitle><icon>temperature.png</icon></item>';
echo '<item uid="humid" arg="humid"><title>Indoor: ' . $humidite_interne . '% / Outdoor: ' . $humidite_externe . '%</title><subtitle>Temperature</subtitle><icon>humidity.png</icon></item>';
echo '<item uid="pressure" arg="pressure"><title>' . $pression . ' mbar</title><subtitle>Pressure (indoor)</subtitle><icon>pressure.png</icon></item>';
echo '<item uid="noise" arg="noise"><title>' . $bruit . ' dB</title><subtitle>Noise (indoor)</subtitle><icon>noise.png</icon></item>';
echo '<item uid="co2" arg="co2"><title>' . $co2 . ' ppm</title><subtitle>CO2 (indoor)</subtitle><icon>co2.png</icon></item>';
echo '</items>';

?>
