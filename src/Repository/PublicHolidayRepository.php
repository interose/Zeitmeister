<?php

namespace App\Repository;

use App\Entity\PublicHoliday;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PublicHoliday>
 */
class PublicHolidayRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PublicHoliday::class);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function getByYear(int $year): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT holiday_date, name FROM public_holiday WHERE holiday_date BETWEEN :start AND :end';
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('start', "$year-01-01 00:00:00");
        $stmt->bindValue('end', "$year-12-31 23:59:59");

        return $stmt->executeQuery()->fetchAllKeyValue();
    }
}
