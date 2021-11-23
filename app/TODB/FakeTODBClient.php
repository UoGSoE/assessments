<?php

namespace App\TODB;

class FakeTODBClient implements TODBClientInterface
{
    public $response;
    public $statusCode;
    protected $todbStaff;
    public $responseCode;
    public $responseMessage;

    public function __construct()
    {
        $this->todbStaff = collect([]);
    }

    public function getData($url)
    {
        return collect([]);
    }

    public function getCourses()
    {
        $this->statusCode = 200;
        return collect(['TEST1234' => $this->getCourse1(), 'TEST4321' => $this->getCourse2()]);
    }

    public function getCourse($code)
    {
        $this->statusCode = 200;
        return $this->getCourse1();
    }

    public function getStaff($guid)
    {
        $this->statusCode = 200;
        if ($guid == 'NONEXISTANT') {
            $this->responseCode = -1;
            $this->responseMessage = 'No such GUID';
            return collect([]);
        }
        if ($guid == 'TODBDOWN') {
            throw new \Exception('TODB Error');
        }
        if (!$this->todbStaff->has($guid)) {
            $this->todbStaff[$guid] = collect([
                'GUID' => $guid,
                'Email' => "{$guid}@glasgow.ac.uk",
                'Surname' => 'McFake',
                'Forenames' => 'Jake'
            ]);
        }
        return $this->todbStaff[$guid];
    }

    protected function getCourse1()
    {
        return [
            'Code' => 'TEST1234',
            'Title' => "Fake Course 1234",
            'ActiveFlag' => 'Yes',
            'Discipline' => 'Electronics',
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
        ];
    }

    protected function getCourse2()
    {
        return [
            'Code' => 'TEST4321',
            'Title' => "Fake Course 4321",
            'ActiveFlag' => 'Yes',
            'Discipline' => 'Aero',
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
        ];
    }
}
