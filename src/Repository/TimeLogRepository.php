<?php

namespace App\Repository;

use App\Entity\TimeLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<TimeLog>
 */
class TimeLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimeLog::class);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findBySearch(UserInterface $user, int $month, int $year): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $firstDayOfMonth = sprintf('%04d-%02d-01', $year, $month);
        $trackerId = $user->getId();

        $sql = <<<SQL
-- WITH-Klausel für Tagesliste und vorbereitete Zeiterfassungen
WITH RECURSIVE all_days(day) AS (
    SELECT DATE('$firstDayOfMonth') AS day
    UNION ALL
    SELECT day + INTERVAL 1 DAY
    FROM all_days
    WHERE day + INTERVAL 1 DAY <= LAST_DAY('$firstDayOfMonth')
),
-- Alle Tracker (Mitarbeiter), die im Monat vorkommen
distinct_trackers AS (
    SELECT DISTINCT tracker_id
    FROM time_log
    WHERE created BETWEEN '$firstDayOfMonth' AND LAST_DAY('$firstDayOfMonth')
),
-- Kombinierte Liste: jeder Tag × jeder Tracker
days_and_trackers AS (
    SELECT d.day, t.tracker_id
    FROM all_days d
    CROSS JOIN (
        SELECT DISTINCT tracker_id
        FROM time_log
        WHERE created BETWEEN '$firstDayOfMonth' AND LAST_DAY('$firstDayOfMonth') AND tracker_id = $trackerId
    ) t
),
-- Paarung von Checkin und dem nächstfolgenden Checkout
paired_logs AS (
    SELECT
        t1.tracker_id,
        DATE(t1.created) AS log_date,
        t1.id AS checkin_id,
        t1.created AS checkin_time,
        t2.id AS checkout_id,
        t2.created AS checkout_time,
        ROUND(TIMESTAMPDIFF(SECOND, t1.created, t2.created) / 3600, 2) AS differenz_stunden
    FROM time_log t1
    LEFT JOIN time_log t2
      ON t2.tracker_id = t1.tracker_id
     AND t2.created = (
          SELECT MIN(t3.created)
          FROM time_log t3
          WHERE t3.tracker_id = t1.tracker_id
            AND t3.created > t1.created
            AND t3.event = 'checkout'
      )
    WHERE t1.event = 'checkin'
      AND t1.created BETWEEN '$firstDayOfMonth' AND LAST_DAY('$firstDayOfMonth')
)
-- Endgültige Abfrage: Tag × Tracker + Zeitdaten (sofern vorhanden)
SELECT
    d.day AS created,
    d.tracker_id,
    p.checkin_time,
    p.checkout_time,
    p.differenz_stunden AS diff
FROM days_and_trackers d
LEFT JOIN paired_logs p
  ON p.tracker_id = d.tracker_id AND p.log_date = d.day
ORDER BY d.tracker_id, d.day, p.checkin_time;
SQL;

        $resultSet = $conn->executeQuery($sql);

        return $resultSet->fetchAllAssociative();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function getEmpty(int $month, int $year): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $firstDayOfMonth = sprintf('%04d-%02d-01', $year, $month);

        $sql = <<<SQL
WITH RECURSIVE all_days(day) AS (
    SELECT DATE('$firstDayOfMonth') AS day
    UNION ALL
    SELECT day + INTERVAL 1 DAY
    FROM all_days
    WHERE day + INTERVAL 1 DAY <= LAST_DAY('$firstDayOfMonth')
)

SELECT
    d.day AS created,
    0 AS tracker_id,
    '' AS checkin_time,
    '' AS checkout_time,
    0 AS diff
FROM all_days d
ORDER BY d.day;
SQL;

        $resultSet = $conn->executeQuery($sql);

        return $resultSet->fetchAllAssociative();
    }
}
