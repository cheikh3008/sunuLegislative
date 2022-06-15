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
    private $userRepository;
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/", name="app_user_index", methods={"GET"})
     */
    public function index(RoleRepository $roleRepository): Response
    {
        $role = $roleRepository->findOneBy(['libelle' => 'ROLE_REPRESENTANT']);
        return $this->render('user/index.html.twig', [
            'users' => $this->userRepository->findBy(["role" => $role], ['id' => 'DESC']),
        ]);
    }

    /**
     * @Route("/new", name="app_user_new", methods={"GET", "POST"})
     */
    public function new(Request $request,  UserPasswordHasherInterface $userPasswordHasher, RoleRepository $roleRepository, BureauVoteRepository $bureauVoteRepository): Response
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
            $userRS = $this->userRepository->findOneBy(['BV' => $nomBV]);
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
            $this->userRepository->add($user, true);

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }


    /**
     * @Route("/{id}/edit", name="app_user_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userRepository->add($user, true);

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

    /**
     * @Route("/sms-all", name="app_user_sendsms_all")
     */
    public  function sendSMSAllUSer()
    {
        $users = $this->userRepository->findAll();
        // $telephone = [];
        // $uid = [];
        $data = [];
        $gateway_url = "https://sms.lws.fr/sms/api";
        $action = "send-sms";
        $apiKey  = "Y2hlaWtoOiQyeSQxMCRoa1FrRHNwZmp1THpUanROVUViRjEuY0ovRUV2UzdWaGQxZExQWndPT3J5ZkRGQUNkdTJxaQ";
        foreach ($users as $key => $value) {
            // $telephone = $value->getTelephone();
            // $uid[] = $value->getUuid();
            // $to = $telephone;
            // $senderID  = "LWS";
            // $message  = urlencode("Ceci est un message de test");
            $data = array(
                'action' => $action,
                'api_key' => $apiKey,
                'to' => $value->getTelephone(),
                'from' => 'lws',
                'sms' => 'Bonjour ' . $value->getFullname() . ', Vos identifiants de connexion sont:' . ' Username: ' . $value->getUsername() . '  Mot de passe: ' . $value->getUuid(),
            );
        }
        $ch = curl_init($gateway_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $get_data = json_decode($response, true);

        dd($data);

        return $this->render('user/sms-all.html.twig', []);
    }

    /**
     * @Route("/sms-one", name="app_user_sendsms_one")
     */
    public  function sendSMSOneUSer()
    {
        $users = $this->userRepository->findAll();
        foreach ($users as $value) {

            $message = urlencode('Bonjour ' . $value->getFullname() . ', Vos identifiants de connexion sont:' . ' Username: ' . $value->getUsername() . '  Mot de passe: ' . $value->getUuid());
            $username = 'SMS-473326';
            $password = 'tzfydejbwktakz';
            $expediteur = 'Cheikh3008';
            $destinataire = '221773043248';
            $sms = "
            https://sms.lws.fr/sms/api?action=send-sms&api_key=Y2hlaWtoOiQyeSQxMCRoa1FrRHNwZmp1THpUanROVUViRjEuY0ovRUV2UzdWaGQxZExQWndPT3J5ZkRGQUNkdTJxaQ==&to=$destinataire&from=lws&sms=$message
            ";
        }
        // dd($sms);
        if ($sms[0] != 'Error') {

            dd('votre sms est envoye');
        } else {
            dd('Erreur:' . $sms[0] . $sms[1]);
        }
        return $this->render('user/sms-all.html.twig', []);
    }
}
