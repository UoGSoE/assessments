<?php

namespace App\TODB;

interface TODBClientInterface
{
    public function getCourses();

    public function getCourse($code);

    public function getStaff($guid);
}
