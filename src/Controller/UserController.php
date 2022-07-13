<?php

namespace App\Controller;

use Exception;
use App\Entity\User;
use App\Entity\Upload;
use App\Form\UserType;
use App\Entity\SendSMS;
use App\Form\UploadType;
use App\Form\SendSMSType;
use App\Form\UserTypeEdit;
use App\Entity\SendMessageAll;
use App\Entity\SendMessageOne;
use App\Entity\SendIdentifiant;
use libphonenumber\PhoneNumber;
use App\Form\SendMessageAllType;
use App\Form\SendMessageOneType;
use App\Form\SendIdentifiantType;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use libphonenumber\PhoneNumberUtil;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Repository\BureauVoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use libphonenumber\NumberParseException;
use App\Repository\DepartementRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @IsGranted("ROLE_ADMIN")
 * @Route("/user")
 */
class UserController extends AbstractController
{

    private $userRepository;
    private $slugger;
    public function __construct(UserRepository $userRepository, SluggerInterface $slugger)
    {
        $this->userRepository = $userRepository;
        $this->slugger = $slugger;
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
        $phoneUtil = PhoneNumberUtil::getInstance();
        $codes_choice = [];
        $regions = ($phoneUtil->getSupportedRegions());
        foreach ($regions as  $value) {
            $codes_choice[$value . " + " . $phoneUtil->getCountryCodeForRegion($value)] = $value;
        }
        $formOptions = array('codes' => $codes_choice);
        // dd($phoneUtil->getSupportedRegions());
        $user = new User();
        $form = $this->createForm(UserType::class, $user, $formOptions);
        $form->handleRequest($request);
        $usersAll = $this->userRepository->findAll();
        $role = $roleRepository->findOneBy(["libelle" => "ROLE_REPRESENTANT"]);
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $password = substr(str_shuffle($chars), 0, 8);
        if ($form->isSubmitted() && $form->isValid()) {
            $telephone =  $form->get('telephone')->getData();
            $code = $form->get('code')->getData();
            $phone = $phoneUtil->parse($telephone, $code);
            $number = $phone->getNationalNumber();
            $indicatif = $phone->getCountryCode();
            if (!$phoneUtil->isValidNumber($phone)) {
                $this->addFlash('error', 'Le numéro de téléphone n\'est pas valide !');
                return $this->redirectToRoute('app_user_new', [], Response::HTTP_SEE_OTHER);
            }
            $nomBV =  ($form->get('BV')->getData());
            // $bvExi = $bureauVoteRepository->findOneBy(['nomBV' => $nomBV]);
            $userRS = $this->userRepository->findOneBy(['BV' => $nomBV]);
            foreach ($usersAll as $key => $value) {
                // dd($value->getTelephone() === (int)$telephone);
                if ($value->getTelephone() === (int)($indicatif . $telephone)) {
                    $this->addFlash('error', 'ce numéro de téléphone existe dèja !');
                    return $this->redirectToRoute('app_user_new', [], Response::HTTP_SEE_OTHER);
                }
            }
            if ($userRS) {
                $this->addFlash('error', "Ce bureau a été dèja affecté par un représentant");
                return $this->redirectToRoute('app_user_new', [], Response::HTTP_SEE_OTHER);
            }
            $user->setRole($role);
            $user->setUuid($password);
            $user->setCode($code);
            $user->setTelephone(trim($indicatif . $telephone));
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $password
                )
            );

            $user->setUsername($number);
            $this->userRepository->add($user, true);
            $this->addFlash('success', 'Ce représentant a été bien ajouté');
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
        $usersAll = $this->userRepository->findAll();
        $phoneUtil = PhoneNumberUtil::getInstance();
        $codes_choice = [];
        $regions = ($phoneUtil->getSupportedRegions());
        foreach ($regions as  $value) {
            $codes_choice[$value . " + " . $phoneUtil->getCountryCodeForRegion($value)] = $value;
        }
        $formOptions = array('codes' => $codes_choice);

