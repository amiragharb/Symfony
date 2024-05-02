<?php
namespace App\Repository;

use App\Entity\CommandeResto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CommandeResto|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommandeResto|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommandeResto[]    findAll()
 * @method CommandeResto[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommandeRestoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommandeResto::class);
    }
    public function findByClient($clientId)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.client = :clientId')
            ->setParameter('clientId', $clientId)
            ->getQuery()
            ->getResult();
    }
    public function findByNumeroCommande($id)
    {
        return $this->findOneBy(['id' => $id]);
    }
}
