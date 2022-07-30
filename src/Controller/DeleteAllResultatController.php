<?php

namespace App\Controller;

use App\Repository\BureauVoteRepository;
use App\Repository\ResultatRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DeleteAllResultatController extends AbstractController
{
    private $resultatRepository;
    private $userRepository;
    private $bureauVoteRepository;
    public function __construct(ResultatRepository $resultatRepository, UserRepository $userRepository, BureauVoteRepository $bureauVoteRepository)
    {
        $this->resultatRepository = $resultatRepository;
        $this->userRepository = $userRepository;
        $this->bureauVoteRepository = $bureauVoteRepository;
    }

    /**
     * @Route("/tout-supprimer", name="app_sup_tout")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function index(): Response
    {

        return $this->renderForm('delete_all_resultat/index.html.twig');
    }

    /**
     * @Route("/delete-all", name="delete_all")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function deleteAll()
    {
        $resultats = $this->resultatRepository->findAll();
        $entityManager = $this->getDoctrine()->getManager();
        foreach ($resultats as $key => $value) {
            $bv = $this->bureauVoteRepository->find($value->getUser()->getBV()->getId());
            $bv->setIsValid(false);
            $user = $this->userRepository->find($value->getUser()->getid());

            if ($value->getUser()->getRole()->getLibelle() == 'ROLE_JOURNALISTE') {
                $user->setBV(null);
                $user->setCommune(null);
            }
            $entityManager->persist($user);
            $entityManager->remove($value);
            $bv->setIsValid(false);
            $entityManager->persist($bv);
            $entityManager->flush();
        }
        $entityManager->flush();
        $this->addFlash('success', 'Tous les resultats ont été  supprimés');
        return $this->redirectToRoute('app_resultats_bureau_de_vote', [], Response::HTTP_SEE_OTHER);
    }
}
