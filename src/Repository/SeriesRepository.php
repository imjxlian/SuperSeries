<?php

namespace App\Repository;

use App\Entity\Series;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @method Series|null find($id, $lockMode = null, $lockVersion = null)
 * @method Series|null findOneBy(array $criteria, array $orderBy = null)
 * @method Series[]    findAll()
 * @method Series[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SeriesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Series::class);
    }

    public function getSeries($sort = null, $search = null)
    {
        $query = $this->createQueryBuilder('s')
            ->select('s, COALESCE(avg(r.value), 0), er')
            ->leftjoin('s.externalRating', 'er')
            ->leftJoin('s.ratings', 'r')
            ;

            if($search != null){
                $query->where('s.title LIKE :search')
                ->setParameter('search', '%' . htmlspecialchars($search) . '%');
            }

            if($sort != null){
                $query->orderBy('avg(r.value)', $sort == 'ASC' ? 'ASC' : 'DESC');
                $query->addOrderBy('s.title', 'ASC');
            } else {
                $query->orderBy('s.title', 'ASC');
            }

            $query->groupBy('s.id')
            ->getQuery()
            ->getResult()
            ;

        return $query;
    }

    public function associateRatings($series = []){
        $query = $this->createQueryBuilder('r')
            ->select('COALESCE(avg(r.value))')
            ->from('Rating', 'r')
            ->where('r.series IN (:series)')
            ->setParameter('series', $series)
            ->getQuery()
            ->getResult();
        dump($query); die;
        return null;
    }

    public function getRandomSeries($genre, $limit = 4){
        return $this->createQueryBuilder('s')
        ->select('s, r')
        ->join('s.externalRating', 'r')
        ->join('s.genre', 'g')
        ->where('g.name = :genre')
        ->setParameter('genre', $genre)
        ->setFirstResult(2) // demander
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult();
    }
}