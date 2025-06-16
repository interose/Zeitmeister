<?php

namespace App\Lib;

use App\Config\TimeLogEvent;
use Symfony\Component\Validator\Constraints as Assert;

class PayloadTimeTrackDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'Time entry cannot be empty')]
        #[Assert\DateTime(message: 'Time entry must be a valid datetime')]
        public string $ts,
        #[Assert\NotBlank(message: 'Time entry cannot be empty')]
        #[Assert\Choice(callback: [TimeLogEvent::class, 'values'])]
        public string $eventType,
    ) {
    }

    public function getEventTypeEnum(): TimeLogEvent
    {
        return TimeLogEvent::from($this->eventType);
    }

    public function getTime(): \DateTime
    {
        $tz = new \DateTimeZone('Europe/Berlin');

        return \DateTime::createFromFormat('Y-m-d H:i:s', $this->ts, $tz);
    }
}
