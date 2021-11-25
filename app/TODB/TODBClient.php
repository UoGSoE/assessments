<?php

namespace App\TODB;

use Zttp\Zttp;

class TODBClient implements TODBClientInterface
{
    public $response;
    public $statusCode;
    protected $client;
    protected $baseUri;

    public function __construct()
    {
        $this->client = new Zttp;
        $this->baseUri = config('assessments.todb_uri');
    }

    public function getCourses()
    {
        $this->response = $this->client::withHeaders([
            'x-api-key' => config('assessments.todb_key')
        ])->get($this->baseUri . 'courses');

        $this->statusCode = $this->response->getStatusCode();
        $json = $this->response->json();
        if (!array_key_exists('data', $json)) {
            return collect([]);
        }
        return collect($json['data']);
    }
}
