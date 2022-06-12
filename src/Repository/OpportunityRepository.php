<?php

namespace App\Repository;

use App\Entity\Opportunity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Opportunity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Opportunity|null findOneBy(array $criteria, array $orderBy = null)
 * @method Opportunity[]    findAll()
 * @method Opportunity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OpportunityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Opportunity::class);
    }

    public function findAllQB(): Query
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.buyMarket', 'buyMarket')
            ->leftJoin('o.sellMarket', 'sellMarket')
            ->leftJoin('o.buyOrder', 'buyOrder')
            ->leftJoin('o.sellOrder', 'sellOrder')
            ->addOrderBy('o.created', 'DESC')
            ->getQuery()
            ;
    }

    public function findChartStat(?string $format = null, ?\DateTime $dateStart = null, ?\DateTime $dateEnd = null): array
    {
        switch ($format) {
            case 'year':
                $format = '%Y';
                $dateStart = $dateStart ?? new \DateTime('first day of january previous year');
                $dateEnd = $dateEnd ? $dateEnd->modify('+ 1 day') : new \DateTime('first day of january next year');
                break;
            case 'month':
                $format = '%Y-%m';
                $dateStart = $dateStart ?? new \DateTime('first day of january this year');
                $dateEnd = $dateEnd ? $dateEnd->modify('+ 1 day') : new \DateTime('first day of january next year');
                break;
            case 'day':
            default:
                $format = '%Y-%m-%d';
                $dateStart = $dateStart ?? new \DateTime('first day of previous month');
                $dateEnd = $dateEnd ? $dateEnd->modify('+ 1 day') : new \DateTime('last day of this month');
        }

        return $this->createQueryBuilder('o')
            ->select("o.received HIDDEN, DATE_FORMAT(o.received, :format) AS x")
            ->andWhere('o.received BETWEEN :dateStart AND :dateEnd')
            ->setParameter('format', $format)
            ->setParameter('dateStart', $dateStart)
            ->setParameter('dateEnd', $dateEnd)
            ->addGroupBy('x')
            ->addSelect('COUNT(o.received) AS y')
            ->orderBy('x', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return Opportunity[] Returns an array of Opportunity objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Opportunity
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
