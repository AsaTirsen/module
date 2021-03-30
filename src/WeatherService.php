<?php

namespace Asti\Weather;

use Asti\Geoip\CurlService;
use Asti\Ipcheck\HelperFunctions;

class WeatherService
{


    /*
     *
     * curls api, get_file_contents
     * sends response to model
     *
     */

    private $key = null;
    private $url = null;

    public function setKey($key)
    {
        $this->key = $key;
    }

    public function setUrl($url) : void
    {
        $this->url = $url;
    }

    public function getKey() : string
    {
        return $this->key;
    }

    public function getUrl() : string
    {
        return $this->url;
    }

    public function getDataThroughCurl(string $url)
    {
//        $url = "http://localhost:8080/dbwebb/ramverk1/me/redovisa/htdocs/api/ipcheck/check?ipCheck=$ip";


        //  Initiate curl handler
        $ch = curl_init();

        // Will return the response, if false it print the response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Set the url
        curl_setopt($ch, CURLOPT_URL, $url);

        // Execute
        $data = curl_exec($ch);

        // Closing
        curl_close($ch);

        return json_decode($data, true);
    }

    public function getMultipleCurls ($fetchURL, $dateArray, $fetchURLPart2)
    {
        $mh = curl_multi_init();
        $multiCurl = [];
        $curlArray = [];

        foreach($dateArray as $i => $date) {
            $URL = $fetchURL . $date . $fetchURLPart2;
            $multiCurl[$i] = curl_init();
            curl_setopt($multiCurl[$i], CURLOPT_URL, $URL);
            curl_setopt($multiCurl[$i], CURLOPT_HEADER, 0);
            curl_setopt($multiCurl[$i], CURLOPT_RETURNTRANSFER, 1);
            curl_multi_add_handle($mh, $multiCurl[$i]);
        }
        $index = null;
        do {
            curl_multi_exec($mh, $index);
        } while ($index > 0);

        foreach ($multiCurl as $k => $ch) {
            $result[$k] = curl_multi_getcontent($ch);
            array_push($curlArray, json_decode($result[$k], true));
            curl_multi_remove_handle($mh, $ch);
        }
        curl_multi_close($mh);
        return $curlArray;
    }

    public function loopThroughDate($dateArray): array
    {
        $newDateArray = [];
        forEach ($dateArray as $date) {
            $modifiedDate = date("Y-m-d", array_shift($date));
            array_push($newDateArray, $modifiedDate);
        }
        return $newDateArray;
    }
    public function loopThroughTemp($dateArray, $el1, $el2): array
    {
        $newTempArray = [];
        forEach ($dateArray as $temp) {
            $modTemp = $temp[$el1][$el2];
            array_push($newTempArray, substr($modTemp,0 ,5));
        }
        return $newTempArray;
    }
    public function loopThroughDesc($dateArray, $el1, $el2): array
    {
        $newDescArray = [];
        forEach ($dateArray as $desc) {
            $modDesc = $desc[$el1][0][$el2];
            array_push($newDescArray, $modDesc);
        }
        return $newDescArray;
    }

    public function curlWeatherApi($lon, $lat) : array
    {
        $res = $this->getDataThroughCurl($this->getUrl() . "?" . "lat=" . $lat . "&lon=" . $lon . "&units=metric" . "&lang=sv" . "&appid=" . $this->getKey());
        if (isset($res["cod"])) {
            return [
                "Error" => "Platsangivelse är fel"
            ];
        } else {
            $json = [
                "CurrentTemp" => substr($res["current"]["temp"], 0, 5),
                "CurrentFeelsLike" => substr($res["current"]["feels_like"], 0, 5),
                "CurrentWeather" => $res["current"]["weather"][0]["description"],
                "DailyDates" => $this->loopThroughDate($res["daily"]),
                "DailyTemperatures" => $this->loopThroughTemp($res["daily"], "temp", "day"),
                "DailyFeelsLike" => $this->loopThroughTemp($res["daily"], "feels_like", "day"),
                "DailyDescriptions" => $this->loopThroughDesc($res["daily"], "weather", "description")
            ];
            return [$json];
        }
    }


    public function curlOldWeatherApi($lon, $lat) : array
    {
        $dateArray = [];
        $currentTime = time();
        for ($x = 0; $x <= 4; $x++) {
            array_push($dateArray, $currentTime -= 86400);
        }
        $res = $this->getMultipleCurls($this->getUrl() . "/timemachine?" . "lat=" . $lat . "&lon=" . $lon . "&units=metric" . "&lang=sv" . "&dt=", $dateArray,  "&appid=" . $this->getKey());
        if (isset($res["cod"])) {
            return [
                "Error" => "Väder kan inte ges för positionen. Försök igen"
            ];
        }
        $json =  [
            "Date1" => date("Y-m-d", $res[0]["current"]["dt"]),
            "CurrentTemp1" => substr($res[0]["current"]["temp"], 0, 5),
            "CurrentFeelsLike1" => substr($res[0]["current"]["feels_like"], 0, 5),
            "CurrentWeather1" => $res[0]["current"]["weather"][0]["main"],
            "Date2" => date("Y-m-d", $res[1]["current"]["dt"]),
            "CurrentTemp2" => substr($res[1]["current"]["temp"], 0, 5),
            "CurrentFeelsLike2" =>  substr($res[1]["current"]["feels_like"], 0, 5),
            "CurrentWeather2" => $res[1]["current"]["weather"][0]["main"],
            "Date3" => date("Y-m-d", $res[2]["current"]["dt"]),
            "CurrentTemp3" => substr($res[2]["current"]["temp"], 0, 5),
            "CurrentFeelsLike3" =>  substr($res[2]["current"]["feels_like"], 0, 5),
            "CurrentWeather3" => $res[2]["current"]["weather"][0]["main"],
            "Date4" => date("Y-m-d", $res[3]["current"]["dt"]),
            "CurrentTemp4" => substr($res[3]["current"]["temp"], 0, 5),
            "CurrentFeelsLike4" =>  substr($res[3]["current"]["feels_like"], 0, 5),
            "CurrentWeather4" => $res[3]["current"]["weather"][0]["main"],
            "Date5" => date("Y-m-d", $res[4]["current"]["dt"]),
            "CurrentTemp5" => substr($res[4]["current"]["temp"], 0, 5),
            "CurrentFeelsLike5" =>  substr($res[4]["current"]["feels_like"], 0, 5),
            "CurrentWeather5" => $res[4]["current"]["weather"][0]["main"]
        ];
        return [$json];
    }
}
