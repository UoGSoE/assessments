<?php

namespace App\TODB;

use GuzzleHttp\Client;

class TODBClient implements TODBClientInterface
{
    public $response;
    public $statusCode;
    public $responseMessage;
    public $responseCode;
    protected $client;
    protected $todbStaff;
    protected $baseUri;

    public function __construct()
    {
        $this->client = new Client;
        $this->todbStaff = collect([]);
        $this->baseUri = config('assessments.todb_uri');
    }

    protected function get($url)
    {
        return $this->client->get($url);
    }

    public function getData($endpoint)
    {
        $this->response = $this->get($this->baseUri . $endpoint);
        $this->statusCode = $this->response->getStatusCode();
        $json = json_decode($this->response->getBody(), true);
        if (!array_key_exists('Data', $json)) {
            return collect([]);
        }
        $this->responseMessage = $json['Response'];
        $this->responseCode = $json['ResponseCode'];
        return collect($json['Data']);
    }

    public function getCourses()
    {
        return $this->getData('getcourse/all');
    }

    public function getCourse($code)
    {
        return $this->getData("getcourse/{$code}");
    }

    public function getStaff($guid)
    {
        if (!$this->todbStaff->has($guid)) {
            $this->todbStaff[$guid] = $this->getData("getdetails/{$guid}");
        }
        return $this->todbStaff[$guid];
    }
}
