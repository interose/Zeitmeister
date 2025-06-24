<?php

namespace App\Lib;

class SqlDataTransformer
{
    public function setupData(array $timeLogs, array $holidays): array
    {
        $result = [];
        $created = $timeLogs[0]['created'];

        $tz = new \DateTimeZone('Europe/Berlin');
        $td = \DateTime::createFromFormat('Y-m-d', $timeLogs[0]['created'], $tz);
        $weekNumber = $td->format('W');
        $weekSum = 0;

        foreach ($timeLogs as $timeLog) {
            // only display the day once but at list just one more time
            if ($created !== $timeLog['created']) {
                $timeLog['date'] = $timeLog['created'];
                $created = $timeLog['created'];
            } else {
                $timeLog['date'] = '';
            }

            if (isset($holidays[$timeLog['created']])) {
                $timeLog['publicHoliday'] = $holidays[$timeLog['created']];
                $timeLog['diff'] = 8;
            } else {
                $timeLog['publicHoliday'] = '';
            }

            $td = \DateTime::createFromFormat('Y-m-d', $timeLog['created'], $tz);
            if ($weekNumber !== $td->format('W')) {
                $weekSum = $timeLog['diff'];
                $timeLog['weekSum'] = $weekSum;
                $weekNumber = $td->format('W');
            } else {
                $weekSum += $timeLog['diff'];
                $timeLog['weekSum'] = $weekSum;
            }



            $result[] = $timeLog;
        }

        return $result;
    }
}
