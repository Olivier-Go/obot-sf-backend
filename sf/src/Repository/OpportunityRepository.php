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
            ->addOrderBy('o.received', 'DESC')
            ->getQuery()
            ;
    }

    public function findChartStatByDay(): array
    {
        return $this->createQueryBuilder('o')
            ->select("o.received HIDDEN, DATE_FORMAT(o.received, '%Y-%m-%d') AS x")
            ->addGroupBy('x')
            ->addSelect('COUNT(o.received) AS y')
            ->orderBy('x', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /*public function findChartStatByMonth(): array
    {
        $qb = $this->createQueryBuilder('o')
            ->select("o.received HIDDEN, DATE_FORMAT(o.received, '%d/%m/%Y') AS date, MONTH(o.received) AS month")
            ->addGroupBy('date')
            ->addSelect('COUNT(o.received) AS opportunity')
            ->orderBy('month', 'ASC')
            ->addOrderBy('o.received', 'ASC')
            ->getQuery()
        ;

        $labels = [];
        $data = [];
        dd($qb->getResult());
        foreach ($qb->getResult() as $result) {
            $labels[$result['month']] = $result['month'];
            $data[$result['month']][] = [
                'label' => $result['date'],
                'data' => $result['opportunity']
            ];
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }*/

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