        $form = $this->createForm(UserType::class, $user, $formOptions);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $telephone =  $data->getTelephone();
            $code = $data->getCode();
            $phone = $phoneUtil->parse($telephone, $code);
            $indicatif = $phone->getCountryCode();
            $number = $phone->getNationalNumber();
            if (!$phoneUtil->isValidNumber($phone)) {
                $this->addFlash('error', 'Le numéro de téléphone n\'est pas valide !');
                return $this->redirectToRoute('app_user_edit', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
            }
            foreach ($usersAll as $key => $value) {
                if ($value->getTelephone() === (int)($indicatif . $telephone)) {
                    $this->addFlash('error', 'ce numéro de téléphone existe dèja !');
                    return $this->redirectToRoute('app_user_edit', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
                }
            }
            $user->setCode($code);
            $user->setTelephone(trim($indicatif . $number));
            $user->setUsername($number);
            $this->userRepository->add($user, true);
            $this->addFlash('success', 'Ce représentant a été bien modifié');
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
        $this->addFlash('success', 'Ce représentant a été bien supprimé');
        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/add", name="app_user_add")
     */
    public function addBy(Request $request, EntityManagerInterface $entityManagerInterface, UserPasswordHasherInterface $userPasswordHasher, BureauVoteRepository $bureauVoteRepository, RoleRepository $roleRepository, DepartementRepository $departementRepository): Response

    {

        $upload = new Upload();
        $form = $this->createForm(UploadType::class, $upload);
        $usersAll = $this->userRepository->findAll();
        $form->handleRequest($request);
        $phoneUtil = PhoneNumberUtil::getInstance();
        $role = $roleRepository->findOneBy(["libelle" => "ROLE_REPRESENTANT"]);
        if ($form->isSubmitted() && $form->isValid()) {

            $fileName = $request->files->get("upload");
            $fileNamePath = $fileName['file']->getRealPath();
            if ($fileName['file']->guessExtension() == "xlsx") {
                # code...
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx;
            }
            if ($fileName['file']->guessExtension() == "xls") {
                # code...
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls;
            }
            if ($fileName['file']->guessExtension() == "csv") {
                # code...
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv;
            }
            if ($fileName['file']->guessExtension() == "txt") {
                # code...
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv;
            }

            $spreadsheet = $reader->load($fileNamePath);
            $data = $spreadsheet->getActiveSheet()->toArray();
            // $spreadsheet = IOFactory::load($fileNamePath);
            // $data = $spreadsheet->getActiveSheet()->toArray();

            $data = array_filter($data, function ($v) {
                return array_filter($v) != array();
            });
            $count = "0";
            foreach ($data as $row) {
                $user = new User();
                $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
                $password = substr(str_shuffle($chars), 0, 8);
                if ($count > 0) {
                    // try {
                    $nom = $row["0"];
                    $prenom  = $row["1"];
                    $telephone  = (string)$row["2"];
                    $commune = $row["3"];
                    $lieu = $row["4"];
                    $nomBV = $row["5"];
                    $phone = $phoneUtil->parse("+" . $telephone, null);
                    $codePays = $phoneUtil->getRegionCodeForNumber($phone);
                    $code = $phone->getCountryCode();
                    $number = (int)$phone->getNationalNumber();
                    $slug = $this->slugger->slug($lieu . '' . $nomBV);

                    // dd( (int) $code.$number, $phoneUtil->isValidNumber($phone));
                    if (!$phoneUtil->isValidNumber($phone)) {
                        $this->addFlash('error', 'Certains numéros de téléphone ne sont pas valides !');
                        return $this->redirectToRoute('app_user_add', [], Response::HTTP_SEE_OTHER);
                    }
                    foreach ($usersAll as $key => $value) {
                        if ($value->getTelephone() === (int)($code . $number)) {
                            $this->addFlash('error', 'Certains numéros de téléphone existent dèja !');
                            return $this->redirectToRoute('app_user_add', [], Response::HTTP_SEE_OTHER);
                        }
                    }
                    $nomBV1 = $bureauVoteRepository->findOneBy(['slug' => $slug]);
                    $cir = $departementRepository->findOneBy(['commune' => $commune]);
                    // $comm = $bureauVoteRepository->findOneBy(['commune' => $cir]);
                    // $slugCommune = $this->slugger->slug($nom.''.$commune);
                    // if (!$nomBV1->getCommune()->getCommune() == $commune) {
                    //     dd('error');
                    // }
                    $user->setNom($nom)
                        ->setUsername($number)
                        ->setPrenom($prenom)
                        ->setCommune($cir)
                        ->setLieu($lieu)
                        ->setCode($codePays)
                        ->setTelephone($code . $number)
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
                    // dd($user);
                    $entityManagerInterface->flush();
                    // } catch (\Throwable $th) {
                    //     throw new Exception("Impossible d'importer ce fichier.");
                    //     return $this->redirectToRoute('app_user_add', [], Response::HTTP_SEE_OTHER);
                    //     $this->addFlash('error', "Impossible d'importer ce fichier.");
                    // }
                } else {
                    $count = "1";
                }
            }
            $this->addFlash('success', 'Votre fichier a été importé avec succès');
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
            $telephone = $value->getTelephone();
            $nom = strtoupper($value->getNom());
            $username = $value->getUsername();
            $uiid = $value->getUuid();
            $prenom = strtoupper($value->getPrenom()[0]);
            $message = ("Bonjour $prenom. $nom, votre  identifiant de connexion est : $username, votre Mot de passe : $uiid \r\nLe lien de la plateforme : www.sunulegislatives.com");

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
            $message = ("Bonjour $prenom. $nom, votre  identifiant de connexion est : $username, votre Mot de passe : $uiid \r\nLe lien de la plateforme : www.sunulegislatives.com");
            $this->getSMS($user->getTelephone(), $message);
            $this->addFlash('success', "Les identifiants de connexion ont été envoyés à " . $user->getFullname());
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
            $this->getSMS($user->getTelephone(), $message);
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
                $telephone = $value->getTelephone();

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
