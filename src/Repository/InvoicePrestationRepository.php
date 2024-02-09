<?php

namespace App\Repository;

use App\Entity\InvoicePrestation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InvoicePrestation>
 *
 * @method InvoicePrestation|null find($id, $lockMode = null, $lockVersion = null)
 * @method InvoicePrestation|null findOneBy(array $criteria, array $orderBy = null)
 * @method InvoicePrestation[]    findAll()
 * @method InvoicePrestation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvoicePrestationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvoicePrestation::class);
    }

//    /**
//     * @return InvoicePrestation[] Returns an array of InvoicePrestation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?InvoicePrestation
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
