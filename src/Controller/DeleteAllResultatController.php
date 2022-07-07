<?php

namespace App\Controller;

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
    public function __construct(ResultatRepository $resultatRepository, UserRepository $userRepository )
    {
        $this->resultatRepository = $resultatRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/tout-supprimer", name="app_sup_tout")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function index( ): Response
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
            $user = $this->userRepository->find($value->getUser()->getid());
            $user->setIsValid(false);
            $entityManager->persist($user);
            $entityManager->remove($value);
            // dd($user, $value);
        }
        $entityManager->flush();
        $this->addFlash('success', 'tous les resultats ont été  supprimés');
        return $this->redirectToRoute('app_resultat_index', [], Response::HTTP_SEE_OTHER);
    }
}
