<?php

namespace App\TODB;

class FakeTODBClient implements TODBClientInterface
{
    public $response;
    public $statusCode;
    protected $todbStaff;

    public function __construct()
    {
        $this->todbStaff = collect([]);
    }

    public function getCourses()
    {
        $this->statusCode = 200;
        return collect([$this->getCourse1(), $this->getCourse2()]);
    }

    protected function getCourse1()
    {
        return [
            'code' => 'TEST1234',
            'title' => "Fake Course 1234",
            'is_current' => 'Yes',
            'discipline' => [
                'title' => 'Electronics'
            ],
            'students' => [
                [
                    'matric' => '1234567',
                    'surname' => 'McFake',
                    'forenames' => 'Fakey',
                    'email' => '1234567@example.com'
                ],
                [
                    'matric' => '7654321',
                    'surname' => 'Smith',
                    'forenames' => 'Jenny',
                    'email' => '7654321@example.com'
                ]
            ],
            'staff' => [
                [
                    'guid' => 'fake1x',
                    'surname' => 'Faker',
                    'forenames' => 'Prof',
                    'email' => 'prof.faker@example.com'
                ],
                [
                    'guid' => 'blah2y',
                    'surname' => 'McManus',
                    'forenames' => 'Mark',
                    'email' => 'mark.mcmanus@example.com'
                ],
            ]
        ];
    }

    protected function getCourse2()
    {
        return [
            'code' => 'TEST4321',
            'title' => "Fake Course 4321",
            'is_current' => 'Yes',
            'discipline' => [
                'title' => 'Aero'
            ],
            'students' => [
                [
                    'matric' => '9999999',
                    'surname' => 'Goldie',
                    'forenames' => 'Debbie',
                    'email' => '9999999@example.com'
                ],
                [
                    'matric' => '7654321',
                    'surname' => 'Smith',
                    'forenames' => 'Jenny',
                    'email' => '7654321@example.com'
                ],
            ],
            'staff' => [
                [
                    'guid' => 'doc2w',
                    'surname' => 'Baker',
                    'forenames' => 'Tom',
                    'email' => 'tom.baker@example.com'
                ],
                [
                    'guid' => 'blah2y',
                    'surname' => 'McManus',
                    'forenames' => 'Mark',
                    'email' => 'mark.mcmanus@example.com'
                ],
            ],
        ];
    }
}
