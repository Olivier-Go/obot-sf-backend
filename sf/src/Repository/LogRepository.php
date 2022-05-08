<?php

namespace App\Repository;

use App\Entity\Balance;
use App\Entity\Log;
use App\Utils\Tools;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @extends ServiceEntityRepository<Log>
 *
 * @method Log|null find($id, $lockMode = null, $lockVersion = null)
 * @method Log|null findOneBy(array $criteria, array $orderBy = null)
 * @method Log[]    findAll()
 * @method Log[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogRepository extends ServiceEntityRepository
{
    private NormalizerInterface $normalizer;

    public function __construct(ManagerRegistry $registry, NormalizerInterface $normalizer)
    {
        parent::__construct($registry, Log::class);
        $this->normalizer = $normalizer;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Log $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Log $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function findChartStat(string $entityName): array
    {
        $rtn = [];
        $logs = $this->findBy(['entityName' => $entityName]);

        if ($entityName === 'Balance') {
            foreach ($logs as $log) {
                $balance = $this->normalizer->denormalize($log->getContent(), Balance::class);
                if ($balance->getTotal() != 0) {
                    $date = $log->getCreated()->format('Y-m-d H:i');
                    $dateExistKey = isset($rtn[$balance->getCurrency()]) ? Tools::array_search_nested($rtn[$balance->getCurrency()], 'label', $date) : false;
                    if (is_int($dateExistKey)) {
                        $rtn[$balance->getCurrency()][$dateExistKey] = [
                            'data' => $rtn[$balance->getCurrency()][$dateExistKey]['data'] + $balance->getTotal(),
                            'label' => $date
                        ];
                    }
                    else {
                        $rtn[$balance->getCurrency()][] = [
                            'data' => $balance->getTotal(),
                            'label' => $date
                        ];
                    }
                }
            }
        }

        return $rtn;
    }

    // /**
    //  * @return Log[] Returns an array of Log objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Log
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
