<?php
require_once 'Telegram.php';

$telegram = new Telegram(
    '1215215798:AAEI8l2bFpFgpIiBrGL6gUZ_02e9j5KLzFQ'
);
$openWeatherKey = "1c05a2b58e7942fe116e88ba52817282";
$data = $telegram->getData();
$callback_data = $telegram->Callback_Data();
$chatID = $telegram->Callback_ChatID();

$callback_query = $telegram->Callback_Query();
$message = $data['message'];
$text = $message['text'];
$chat_id = $message['chat']['id'];
$cities = [
    'Xonqa' => 'Xonqa tumani',
    'Qushkupir' => 'Qo\'shko\'pir tumani',
    'Khiwa' => 'Xiva tumani',
    'Hazorasp' => 'Hazorasp tumani',
    'Gurlan' => 'Gurlan tumani',
    'Urganch' => 'Urganch shahar',
    'Shovot' => 'Shovot tumani',
    'Yangiariq' => 'Yangiariq tumani',
    'Yangibazar' => 'Yangibazar tumani'
];
if ($text == "/start") {
    showMainPage();
}
elseif ($text == "📄 Tumanlar ro'yxati") {
    showCities();
}
elseif ($callback_query) {
    weatherInCity();
}
elseif ($message['location']['latitude']) {
    showLocationWeather();
    /*$telegram->sendMessage([
        'chat_id' => $chat_id,
        'text' => json_encode($data, JSON_PRETTY_PRINT)
    ]);*/
}
function showMainPage()
{
    global $telegram, $chat_id;
    $option = [
        //First row
        [$telegram->buildKeyBoardButton("📄 Tumanlar ro'yxati"), $telegram->buildKeyBoardButton("📍 Turgan joydagi ob-havo", false, true)],
    ];

    $keyb = $telegram->buildKeyBoard($option, $onetime = false, $resize = true);
    $content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "Kerakli bo'limni tanlang");
    $telegram->sendMessage($content);
}

function showCities()
{
    global $cities, $telegram, $chat_id;
    $option = [];
    $keys = array_keys($cities);
    for ($i = 0; $i < count($keys) - 1; $i += 2) {
        $option[] = [$telegram->buildInlineKeyboardButton("📍 " . $cities[$keys[$i]], "", $keys[$i]), $telegram->buildInlineKeyboardButton("📍 " . $cities[$keys[$i + 1]], "", $keys[$i + 1])];
    }
    $option[] = [$telegram->buildInlineKeyboardButton("📍 " . $cities[$keys[count($keys) - 1]], "", $keys[count($keys) - 1])];
    $keyb = $telegram->buildInlineKeyBoard($option);
    $content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "Kerakli tumanni tanlang");
    $telegram->sendMessage($content);
}

function weatherInCity()
{
    global $callback_data, $openWeatherKey, $telegram, $chatID, $cities;
    $request = "http://api.openweathermap.org/data/2.5/weather?q=" . $callback_data . "&appid=" . $openWeatherKey;
    $weatherData = json_decode(file_get_contents($request), true);
    $text = $cities[$callback_data] . "dagi Ob-havo 👇";
    $telegram->sendMessage([
        'chat_id' => $chatID,
        'text' => $text
    ]);
    showInformation($weatherData);
}

function showLocationWeather()
{
    global $message, $openWeatherKey,$telegram,$chat_id;
    $lat = $message['location']['latitude'];
    $lon = $message['location']['longitude'];
    $request = "http://api.openweathermap.org/data/2.5/weather?lat=" . $lat . "&lon=" . $lon . "&appid=" . $openWeatherKey;
    $text = "Siz turgan hududdagi Ob-havo 👇";
    $telegram->sendMessage([
        'chat_id' => $chat_id,
        'text' => $text
    ]);
    $weatherData = json_decode(file_get_contents($request), true);
    showInformation($weatherData);
}

function showInformation($weatherData)
{
    global $telegram, $chatID, $chat_id, $callback_query, $id;

    if ($callback_query != null && $callback_query != '')
        $id = $chatID;
    else
        $id = $chat_id;

    if ($weatherData['cod'] == 200) {
        $temperature = (int)($weatherData['main']['temp']) - 273;
        $tasir = (int)$weatherData['main']['feels_like'] - 273;
        $min = (int)$weatherData['main']['temp_min'] - 273;
        $max = (int)$weatherData['main']['temp_max'] - 273;
        $wind = (int)$weatherData['wind']['speed'];
        $content = "Xarorat 🌤 {$temperature} °C.\n";
        $content .= "Tasir qiluvchi harorat. {$tasir} °C.\n";
        $content .= "Xarorat 🌤  {$min} dan {$max}  gacha o'zgaradi.\n";
        $content .= "Shamol tezligi " . $wind . " m/s";
    } else {
        $content = "Ma'lumot topilmadi.";
    }
    $telegram->sendMessage([
        'chat_id' => $id,
        'text' => $content
    ]);
}
