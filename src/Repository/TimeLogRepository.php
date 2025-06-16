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
        $firstDayOfMonth = sprintf('%04d-%02d-01', $year, $month);
        $conn = $this->getEntityManager()->getConnection();

        $sql = <<<SQL
WITH RECURSIVE all_days(day) AS (
    SELECT '$firstDayOfMonth' AS day
    UNION ALL
    SELECT day + INTERVAL 1 DAY
    FROM all_days
    WHERE day + INTERVAL 1 DAY <= LAST_DAY('$firstDayOfMonth')
)
SELECT DATE_FORMAT(day, '%Y-%m-%d') as created
FROM all_days
ORDER BY created ASC;
SQL;

        $resultSet = $conn->executeQuery($sql);

        return $resultSet->fetchAllAssociative();
    }
}
