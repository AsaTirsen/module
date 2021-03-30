<?php

namespace Asti\Geoip;

class GeoipService
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

    public function setUrl($url): void
    {
        $this->url = $url;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getUrl(): string
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
    public function curlIpApi($ipAdr): array
    {
        if ($ipAdr!= "") {
            $res = $this->getDataThroughCurl($this->getUrl() . $ipAdr . "?access_key=" . $this->getKey());

            if ($res["type"] == null) {
                $json = [
                    "Message" => "IP-adressen är fel. Försök igen!"
                ];
                return $json;
            }
            $json = [
                "Type" => $res["type"],
                "Valid" => $res["type"] ? "ipv4" || "ipv6" : "not valid",
                "UserInput" => $res["ip"],
                "Latitude" => $res["latitude"],
                "Longitude" => $res["longitude"],
                "City" => $res["city"],
                "Country" => $res["country_name"],
            ];
            return [$json];
        }
        else {
            $json = [
                "Message" => "IP-adressen är tom. Försök igen"
            ];
            return $json;
        }
    }
}
