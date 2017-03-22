<?php

namespace App\Wlm;

use GuzzleHttp\Client;

class WlmClient
{
    protected $client;
    public $response;
    public $statusCode;
    protected $wlmStaff;

    public function __construct()
    {
        $this->client = new Client;
        $this->wlmStaff = collect([]);
    }

    protected function get($url)
    {
        return $this->client->get($url);
    }

    public function getData($url)
    {
        $this->response = $this->get($url);
        $this->statusCode = $this->response->getStatusCode();
        $json = json_decode($this->response->getBody(), true);
        if (!array_key_exists('Data', $json)) {
            return collect([]);
        }
        return collect($json['Data']);
    }

    public function getCourses()
    {
        return $this->getData('http://localhost:8088/persons3/api/getcourse/all');
    }

    public function getStaff($guid)
    {
        if (!$this->wlmStaff->has($guid)) {
            $this->wlmStaff[$guid] = $this->getData("http://localhost:8088/persons3/api/getdetails/{$guid}");
        }
        return $this->wlmStaff[$guid];
    }
}
