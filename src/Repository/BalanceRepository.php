<?php

namespace App\Repository;

use App\Entity\Balance;
use App\Entity\Market;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Balance|null find($id, $lockMode = null, $lockVersion = null)
 * @method Balance|null findOneBy(array $criteria, array $orderBy = null)
 * @method Balance[]    findAll()
 * @method Balance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BalanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Balance::class);
    }

    public function findMarketBalancesForTicker(Market $market, string $ticker)
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.ticker', 't')
            ->leftJoin('b.market', 'm')
            ->andWhere('m = :market')
            ->andWhere('t.name = :ticker')
            ->setParameter('market', $market)
            ->setParameter('ticker', $ticker)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findChartStat(): array
    {
        $assets = $this->createQueryBuilder('b')
            ->select('b.currency AS currency')
            ->addSelect('SUM(b.available) AS available')
            ->addSelect('SUM(b.hold) AS hold')
            ->addGroupBy('currency')
            ->getQuery()
            ->getResult()
            ;

        $data = [];
        foreach ($assets as $asset) {
            $data[$asset['currency'] .' (available)'] = $asset['available'];
            $data[$asset['currency'] .' (hold)'] = $asset['hold'];
        }

        return $data;
    }

    // /**
    //  * @return Balance[] Returns an array of Balance objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Balance
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
