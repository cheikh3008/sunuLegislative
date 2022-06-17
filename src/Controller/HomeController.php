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
        // RetenusRepository $retenusRepository,
        ChartBuilderInterface $chartBuilder
    ) {
        $this->departementRepository = $departementRepository;
        $this->resultatRepository = $resultatRepository;
        // $this->retenusRepository = $retenusRepository;
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
        // $retenus = $this->retenusRepository->findAll();
        $nbInscrit = 0;
        $nbVotant = 0;
        $bulletinNull = 0;
        $bulletinExp = 0;
        $nombreTotalBV = 0;
        $nombreResultatBV = $this->resultatRepository->findBy([], ['id' => 'DESC']);
        $nombreBVCirconscription = $this->resultatRepository->findNombreBureauVoteCoalition();
        $nb = [];
        $nbTotalVoixPourCoalition = [];
        $nomCoalition  = [];
        $nbVoix  = [];
        // dd($nbVoix);
        foreach ($this->resultatRepository->findNombreTotalVoix()[0] as $key => $value) {
            $nomCoalition[] = str_replace('_', ' ', $key);
            $nbVoix[] = $value;
            $nbTotalVoixPourCoalition[$key] = $value;
        }
        // foreach ($this->resultatRepository->findNombreBureauVoteCoalition() as $key => $value) {
        //     $nombreBVCir[$key] = $value;
        // }
        // dd($nombreBVCir);
        $taux = 0;
        $resultDprt = [];

        foreach ($this->resultatRepository->findByDepartement() as $key => $ddd) {
            $resultDprt[] = $ddd;
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
        // dd($resultDprt);
        return $this->render('home/index.html.twig', [
            'nbInscrit' => number_format($nbInscrit, 0, '.', ' '),
            'nbVotant' => number_format($nbVotant, 0, '.', ' '),
            'bulletinExp' => number_format($bulletinExp, 0, '.', ' '),
            'nombreTotalBV' => number_format($nombreTotalBV, 0, '.', ' '),
            'bulletinNull' => number_format($bulletinNull, 0, '.', ' '),
            'taux' => number_format($taux, 2),
            // 'retenus' => $retenus,
            'nb' => $nb,
            'resultDprt' => $resultDprt,
            'chartBar' => $this->getChartBar($nomCoalition, $nbVoix),
            'chartLine' => $this->getChartLine($nomCoalition, $nbVoix),
            'nomCoalition' => $nomCoalition,
            'nbTotalVoixPourCoalition' => $nbTotalVoixPourCoalition,
            'nombreBVCirconscription' => $nombreBVCirconscription,
            'nombreResultatBV' => $nombreResultatBV
        ]);
    }

    public function getChartBar($data, $datas)
    {
        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);

        $chart->setData([
            'labels' => $data,
            'datasets' => [
                [
                    'label' => 'Resulstats  par coaltion sous forme de diagramme en barre',
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
