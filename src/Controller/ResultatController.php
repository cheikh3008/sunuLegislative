<?php

namespace App\Controller;

use Twilio\Rest\Client;
use App\Entity\Resultat;
use App\Form\ResultatType;
use App\Repository\UserRepository;
use App\Repository\ResultatRepository;
use App\Repository\BureauVoteRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @Route("/", name="app_resultat_index", methods={"GET"})
     */
    public function index(ResultatRepository $resultatRepository): Response
    {
        return $this->render('resultat/index.html.twig', [
            'resultats' => $resultatRepository->findBy([], ['id' => 'DESC']),
        ]);
    }

    /**
     * @Route("/new", name="app_resultat_new", methods={"GET", "POST"})
     *  @IsGranted("ROLE_REPRESENTANT")
     */
    public function new(Request $request, ResultatRepository $resultatRepository, BureauVoteRepository $bureauVoteRepository): Response
    {
        $resultat = new Resultat();
        $form = $this->createForm(ResultatType::class, $resultat);
        $form->handleRequest($request);
        $userConnected = $this->getUser();
        $resultats = $resultatRepository->findBy(['user' => $userConnected]);
        // dd($resultats);
        $dataForm = $this->session->get("dataForm", []);
        $Bv = $bureauVoteRepository->findOneBy(["nomBV" => $userConnected->getBV()->getNomBV()]);
        if ($Bv !== $userConnected->getBV()) {
            throw new AccessDeniedException("Permission non accordé !");
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $resultat->setUser($userConnected);
            foreach ($resultats as $value) {
                if ($value->getRetenus() === $resultat->getRetenus() && $value->getUser() === $resultat->getUser()) {
                    $this->addFlash('error', "Résultats déja ajoutés pour " .  $resultat->getRetenus());
                    return $this->redirectToRoute('app_resultat_new', [], Response::HTTP_SEE_OTHER);
                }
            }
            $this->session->set("dataForm", $data);
            // $resultatRepository->add($resultat, true);

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
     *  @IsGranted("ROLE_REPRESENTANT")
     */
    public function new_add(Request $request, ResultatRepository $resultatRepository, BureauVoteRepository $bureauVoteRepository): Response
    {

        $resultat = new Resultat();
        $form = $this->createForm(ResultatType::class, $resultat);
        $form->handleRequest($request);
        $userConnected = $this->getUser();
        $dataForm = $this->session->get("dataForm", []);
        $Bv = $bureauVoteRepository->findOneBy(["nomBV" => $userConnected->getBV()->getNomBV()]);
        if ($Bv !== $userConnected->getBV()) {
            throw new AccessDeniedException("Permission non accordé !");
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $resultat->setUser($userConnected);
            
            if (
                $dataForm->getRetenus()->getId() !== $resultat->getRetenus()->getId() ||
                (int)$dataForm->getNbInscrit() !== (int)$resultat->getNbInscrit() ||
                    (int)$dataForm->getNbVotant()  !== (int)$resultat->getNbVotant() ||
                    (int)$dataForm->getBulletinnull() !== (int)$resultat->getBulletinnull() || (int)$dataForm->getBulletinExp() !== (int)$resultat->getBulletinExp()
            ) {
                // dd('error');
                $this->addFlash('error', "Les résultats ne sont pas les mêmes !");
                return $this->redirectToRoute('app_resultat_new', [], Response::HTTP_SEE_OTHER);
            } else {
                // dd('ok');
                $resultatRepository->add($resultat, true);
                $this->session->remove("dataForm");
                $this->addFlash('success', "Les résultats ont été ajoutés avec succés");
                return $this->redirectToRoute('app_resultat_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->renderForm('resultat/new-add.html.twig', [
            'resultat' => $resultat,
            'form' => $form,
            'dataForm' => $dataForm
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_resultat_edit", methods={"GET", "POST"})
     *  @IsGranted("ROLE_REPRESENTANT")
     */
    public function edit(Request $request, Resultat $resultat, ResultatRepository $resultatRepository): Response
    {
        $form = $this->createForm(ResultatType::class, $resultat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $resultatRepository->add($resultat, true);

            return $this->redirectToRoute('app_resultat_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('resultat/edit.html.twig', [
            'resultat' => $resultat,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/delete", name="app_resultat_delete")
     * 
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
