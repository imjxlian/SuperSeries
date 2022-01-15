<?php

namespace App\Controller;

use App\Entity\Genre;
use App\Repository\SeriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'default')]
    public function index(SeriesRepository $sr, EntityManagerInterface $em): Response
    {
        $genreLimit = 4;

        $qb = $em->createQueryBuilder();
        $qb->select('count(g.id)')
            ->from('App\Entity\Genre','g');
        $genreCount = $qb->getQuery()->getSingleScalarResult();
        $genreIds = [];

        $i = 0;
        while($i <= $genreLimit){
            $randomNumber = rand(1 , $genreCount);
            if(!in_array($randomNumber, $genreIds)){
                $genreIds[$i] = rand(1 , $randomNumber);
                $i++;
            }
        }
        
        $qb = $em->createQueryBuilder();
        $genres = $qb->select('g.name')
        ->from('App\Entity\Genre','g')
        ->where('g.id IN (:genreIds)')
        ->setParameter('genreIds', $genreIds)
        ->getQuery()
        ->getResult();
        
        $series = [];
        foreach($genres as $genre){
            $series[$genre['name']] = $sr->getRandomSeries($genre['name']);
        }
        
        return $this->render('default/index.html.twig', [
            'seriesList' => $series,
        ]);
    }

    #[Route('/apropos', name: 'apropos')]
    public function apropos() : Response
    {
        return $this->render('default/apropos.html.twig', [
            'controller_name' => 'DefaultController',
        ]);
    }
}
