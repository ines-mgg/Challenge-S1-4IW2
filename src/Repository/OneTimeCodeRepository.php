<?php

namespace App\Repository;

use App\Entity\OneTimeCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OneTimeCode>
 *
 * @method OneTimeCode|null find($id, $lockMode = null, $lockVersion = null)
 * @method OneTimeCode|null findOneBy(array $criteria, array $orderBy = null)
 * @method OneTimeCode[]    findAll()
 * @method OneTimeCode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OneTimeCodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OneTimeCode::class);
    }

//    /**
//     * @return OneTimeCode[] Returns an array of OneTimeCode objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?OneTimeCode
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
