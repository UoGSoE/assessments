<?php

namespace App\Wlm;

class FakeWlmClient
{
    public $response;
    public $statusCode;
    protected $wlmStaff;

    public function __construct()
    {
        $this->wlmStaff = collect([]);
    }

    public function getData($url)
    {
        return collect([]);
    }

    public function getCourses()
    {
        $this->statusCode = 200;
        return collect([
            'ENG1234' => [
                'Code' => 'ENG1234',
                'Title' => "Fake Course 1234",
                'Students' => [
                    '1234567' => [
                        'Matric' => '1234567',
                        'Surname' => 'McFake',
                        'Forenames' => 'Fakey'
                    ],
                    '7654321' => [
                        'Matric' => '7654321',
                        'Surname' => 'Smith',
                        'Forenames' => 'Jenny'
                    ],
                ],
                'Staff' => [
                    'fake1x' => [
                        'GUID' => 'fake1x',
                        'Surname' => 'Faker',
                        'Forenames' => 'Prof'
                    ],
                    'blah2y' => [
                        'GUID' => 'blah2y',
                        'Surname' => 'McManus',
                        'Forenames' => 'Mark'
                    ],
                ],
            ],
            'ENG4321' => [
                'Code' => 'ENG4321',
                'Title' => "Fake Course 4321",
                'Students' => [
                    '9999999' => [
                        'Matric' => '9999999',
                        'Surname' => 'Goldie',
                        'Forenames' => 'Debbie'
                    ],
                    '7654321' => [
                        'Matric' => '7654321',
                        'Surname' => 'Smith',
                        'Forenames' => 'Jenny'
                    ],
                ],
                'Staff' => [
                    'doc2w' => [
                        'GUID' => 'doc2w',
                        'Surname' => 'Baker',
                        'Forenames' => 'Tom'
                    ],
                    'blah2y' => [
                        'GUID' => 'blah2y',
                        'Surname' => 'McManus',
                        'Forenames' => 'Mark'
                    ],
                ],
            ],

        ]);
    }

    public function getStaff($guid)
    {
        $this->statusCode = 200;
        if (!$this->wlmStaff->has($guid)) {
            $this->wlmStaff[$guid] = collect([
                'GUID' => $guid,
                'Email' => "{$guid}@glasgow.ac.uk",
                'Surname' => 'McFake',
                'Forenames' => 'Jake'
            ]);
        }
        return $this->wlmStaff[$guid];
    }
}
