<?php

namespace App\Controller;

use Exception;
use App\Entity\User;
use App\Entity\Upload;
use App\Form\UserType;
use App\Entity\SendSMS;
use App\Form\UploadType;
use App\Form\SendSMSType;
use App\Entity\SendMessageAll;
use App\Entity\SendMessageOne;
use App\Entity\SendIdentifiant;
use App\Form\SendMessageAllType;
use App\Form\SendMessageOneType;
use App\Form\SendIdentifiantType;
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
            $data = $form->getData();
            $user->setUsername($data->getTelephone());
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
     * @Route("/send-identifiant-all", name="app_user_sendsms_all")
     */
    public  function sendSMSAllUSer()
    {
        $users = $this->userRepository->findAll();
        $datas = [];
        foreach ($users as $value) {

            $datas[] = $value;
            $telephone = '221' . $value->getTelephone();
            $nom = strtoupper($value->getNom());
            $username = $value->getUsername();
            $uiid = $value->getUuid();
            $prenom = strtoupper($value->getPrenom()[0]);
            $message = ("Bonjour $prenom. $nom, votre  identifiant de connexion est : $username, votre Mot de passe : $uiid \r\nLe lien de la plateforme : www.sunulegislatives2022.com");

            $this->getSMS($telephone, $message);
        }
        $this->addFlash('success', "Les identifiants de connexion ont été envoyés  à tous les représentants .");
        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);


        // return $this->render('user/sms-all.html.twig', []);
    }

    /**
     * @Route("/send-identifiant-one", name="app_user_sendsms_one")
     */
    public  function sendSMSOneUSer(Request $request)
    {
        $sms = new SendIdentifiant();
        $form = $this->createForm(SendIdentifiantType::class, $sms);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $tel = $data->getTelephone();
            $user = $this->userRepository->findOneBy(["uuid" => $tel]);
            $nom = strtoupper($user->getNom());
            $username = $user->getUsername();
            $uiid = $user->getUuid();
            $prenom = strtoupper($user->getPrenom()[0]);
            $message = ("Bonjour $prenom. $nom, votre  identifiant de connexion est : $username, votre Mot de passe : $uiid \r\nLe lien de la plateforme : www.sunulegislatives2022.com");
            $this->getSMS('221' . $user->getTelephone(), $message);
            $this->addFlash('success', "Les identifiants de connexion ont été envoyé à " . $user->getFullname());
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('user/sms-one.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/send-message-one", name="app_user_send_message_one")
     */
    public  function sendMessage(Request $request)
    {
        $sms = new SendMessageOne();
        $form = $this->createForm(SendMessageOneType::class, $sms);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $tel = $data->getTelephone();
            $user = $this->userRepository->findOneBy(["uuid" => $tel]);
            $message = $data->getMessage();
            $this->getSMS('221' . $user->getTelephone(), $message);
            $this->addFlash('success', "Votre message a été bien envoyé à " . $user->getFullname());
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('user/sms-message-one.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/send-message-all", name="app_user_send_message_all")
     */
    public  function sendMessageAll(Request $request)
    {
        $sms = new SendMessageAll();
        $form = $this->createForm(SendMessageAllType::class, $sms);
        $users = $this->userRepository->findAll();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $message = $data->getMessage();
            foreach ($users as $value) {
                $telephone = '221' . $value->getTelephone();

                $this->getSMS($telephone, $message);
            }
            $this->addFlash('success', "Votre message a été bien envoyé à tous les représentants ");
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('user/sms-message-all.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function getSMS($to, $message)

    {
        $gateway_url = "https://sms.lws.fr/sms/api";
        $action = "send-sms";
        $apiKey  = "Y2hlaWtoOiQyeSQxMCRoa1FrRHNwZmp1THpUanROVUViRjEuY0ovRUV2UzdWaGQxZExQWndPT3J5ZkRGQUNkdTJxaQ==";
        $senderID  = "SN2022";
        $data = array(
            'action' => $action,
            'api_key' => $apiKey,
            'to' => $to,
            'from' => $senderID,
            'sms' => ($message),
        );
        $ch = curl_init($gateway_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $get_data = json_decode($response, true);
        if ($get_data['code'] === 'ok') {

            echo ('<div class= "alert alert-success">Le message a bien été envoyé</div>');
        } else {

            echo ('<div class = "alert alert-danger">Message non envoyé !</div>');
        }
    }
}
