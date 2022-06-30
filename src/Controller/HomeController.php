<?php

namespace App\Controller;


use Symfony\UX\Chartjs\Model\Chart;
use App\Repository\ResultatRepository;
use App\Repository\CoalitionRepository;
use App\Repository\BureauVoteRepository;
use App\Repository\DepartementRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    private $departementRepository;
    private $resultatRepository;
    private $bureauVoteRepository;
    private $chartBuilder;
    private $coalitionRepository;
    public function __construct(
        DepartementRepository $departementRepository,
        ResultatRepository $resultatRepository,
        ChartBuilderInterface $chartBuilder,
        BureauVoteRepository $bureauVoteRepository,
        CoalitionRepository $coalitionRepository
    ) {
        $this->departementRepository = $departementRepository;
        $this->resultatRepository = $resultatRepository;
        $this->chartBuilder = $chartBuilder;
        $this->bureauVoteRepository = $bureauVoteRepository;
        $this->coalitionRepository = $coalitionRepository;

    }
    /**
     * @Route("/", name="app_home")
     * @IsGranted("ROLE_ADMIN")
     */

    public function index(): Response

    {
        $departements = $this->departementRepository->findAll();
        $resultats =  $this->resultatRepository->findAll();
        $coalitions = $this->coalitionRepository->findBy([], []);
        $nbInscrit = 0;
        $nbVotant = 0;
        $bulletinNull = 0;
        $bulletinExp = 0;
        $nombreTotalBV = 0;
        $nombreResultatBV = $this->resultatRepository->findBy([], ['id' => 'DESC']);
        $nombreBVCirconscription = $this->resultatRepository->findNombreBureauVoteCoalition();
        $man = $this->resultatRepository->findNombreResultBureauVoteCoalition();
        $findNombreTotalVoix =  $this->resultatRepository->findNombreTotalVoix() ;       
        $nb = [];
        $nbVoix = [];
        $nom_de_la_coaltion = [];
        $taux = 0;
        $resultDprt = [];

        foreach ($this->resultatRepository->findByDepartement() as $key => $ddd) {
            $resultDprt[] = $ddd;
        }
        // dd($this->resultatRepository->findNombreTotalVoix());
        foreach ($findNombreTotalVoix as $key => $value) {
            $nbVoix [$key] = $value['nbVoix'];
            $nom_de_la_coaltion [$key] = $value['nom'];
        }
        foreach ($departements as  $value) {
            $nbInscrit += $value->getNbInscrit();
            $nombreTotalBV += $value->getNbBV();
        }
        foreach ($resultats as  $res) {
            $nbVotant += $res->getNbVotant();
            $bulletinNull += $res->getBulletinNull();
            $bulletinExp += $res->getBulletinExp();
        }
        if ($nbVotant && $nbInscrit) {
            $taux = $nbVotant / $nbInscrit * 100;
        }
        // foreach ($coalitions as $key => $value) {
        //     $nom_de_la_coaltion [] = $value->getNom();
        // }
        return $this->render('home/index.html.twig', [
            'nbInscrit' => number_format($nbInscrit, 0, '.', ' '),
            'nbVotant' => number_format($nbVotant, 0, '.', ' '),
            'bulletinExp' => number_format($bulletinExp, 0, '.', ' '),
            'nombreTotalBV' => number_format($nombreTotalBV, 0, '.', ' '),
            'bulletinNull' => number_format($bulletinNull, 0, '.', ' '),
            'taux' => number_format($taux, 2),
            'nb' => $nb,
            'resultDprt' => $resultDprt,
            'chartBar' => $this->getChartBar($nom_de_la_coaltion, $nbVoix),
            'nombreBVCirconscription' => $nombreBVCirconscription,
            'nombreResultatBV' => $nombreResultatBV,
            'man' => $man,
            'coalitions' => $coalitions,
            'findNombreTotalVoix' => $findNombreTotalVoix,
            'resultats' => $resultats
        ]);
    }

    public function getChartBar($data = [], $datas = [])
    {
        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);

        $chart->setData([
            'labels' => $data,
            'datasets' => [
                [
                    'label' => 'Nombre de voix total de chaque Coalition
                    ',
                    'backgroundColor' => [
                        'rgb(255, 99, 132)',
                        'rgb(0, 128, 0)',
                        'rgb(255, 205, 86)',
                        'rgb(95,106,106)',
                        'rgb(54, 162, 235)',
                        'rgb(153, 102, 255)',
                        'rgb( 230, 126, 34 )',
                        'rgb(255, 0, 0)'

                    ],
                    'borderColor' => [
                        'rgb(255, 99, 132)',
                        'rgb(0, 128, 0)',
                        'rgb(255, 205, 86)',
                        'rgb(95,106,106)',
                        'rgb(54, 162, 235)',
                        'rgb(153, 102, 255)',
                        'rgb( 230, 126, 34 )',
                        'rgb(255, 0, 0)'

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
}
