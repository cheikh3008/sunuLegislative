<?php

namespace App\Controller;

use Exception;
use App\Entity\User;
use App\Entity\Upload;
use App\Form\UserType;
use App\Form\UploadType;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Repository\BureauVoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @Route("/user")
 * @IsGranted("ROLE_ADMIN")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="app_user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository, RoleRepository $roleRepository): Response
    {
        $role = $roleRepository->findOneBy(['libelle' => 'ROLE_REPRESENTANT']);
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findBy(["role" => $role], ['id' => 'DESC']),
        ]);
    }

    /**
     * @Route("/new", name="app_user_new", methods={"GET", "POST"})
     */
    public function new(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher, RoleRepository $roleRepository, BureauVoteRepository $bureauVoteRepository): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        $role = $roleRepository->findOneBy(["libelle" => "ROLE_REPRESENTANT"]);
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $password = substr(str_shuffle($chars), 0, 8);
        if ($form->isSubmitted() && $form->isValid()) {
            $username = ($form->get('telephone')->getData());
            $nomBV =  ($form->get('BV')->getData());
            // $bvExi = $bureauVoteRepository->findOneBy(['nomBV' => $nomBV]);
            $userRS = $userRepository->findOneBy(['BV' => $nomBV]);
            // dd($userRS);
            if ($userRS) {
                $this->addFlash('error', "Ce bureau a été dèja affecté par un représentant");
                return $this->redirectToRoute('app_user_new', [], Response::HTTP_SEE_OTHER);
            }
            $user->setRole($role);
            $user->setUuid($password);
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $password
                )
            );
            $user->setUsername($username);
            $userRepository->add($user, true);

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    // /**
    //  * @Route("/{id}", name="app_user_show", methods={"GET"})
    //  */
    // public function show(User $user): Response
    // {
    //     return $this->render('user/show.html.twig', [
    //         'user' => $user,
    //     ]);
    // }

    /**
     * @Route("/{id}/edit", name="app_user_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, User $user, UserRepository $userRepository): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->add($user, true);

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/delete", name="app_user_delete")
     */
    public function delete(User $user): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush();
        $this->addFlash('success', 'Votre utlisateur a été bien supprimé');
        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/add", name="app_user_add")
     */
    public function addBy(Request $request, EntityManagerInterface $entityManagerInterface, UserPasswordHasherInterface $userPasswordHasher, BureauVoteRepository $bureauVoteRepository, RoleRepository $roleRepository): Response

    {

        $upload = new Upload();
        $form = $this->createForm(UploadType::class, $upload);

        $form->handleRequest($request);
        $role = $roleRepository->findOneBy(["libelle" => "ROLE_REPRESENTANT"]);
        if ($form->isSubmitted() && $form->isValid()) {

            $fileName = $request->files->get("upload");
            $fileNamePath = $fileName['file']->getRealPath();
            $spreadsheet = IOFactory::load($fileNamePath);
            $data = $spreadsheet->getActiveSheet()->toArray();
            // dd($data);
            $count = "0";
            foreach ($data as $row) {
                // if (count($row) <= 3) {
                //     throw new Exception("Impossible d'importer ce fichier.");
                // }
                $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
                $password = substr(str_shuffle($chars), 0, 8);
                if ($count > 0) {
                    try {
                        $user = new User();
                        $nom = $row["0"];
                        $prenom  = $row["1"];
                        $telephone  = $row["2"];
                        $nomBV = $row["3"];
                        $nomBV1 = $bureauVoteRepository->findOneBy(['nomBV' => $nomBV]);

                        $user->setNom($nom)
                            ->setPrenom($prenom)
                            ->setUsername($telephone)
                            ->setTelephone($telephone)
                            ->setUuid($password)
                            ->setBV($nomBV1)
                            ->setRole($role)
                            ->setPassword(
                                $userPasswordHasher->hashPassword(
                                    $user,
                                    $password
                                )
                            );
                        $entityManagerInterface->persist($user);
                        $entityManagerInterface->flush();
                    } catch (\Throwable $th) {
                        // throw new Exception("Impossible d'importer ce fichier.");
                        $this->addFlash('error', "Impossible d'importer ce fichier.");
                        return $this->redirectToRoute('app_user_add', [], Response::HTTP_SEE_OTHER);
                    }
                } else {
                    $count = "1";
                }
            }
            $this->addFlash('success', 'Votre fichier a été importé avec succés');
            return $this->redirectToRoute('app_user_index');
        }
        return $this->render('user/add-user.html.twig', [
            'form' => $form->createView()
            // 'erreur' => $erreur,
        ]);
    }
}
