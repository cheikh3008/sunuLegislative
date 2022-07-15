<?php

namespace App\Controller;

use App\Entity\Coalition;
use Twilio\Rest\Client;
use App\Entity\Resultat;
use App\Form\ResultatType;
use App\Entity\ResultatCoalition;
use App\Repository\UserRepository;
use App\Repository\ResultatRepository;
use App\Repository\CoalitionRepository;
use App\Repository\BureauVoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\ResultatCoalitionRepository;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/resultat")
 */
class ResultatController extends AbstractController
{
    private $session;
    private $coalitionRepository;
    public function __construct(SessionInterface $session, CoalitionRepository $coalitionRepository)
    {
        $this->session = $session;
        $this->coalitionRepository = $coalitionRepository;
    }

    /**
     * @Route("/", name="app_resultat_index", methods={"GET"})
     * @Security("is_granted('ROLE_REPRESENTANT')")
     */
    public function index(ResultatRepository $resultatRepository, CoalitionRepository $coalitionRepository): Response
    {
        $role = $this->getUser()->getRoles()["0"];
        $user = $this->getUser();
        if ($role === "ROLE_REPRESENTANT") {
            $coalitions = $coalitionRepository->findBy([], []);
            $resultats_rep = $resultatRepository->findOneBy(["user" => $user], []);
            return $this->render('resultat/representant.html.twig', [
                'resultats' => $resultats_rep,
                'coalitions' => $coalitions,
            ]);
        }
    }

    /**
     * @Route("/new", name="app_resultat_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_REPRESENTANT")
     */
    public function new(Request $request, ResultatRepository $resultatRepository, BureauVoteRepository $bureauVoteRepository, EntityManagerInterface $manager): Response
    {
        $resultat = new Resultat();
        $form = $this->createForm(ResultatType::class);
        $form->handleRequest($request);
        $userConnected = $this->getUser();
        $resultats = $resultatRepository->findBy(['user' => $userConnected]);
        $coalitions = $this->coalitionRepository->findAll();
        // dd($resultats);
        $dataForm = $this->session->get("dataForm", []);
        $Bv = $bureauVoteRepository->findOneBy(["slug" => $userConnected->getBV()->getSlug()]);
        if ($Bv !== $userConnected->getBV()) {
            throw new AccessDeniedException("Permission non accordé !");
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $resultat->setUser($userConnected);
            foreach ($resultats as $value) {
                if ($value->getUser() === $resultat->getUser()) {
                    $this->addFlash('error', "Le bureau de vote " .  $resultat->getUser()->getBV()->getNomBV() . ' a dèja saisi ces résultats ');
                    return $this->redirectToRoute('app_resultat_new', [], Response::HTTP_SEE_OTHER);
                }
            }
            $data = $form->getData();
            $this->session->set('data_session', $data);
            $resultat->setNbVotant($data['nbVotant'])
                ->setBulletinNull($data['bulletinNull'])
                ->setBulletinExp($data['bulletinExp']);
            $manager->persist($resultat);
            unset($data["nbVotant"]);
            unset($data["bulletinNull"]);
            unset($data["bulletinExp"]);
            foreach ($data as $key => $value) {
                $res[] = [
                    "nom" => $this->coalitionRepository->findOneBy(["slug" => $key]),
                    "nombre" => $value,

                ];
                $res_coal = new ResultatCoalition();
                $res_coal->setResulat($resultat);
                foreach ($res as $key => $value) {

                    $res_coal->setCoaltion($value['nom']);
                    $res_coal->setNombre($value['nombre']);
                }

                $manager->persist($res_coal);
            }

            $this->addFlash('success', "Veuillez saisir à nouveau les résultats");
            return $this->redirectToRoute('app_resultat_new_add', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('resultat/new.html.twig', [
            'resultat' => $resultat,
            'form' => $form,
            'dataForm' => $dataForm
        ]);
    }

    /**
     * @Route("/new-add", name="app_resultat_new_add", methods={"GET", "POST"})
     * @IsGranted("ROLE_REPRESENTANT")
     */
    public function new_add(Request $request,  BureauVoteRepository $bureauVoteRepository, EntityManagerInterface $manager, UserRepository $userRepository): Response
    {

        $resultat = new Resultat();
        $form = $this->createForm(ResultatType::class);
        $form->handleRequest($request);
        $userConnected = $this->getUser();
        $resultat_session = $this->session->get("data_session", []);
        $user = $userRepository->find($this->getUser()->getId());
        // dd($user);
        $Bv = $bureauVoteRepository->findOneBy(["slug" => $userConnected->getBV()->getSlug()]);
        if ($Bv !== $userConnected->getBV()) {
            throw new AccessDeniedException("Permission non accordé !");
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $resultat->setUser($userConnected);

            if ($resultat_session != $data) {
                $this->addFlash('error', "Les résultats ne sont pas les mêmes !");
                return $this->redirectToRoute('app_resultat_new', [], Response::HTTP_SEE_OTHER);
            } else {
                $resultat->setNbVotant($data['nbVotant'])
                    ->setBulletinNull($data['bulletinNull'])
                    ->setBulletinExp($data['bulletinExp']);
                $resultat->setUser($userConnected);
                $manager->persist($resultat);
                unset($data["nbVotant"]);
                unset($data["bulletinNull"]);
                unset($data["bulletinExp"]);
                foreach ($data as $key => $value) {
                    $res[] = [
                        "nom" => $this->coalitionRepository->findOneBy(["slug" => $key]),
                        "nombre" => $value,

                    ];
                    $res_coal = new ResultatCoalition();
                    $res_coal->setResulat($resultat);
                    foreach ($res as $key => $value) {

                        $res_coal->setCoaltion($value['nom']);
                        $res_coal->setNombre($value['nombre']);
                    }

                    $manager->persist($res_coal);
                }
                $manager->persist($user->setIsValid(true));
                $manager->flush();
                $this->session->remove("data_session");
                $this->addFlash('success', "Les résultats ont été ajoutés avec succès");
                return $this->redirectToRoute('app_resultat_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->renderForm('resultat/new-add.html.twig', [
            'resultat' => $resultat,
            'form' => $form,
            'resultat_session' => $resultat_session
        ]);
    }

    // /**
    //  * @Route("/{id}/edit", name="app_resultat_edit", methods={"GET", "POST"})
    //  * @Security("is_granted('ROLE_REPRESENTANT') && resultat.getUser() == user")
    //  */
    // public function edit(Request $request, Resultat $resultat, ResultatRepository $resultatRepository): Response
    // {
    //     $form = $this->createForm(ResultatType::class, $resultat);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $resultatRepository->add($resultat, true);

    //         return $this->redirectToRoute('app_resultat_index', [], Response::HTTP_SEE_OTHER);
    //     }

    //     return $this->renderForm('resultat/edit.html.twig', [
    //         'resultat' => $resultat,
    //         'form' => $form,
    //     ]);
    // }

    /**
     * @Route("/{id}/delete", name="app_resultat_delete")
     * @Security("is_granted('ROLE_REPRESENTANT') && resultat.getUser() == user")
     */
    public function delete(Resultat $resultat): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($resultat);
        $entityManager->flush();
        $this->addFlash('success', 'Votre resultat a été bien supprimé');
        return $this->redirectToRoute('app_resultat_index', [], Response::HTTP_SEE_OTHER);
    }
}
