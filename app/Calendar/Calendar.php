<?php

namespace App\Calendar;

use Eluceo\iCal\Component\Calendar as iCal;
use Eluceo\iCal\Component\Event;
use Storage;

class Calendar
{
    public function __construct(iCal $calendar)
    {
        $this->calendar = $calendar;
    }

    public function addEvents($events)
    {
        foreach ($events as $event) {
            $this->addEvent($event);
        }
        return $this;
    }

    public function addEvent($event)
    {
        $icalEvent = new Event;
        $icalEvent->setDtStart($event['start'])
            ->setDtEnd($event['end'])
            ->setSummary($event['title']);
        $this->calendar->addComponent($icalEvent);
        return $this;
    }

    public function render()
    {
        return $this->calendar->render();
    }

    public function save($filename)
    {
        Storage::disk('calendars')->put($filename, $this->render());
    }
}
