<?php

namespace App\Lib;

class SqlDataTransformer
{
    public const WORKING_HOURS_PER_DAY = 8;

    public function setupData(array $timeLogs, array $holidays): array
    {
        $created = null;
        $tz = new \DateTimeZone('Europe/Berlin');
        $td = \DateTime::createFromFormat('Y-m-d', $timeLogs[0]['created'], $tz);

        $formatter = new \IntlDateFormatter(
            'de_DE',
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::NONE,
            null,
            null,
            'ccc d.' // Day name
        );

        // group by date
        $groupedByDate = [];
        foreach ($timeLogs as $timeLog) {
            $created = $timeLog['created'];

            if (!isset($groupedByDate[$created])) {
                $groupedByDate[$created] = [];
            }

            $groupedByDate[$created][] = $timeLog;
        }

        unset($created);

        $result = [];
        $weekNumber = $td->format('W');
        $weekSum = 0;
        $weekSumPlan = 0;
        foreach ($groupedByDate as $date => $records) {
            $td = \DateTime::createFromFormat('Y-m-d', $date, $tz);

            $weekSumPlan += 8;

            foreach ($records as $key => $record) {
                $row = [];
                $row['created'] = $date;

                // create a col where the name of the day and the day number of the month is only visible for the first
                // record
                if (0 === $key) {
                    $row['date'] = $formatter->format($td);
                } else {
                    $row['date'] = '';
                }

                // check if day is a holiday day
                if (isset($holidays[$date])) {
                    $row['publicHoliday'] = $holidays[$date];
                    $row['diff'] = self::WORKING_HOURS_PER_DAY;
                    $record['diff'] = self::WORKING_HOURS_PER_DAY;
                } else {
                    $row['publicHoliday'] = '';
                    $row['diff'] = $record['diff'];
                }

                // calculate the week sum
                if ($weekNumber !== $td->format('W')) {
                    $weekSum = $record['diff'];
                    $weekNumber = $td->format('W');

                    // reset the weekly working hours if the week number changes
                    $weekSumPlan = self::WORKING_HOURS_PER_DAY;
                } else {
                    $weekSum += $record['diff'];
                }

                // add weekly sums to the last daily entry
                if ($key === count($records) - 1 && $record['diff'] > 0) {
                    $row['weekSum'] = $weekSum;
                    $row['weekSumPlan'] = $weekSumPlan;
                } else {
                    $row['weekSum'] = '';
                    $row['weekSumPlan'] = '';
                }

                // append needed values
                $row['checkin_time'] = $record['checkin_time'];
                $row['checkout_time'] = $record['checkout_time'];

                $result[] = $row;
            }
        }

        return $result;
    }
}
