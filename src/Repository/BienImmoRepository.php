<?php

namespace App\Repository;

use App\Entity\BienImmo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BienImmo|null find($id, $lockMode = null, $lockVersion = null)
 * @method BienImmo|null findOneBy(array $criteria, array $orderBy = null)
 * @method BienImmo[]    findAll()
 * @method BienImmo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BienImmoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BienImmo::class);
    }

    // /**
    //  * @return BienImmo[] Returns an array of BienImmo objects
    //  */



    public function findWithoutLocataires()
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.locataires = :val')
            ->setParameter('val', null)
            ->orderBy('b.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }



//    public function findByUserId($id): ?BienImmo
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.user = :val')
//            ->setParameter('val', $id)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

}
