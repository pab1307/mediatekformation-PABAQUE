<?php

namespace App\Repository;

use App\Entity\Formation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Formation>
 */
class FormationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Formation::class);
    }

    public function add(Formation $entity): void
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    public function remove(Formation $entity): void
    {
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
    }

    /**
     * Retourne toutes les formations triées sur un champ.
     *
     * @param string      $champ
     * @param string      $ordre
     * @param string|null $table si $champ est dans une autre table
     *
     * @return Formation[]
     */
    public function findAllOrderBy(string $champ, string $ordre, ?string $table = null): array
    {
        if ($table === null || $table === '') {
            return $this->createQueryBuilder('f')
                ->orderBy('f.' . $champ, $ordre)
                ->getQuery()
                ->getResult();
        }

        return $this->createQueryBuilder('f')
            ->join('f.' . $table, 't')
            ->orderBy('t.' . $champ, $ordre)
            ->getQuery()
            ->getResult();
    }

    /**
     * Enregistrements dont un champ contient une valeur
     * ou tous les enregistrements si la valeur est vide.
     *
     * @param string      $champ
     * @param string      $valeur
     * @param string|null $table si $champ est dans une autre table
     *
     * @return Formation[]
     */
    public function findByContainValue(string $champ, string $valeur, ?string $table = null): array
    {
        if ($valeur === '') {
            return $this->findAll();
        }

        if ($table === null || $table === '') {
            return $this->createQueryBuilder('f')
                ->where('f.' . $champ . ' LIKE :valeur')
                ->orderBy('f.publishedAt', 'DESC')
                ->setParameter('valeur', '%' . $valeur . '%')
                ->getQuery()
                ->getResult();
        }

        return $this->createQueryBuilder('f')
            ->join('f.' . $table, 't')
            ->where('t.' . $champ . ' LIKE :valeur')
            ->orderBy('f.publishedAt', 'DESC')
            ->setParameter('valeur', '%' . $valeur . '%')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne les n formations les plus récentes.
     *
     * @param int $nb
     *
     * @return Formation[]
     */
    public function findAllLasted(int $nb): array
    {
        return $this->createQueryBuilder('f')
            ->orderBy('f.publishedAt', 'DESC')
            ->setMaxResults($nb)
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne la liste des formations d'une playlist.
     *
     * @param int $idPlaylist
     *
     * @return Formation[]
     */
    public function findAllForOnePlaylist(int $idPlaylist): array
    {
        return $this->createQueryBuilder('f')
            ->join('f.playlist', 'p')
            ->where('p.id = :id')
            ->setParameter('id', $idPlaylist)
            ->orderBy('f.publishedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
