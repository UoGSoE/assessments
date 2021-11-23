<?php

namespace App\TODB;

class FakeBrokenTODBClient implements TODBClientInterface
{
    public function getCourses()
    {
        throw new \Exception('Broken Teaching Office test exception');
    }

    public function getCourse($code)
    {
    }

    public function getStaff($guid)
    {
    }
}
