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
     * Retourne toutes les playlists triées sur le nombre de formations associées.
     *
     * @param string $ordre 'ASC' ou 'DESC'
     *
     * @return Playlist[]
     */
    public function findAllOrderByNbFormations(string $ordre): array
    {
        if (!in_array($ordre, [self::ORDER_ASC, self::ORDER_DESC], true)) {
            $ordre = self::ORDER_ASC;
        }

        return $this->createQueryBuilder('p')
            ->leftJoin('p.formations', 'f')
            ->addSelect('COUNT(f.id) AS HIDDEN nbFormations')
            ->groupBy('p.id')
            ->orderBy('nbFormations', $ordre)
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
        // si pas de valeur, on renvoie tout, trié par nom
        if ($valeur === '') {
            return $this->findAllOrderByName(self::ORDER_ASC);
        }

        // champ dans Playlist
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

        // champ dans une autre table (ex : catégories)
        return $this->createQueryBuilder('p')
            ->leftJoin('p.formations', 'f')
            ->leftJoin('f.categories', 'c')
            ->where('c.' . $champ . ' LIKE :valeur')
            ->setParameter('valeur', '%' . $valeur . '%')
            ->groupBy('p.id')
            ->orderBy('p.' . self::FIELD_NAME, self::ORDER_ASC)
            ->getQuery()
            ->getResult();
    }   // 👈👈👈 THIS BRACE WAS MISSING

    /**
     * Playlists pour le back-office avec tri et filtre sur le nom.
     *
     * @return Playlist[]
     */
    public function findForBackOffice(
        string $tri = 'name',
        string $ordre = self::ORDER_ASC,
        ?string $filtreName = null
    ): array {
        if (!in_array($ordre, [self::ORDER_ASC, self::ORDER_DESC], true)) {
            $ordre = self::ORDER_ASC;
        }

        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.formations', 'f')
            ->addSelect('COUNT(f.id) AS HIDDEN nbFormations')
            ->groupBy('p.id');

        // filtre sur le nom de la playlist
        if ($filtreName !== null && $filtreName !== '') {
            $qb->andWhere('p.' . self::FIELD_NAME . ' LIKE :name')
               ->setParameter('name', '%' . $filtreName . '%');
        }

        // tri
        if ($tri === 'nb') {
            $qb->orderBy('nbFormations', $ordre);
        } else { // 'name' par défaut
            $qb->orderBy('p.' . self::FIELD_NAME, $ordre);
        }

        return $qb->getQuery()->getResult();
    }

}
