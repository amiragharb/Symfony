<?php

namespace App\Repository;

use App\Entity\CommandeClient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
/**
 * @method CommandeClient|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommandeClient|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommandeClient[]    findAll()
 * @method CommandeClient[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method \Doctrine\ORM\QueryBuilder createQueryBuilder($alias, $indexBy = null) // Ajouté pour l'IDE
 */
class CommandeClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommandeClient::class);
    }

    // Vous pouvez ajouter des méthodes personnalisées ici pour interagir avec les données des clients si nécessaire
}
