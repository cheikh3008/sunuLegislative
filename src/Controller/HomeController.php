<?php

namespace App\Controller;

use App\Entity\Test;
use App\Entity\Upload;
use App\Form\UploadType;
use App\Entity\Departement;
use Symfony\UX\Chartjs\Model\Chart;
use App\Repository\RetenusRepository;
use App\Repository\ResultatRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DepartementRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    private $departementRepository;
    private $resultatRepository;
    private $retenusRepository;
    private $chartBuilder;
    public function __construct(
        DepartementRepository $departementRepository,
        ResultatRepository $resultatRepository,
        RetenusRepository $retenusRepository,
        ChartBuilderInterface $chartBuilder
    ) {
        $this->departementRepository = $departementRepository;
        $this->resultatRepository = $resultatRepository;
        $this->retenusRepository = $retenusRepository;
        $this->chartBuilder = $chartBuilder;
    }
    /**
     * @Route("/", name="app_home")
     * @IsGranted("ROLE_ADMIN")
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
        $nbCol = [];
        $dataRetenusForChart = [];
        $dataResultForchard = [];
        $taux = 0;
        $nomdprt = [];
        $resultDprt = [];

        foreach ($this->resultatRepository->findByDepartement() as $key => $ddd) {
            $resultDprt[] = $ddd;
        }
        // foreach ($retenus as $value) {
        //     $nbCol[$value->getNom()] = $value->getResultats()->toArray();

        //     foreach ($nbCol as  $val) {
        //         $tt = 0;
        //         foreach ($val as $key => $dd) {
        //             $tt += $dd->getNbInscrit();
        //         }
        //         $dataResultForchard[$value->getNom()] = $tt;
        //     }
        // }
        // dd($dataResultForchard);
        // foreach ($retenus as $value) {
        //     $nb[$value->getNom()] = $value->getResultats()->toArray();
        //     $dataRetenusForChart[] = $value->getNom();
        // }

        foreach ($departements as  $value) {
            $nbInscrit += $value->getNbInscrit();
            $nbBVvote += $value->getNbBV();
        }
        foreach ($resultats as  $res) {
            $nbVotant += $res->getNbVotant();
            $bulletinNull += $res->getBulletinNull();
            $bulletinExp += $res->getBulletinExp();
        }
        if ($nbVotant && $nbInscrit) {

            $taux = $nbVotant / $nbInscrit * 100;
        }
        // dd($resultDprt);
        return $this->render('home/index.html.twig', [
            'nbInscrit' => $nbInscrit,
            'nbVotant' => $nbVotant,
            'bulletinExp' => $bulletinExp,
            'nbBVvote' => $nbBVvote,
            'bulletinNull' => $bulletinNull,
            'taux' => number_format($taux, 2),
            'retenus' => $retenus,
            'nb' => $nb,
            'resultDprt' => $resultDprt,
            'chartBar' => $this->getChartBar($dataRetenusForChart, $dataResultForchard),
            'chartLine' => $this->getChartLine($dataRetenusForChart, $dataResultForchard),
            // 'nbVoix' => $this->resultatRepository->findOneBySomeField()
            'nbVoix' => []
        ]);
    }

    public function getChartBar($data = [], $datas = [])
    {
        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);

        $chart->setData([
            'labels' => $data,
            'datasets' => [
                [
                    'label' => 'Resulstats  par coaltion sous forme de diagramme en barre',
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(255, 205, 86, 0.2)',
                        'rgba(75, 192, 5, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(201, 203, 207, 0.2)'
                    ],
                    'borderColor' => [
                        'rgb(255, 99, 132)',
                        'rgb(255, 159, 64)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 5)',
                        'rgb(54, 162, 235)',
                        'rgb(153, 102, 255)',
                        'rgb(201, 203, 207)'
                    ],
                    'borderWidth' => '1',
                    'data' => $datas,
                ],

            ],
        ]);

        $chart->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => 100,
                ],
            ],
        ]);

        return $chart;
    }
    public function getChartLine($data = [], $datas = [])
    {
        $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);

        $chart->setData([
            'labels' => $data,
            'datasets' => [
                [
                    'label' => 'Resulstats  par coaltion sous forme de courbe d\'évolution ',
                    'backgroundColor' => 'rgb(255, 99, 132)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => $datas,
                ],
            ],
        ]);

        $chart->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => 100,
                ],
            ],
        ]);

        return $chart;
    }
}
