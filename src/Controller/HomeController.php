<?php

namespace App\Controller;

use App\Entity\Test;
use App\Entity\Upload;
use App\Form\UploadType;
use App\Entity\Departement;
use App\Repository\DepartementRepository;
use App\Repository\ResultatRepository;
use App\Repository\RetenusRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    private $departementRepository;
    private $resultatRepository;
    private $retenusRepository;
    public function __construct(
        DepartementRepository $departementRepository,
        ResultatRepository $resultatRepository,
        RetenusRepository $retenusRepository
    ) {
        $this->departementRepository = $departementRepository;
        $this->resultatRepository = $resultatRepository;
        $this->retenusRepository = $retenusRepository;
    }
    /**
     * @Route("/", name="app_home")
     */

    public function index(): Response

    {
        $departements = $this->departementRepository->findAll();
        $resultats =  $this->resultatRepository->findAll();
        $retenus = $this->retenusRepository->findAll();
        $nbInscrit = 0;
        $nbVotant = 0;
        $bulletinNull = 0;
        $bulletinExp = 0;
        $nbBVvote = 0;
        $nb = [];
        
        foreach ($retenus as $value) {
            $nb[$value->getNom()] = $value->getResultats()->toArray();
        }
        
        foreach ($departements as  $value) {
            $nbInscrit += $value->getNbInscrit();
            $nbBVvote += $value->getNbBV();
        }
        foreach ($resultats as  $res) {
            $nbVotant += $res->getNbVotant();
            $bulletinNull += $res->getBulletinNull();
            $bulletinExp += $res->getBulletinExp();
        }
        $taux = $nbVotant / $nbInscrit * 100;
        return $this->render('home/index.html.twig', [
            'nbInscrit' => $nbInscrit,
            'nbVotant' => $nbVotant,
            'bulletinExp' => $bulletinExp,
            'nbBVvote' => $nbBVvote,
            'bulletinNull' => $bulletinNull,
            'taux' => number_format($taux, 2),
            'retenus' => $retenus,
            'nb' => $nb
        ]);
    }
}
