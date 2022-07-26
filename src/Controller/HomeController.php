<?php

namespace App\Controller;


use Symfony\UX\Chartjs\Model\Chart;
use App\Repository\ResultatRepository;
use App\Repository\CoalitionRepository;
use App\Repository\BureauVoteRepository;
use App\Repository\DepartementRepository;
use App\Repository\UserRepository;
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
    private $userRepository;
    public function __construct(
        DepartementRepository $departementRepository,
        ResultatRepository $resultatRepository,
        ChartBuilderInterface $chartBuilder,
        BureauVoteRepository $bureauVoteRepository,
        CoalitionRepository $coalitionRepository,
        UserRepository $userRepository
    ) {
        $this->departementRepository = $departementRepository;
        $this->resultatRepository = $resultatRepository;
        $this->chartBuilder = $chartBuilder;
        $this->bureauVoteRepository = $bureauVoteRepository;
        $this->coalitionRepository = $coalitionRepository;
        $this->userRepository = $userRepository;
    }
    /**
     * @Route("/", name="app_home")
     * @IsGranted("ROLE_ADMIN")
     */

    public function index(): Response

    {
        $departements = $this->departementRepository->findAll();
        $resultats =  $this->resultatRepository->findBy([], ['user' => 'DESC']);
        $nbTotalElecteurDepartement = $this->resultatRepository->findNombreTotalElecteursDepartement();
        $resultatsCoalitionParDepartement = $this->resultatRepository->findTotalNbresultatParDepartement();
        $resultatsCoalitionCommune = $this->resultatRepository->findTotalNbresultatParCommune();
        $coalitions = $this->coalitionRepository->findBy([], ['nom' => 'ASC']);
        foreach ($coalitions as $key => $value) {

            $tt[] = $value->getResultatCoalitions()->toArray();
        }
        // dd($tt);
        $nbInscrit = 0;
        $nbVotant = 0;
        $bulletinNull = 0;
        $bulletinExp = 0;
        $nombreTotalBV = $this->resultatRepository->findNombreBureauVoteTotal()[0];
        // dd($nombreTotalBV);
        $nombreResultatBV = $this->resultatRepository->findBy([], ['id' => 'DESC']);
        $nombreBVCirconscription = $this->resultatRepository->findNombreBureauVoteCoalition();
        $man = $this->resultatRepository->findNombreResultBureauVoteCoalition();
        $findNombreTotalVoix =  $this->resultatRepository->findNombreTotalVoix();
        $nbResultatBVCir = $this->resultatRepository->findNombreResultBureauVoteParDepartement();
        $nb = [];
        $nbVoix = [];
        $nom_de_la_coaltion = [];
        $taux = 0;
        $nbVoixByCoalitionBycirconscription = [];
        $nbVoixByCoalitionByCommune = $this->resultatRepository->findByCommune();
        foreach ($this->resultatRepository->findByCirconscription() as $key => $value) {
            $nbVoixByCoalitionBycirconscription[] = $value;
        }
        // dd($this->resultatRepository->findNombreTotalVoix());
        foreach ($findNombreTotalVoix as $key => $value) {
            $nbVoix[$key] = $value['nbVoix'];
            $nom_de_la_coaltion[$key] = $value['nom'];
        }
        foreach ($departements as  $value) {
            $nbInscrit += 0;
        }
        foreach ($resultats as  $res) {
            $nbVotant += $res->getNbVotant();
            $bulletinNull += $res->getBulletinNull();
            $bulletinExp += $res->getBulletinExp();
        }
        if (!empty((int)$nombreTotalBV['nbElecteur'])) {
            $taux = $nbVotant / (int)$nombreTotalBV['nbElecteur'] * 100;
        }

        return $this->render('home/index.html.twig', [
            'nbInscrit' => number_format($nbInscrit, 0, ' ', ' '),
            'nbVotant' => number_format($nbVotant, 0, '.', ' '),
            'bulletinExp' => number_format($bulletinExp, 0, '.', ' '),
            'nombreTotalBV' => $nombreTotalBV,
            'bulletinNull' => number_format($bulletinNull, 0, '.', ' '),
            'taux' => number_format($taux, 2),
            'nb' => $nb,
            'chartBar' => $this->getChartBar($nom_de_la_coaltion, $nbVoix),
            'nombreBVCirconscription' => $nombreBVCirconscription,
            'nombreResultatBV' => $nombreResultatBV,
            'man' => $man,
            'coalitions' => $coalitions,
            'findNombreTotalVoix' => $findNombreTotalVoix,
            'resultats' => $resultats,
            'nbTotalResultatBureauObtenus' => count($resultats),
            'nbVoixByCoalitionBycirconscription' => $nbVoixByCoalitionBycirconscription,
            'nbVoixByCoalitionByCommune' => $nbVoixByCoalitionByCommune,
            'nbResultatBVCir' => $nbResultatBVCir,
            'resultatsCoalitionParDepartement' => $resultatsCoalitionParDepartement,
            'resultatsCoalitionCommune' => $resultatsCoalitionCommune,
            'nbTotalElecteurDepartement' => $nbTotalElecteurDepartement
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

    /**
     * @Route("/resultats-bureau-de-vote", name="app_resultats_bureau_de_vote")
     * @IsGranted("ROLE_ADMIN")
     */

    public function getResultatParBureauVote()
    {
        $resultats = $this->resultatRepository->findBy([], ['user' => 'DESC']);
        $coalitions = $this->coalitionRepository->findAll();
        return $this->render('home/resultats-bv.html.twig', [
            'resultats' => $resultats,
            'coalitions' => $coalitions,
        ]);
    }

    /**
     * @Route("/resultats-communes", name="app_resultats_communes")
     * @IsGranted("ROLE_ADMIN")
     */

    public function getResultatParCommune()
    {
        $resultatsCoalitionCommune = $this->resultatRepository->findTotalNbresultatParCommune();
        $coalitions = $this->coalitionRepository->findBy([], ['nom' => 'ASC']);
        $nbVoixByCoalitionByCommune = $this->resultatRepository->findByCommune();
        $nbTotalElecteurCommune = $this->resultatRepository->findNombreTotalElecteursCommune();
        return $this->render('home/resultats-communes.html.twig', [
            'coalitions' => $coalitions,
            'resultatsCoalitionCommune' => $resultatsCoalitionCommune,
            'nbVoixByCoalitionByCommune' => $nbVoixByCoalitionByCommune,
            'nbTotalElecteurCommune' => $nbTotalElecteurCommune
        ]);
    }

    /**
     * @Route("/resultats-lieu-de-vote", name="app_resultats_lieu_vote")
     * @IsGranted("ROLE_ADMIN")
     */

    public function getResultatLieuVote()

    {
        $coalitions = $this->coalitionRepository->findBy([], ['nom' => 'ASC']);
        $resultatsParLieuVote  = $this->resultatRepository->findTotalNbresultatParLieuVote();
        $nbVoixByCoalitionByLieuVote = $this->resultatRepository->findNombreTotalElecteursLieuVote();
        return $this->render('home/resultats-lv.html.twig', [
            'resultatsParLieuVote' => $resultatsParLieuVote,
            'coalitions' => $coalitions,
            'nbVoixByCoalitionByLieuVote' => $nbVoixByCoalitionByLieuVote
        ]);
    }
}
