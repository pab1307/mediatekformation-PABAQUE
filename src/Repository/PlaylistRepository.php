<?php

namespace App\Repository;

use App\Entity\Playlist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Playlist>
 */
class PlaylistRepository extends ServiceEntityRepository
{
    public const ORDER_ASC  = 'ASC';
    public const ORDER_DESC = 'DESC';
    public const FIELD_NAME = 'name';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Playlist::class);
    }

    public function add(Playlist $entity): void
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    public function remove(Playlist $entity): void
    {
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
    }

    /**
     * Retourne toutes les playlists triées sur le nom de la playlist.
     *
     * @param string $ordre
     *
     * @return Playlist[]
     */
    public function findAllOrderByName(string $ordre): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.formations', 'f')
            ->groupBy('p.id')
            ->orderBy('p.' . self::FIELD_NAME, $ordre)
            ->getQuery()
            ->getResult();
    }


    /**
     * Enregistrements dont un champ contient une valeur
     * ou tous les enregistrements si la valeur est vide.
     *
     * @param string      $champ
     * @param string      $valeur
     * @param string|null $table si $champ dans une autre table
     *
     * @return Playlist[]
     */
    public function findByContainValue(string $champ, string $valeur, ?string $table = null): array
    {
    if ($valeur === '') {
        return $this->findAllOrderByName(self::ORDER_ASC);
    }

    if ($table === null || $table === '') {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.formations', 'f')
            ->where('p.' . $champ . ' LIKE :valeur')
            ->setParameter('valeur', '%' . $valeur . '%')
            ->groupBy('p.id')
            ->orderBy('p.' . self::FIELD_NAME, self::ORDER_ASC)
            ->getQuery()
            ->getResult();
    }

    return $this->createQueryBuilder('p')
        ->leftJoin('p.formations', 'f')
        ->leftJoin('f.categories', 'c')
        ->where('c.' . $champ . ' LIKE :valeur')
        ->setParameter('valeur', '%' . $valeur . '%')
        ->groupBy('p.id')
        ->orderBy('p.' . self::FIELD_NAME, self::ORDER_ASC)
        ->getQuery()
        ->getResult();
    }
}
