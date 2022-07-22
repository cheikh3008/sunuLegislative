<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\BureauVote;
use App\Entity\Departement;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use libphonenumber\PhoneNumberUtil;
use App\Repository\BureauVoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DepartementRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RepresentantController extends AbstractController
{
    private $session;
    private $departementRepository;
    private $bureauVoteRepository;
    private $userRepository;
    public function __construct(SessionInterface $session, DepartementRepository $departementRepository, BureauVoteRepository $bureauVoteRepository, UserRepository $userRepository)
    {
        $this->session = $session;
        $this->departementRepository = $departementRepository;
        $this->bureauVoteRepository = $bureauVoteRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/representant", name="app_representant")
     */
    public function index(): Response
    {
        return $this->render('representant/index.html.twig', [
            'controller_name' => 'RepresentantController',
        ]);
    }

    /**
     * @Route("/representant/add-circonscription", name="app_circonscription_add")
     */
    public function addcirconscription(Request $request): Response
    {
        $departements = $this->departementRepository->findBy([], []);
        foreach ($departements as $key => $value) {
            $ressultDepartements[$value->getNom()] = $value->getNom();
        }
        // dd($ressultDepartements);
        $form = $this->createFormBuilder()
            ->add('circonscription', ChoiceType::class, [
                'label' => false,
                'placeholder' => 'Choisir la circonscription',
                'choices' => $ressultDepartements,
                // 'class' => Departement::class,
                'constraints' => new NotBlank([
                    'message' => 'Veuillez choisir la circonscription .'
                ]),
            ])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->session->get("circonscription", []);
            $data = $form->getData();
            $this->session->set("circonscription", $data);
            // $this->addFlash('success', 'Le partenaire a été bien ajouté');
            return $this->redirectToRoute('app_commune_add', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('representant/add-circonscription.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/representant/add-commune", name="app_commune_add")
     */
    public function addcommune(Request $request): Response
    {
        $departement = $this->session->get("circonscription", []);
        $communes  = $this->departementRepository->findBy(["nom" => $departement['circonscription']]);
        foreach ($communes as $key => $value) {
            $com[$value->getCommune()] = $value->getCommune();
        }
        $form = $this->createFormBuilder()
            ->add('commune', ChoiceType::class, [
                'label' => false,
                'placeholder' => 'Choisir la commune',
                'choices' => $com,
                'constraints' => new NotBlank([
                    'message' => 'Veuillez choisir la commune .'
                ]),
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $departement = $this->session->get("commune", []);
            $data = $form->getData();
            $this->session->set("commune", $data);
            // $this->addFlash('success', 'Le partenaire a été bien ajouté');
            return $this->redirectToRoute('app_lieu_add', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('representant/add-commune.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/representant/add-lieu", name="app_lieu_add")
     */
    public function addlieu(Request $request): Response
    {
        $commune = $this->session->get("commune", []);
        $communes  = $this->departementRepository->findBy(["commune" => $commune['commune']]);
        $bureauVote = $this->bureauVoteRepository->findBy(['commune' => $communes[0]]);
        // dd($bureauVote);
        foreach ($bureauVote as $key => $value) {
            $bv[$value->getLieu() . ' - ' . $value->getNomBV()] = $value->getSlug();
        }
        $form = $this->createFormBuilder()
            ->add('commune', ChoiceType::class, [
                'label' => false,
                'placeholder' => 'Choisir le lieu/ centre de vote',
                'choices' => $bv,
                'constraints' => new NotBlank([
                    'message' => 'Veuillez choisir le lieu/ centre de vote .'
                ]),
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $lieu = $this->session->get("lieu", []);
            $data = $form->getData();
            $bv = $this->bureauVoteRepository->findOneBy(['slug' => $data]);
            $userRS = $this->userRepository->findOneBy(['BV' => $bv]);
            if ($userRS) {
                $this->addFlash('error', "Ce bureau a été dèja affecté par un représentant");
                return $this->redirectToRoute('app_lieu_add', [], Response::HTTP_SEE_OTHER);
            }
            $this->session->set("lieu", $data);
            // $this->addFlash('success', 'Le partenaire a été bien ajouté');
            return $this->redirectToRoute('app_infos_add', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('representant/add-lieu.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/representant/add-infos", name="app_infos_add")
     */
    public function addInfo(Request $request, UserPasswordHasherInterface $userPasswordHasher, RoleRepository $roleRepository, EntityManagerInterface $em): Response
    {
        $user = new User();
        $role = $roleRepository->findOneBy(["libelle" => "ROLE_REPRESENTANT"]);
        $slugBV = $this->session->get("lieu", []);
        $bureauVote = $this->bureauVoteRepository->findOneBy(['slug' => $slugBV]);
        // dd($bureauVote);
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $password = substr(str_shuffle($chars), 0, 8);
        $phoneUtil = PhoneNumberUtil::getInstance();
        $codes_choice = [];
        $regions = ($phoneUtil->getSupportedRegions());
        foreach ($regions as  $value) {
            $codes_choice[$value . " + " . $phoneUtil->getCountryCodeForRegion($value)] = $value;
        }
        $form = $this->createFormBuilder()
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Entrez le nom',
                ],
                'constraints' => new NotBlank([
                    'message' => 'Veuillez remplir ce champs.'
                ]),
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'placeholder' => 'Entrez le prénom',
                ],
                'constraints' => new NotBlank([
                    'message' => 'Veuillez remplir ce champs.'
                ]),
            ])
            ->add('code', ChoiceType::class, [
                'label' => 'Code pays',
                'choices'  =>  $codes_choice,
                'placeholder' => 'Veuillez choisir un code pays',
                'constraints' => new NotBlank([
                    'message' => 'Veuillez remplir ce champs.'
                ]),
            ])
            ->add('telephone', NumberType::class, [
                'label' => 'Téléphone',
                'attr' => [
                    'placeholder' => 'Entrez le numéro de téléphone',
                    'id' => 'phone'
                ],
                'constraints' => new NotBlank([
                    'message' => 'Veuillez remplir ce champs.'
                ]),
                
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $telephone =  $form->get('telephone')->getData();
            $nom = $form->get('nom')->getData();
            $prenom = $form->get('prenom')->getData();
            $code = $form->get('code')->getData();
            $phone = $phoneUtil->parse($telephone, $code);
            $number = $phone->getNationalNumber();
            $indicatif = $phone->getCountryCode();
            if (!$phoneUtil->isValidNumber($phone)) {
                $this->addFlash('error', 'Le numéro de téléphone n\'est pas valide !');
                return $this->redirectToRoute('app_infos_add', [], Response::HTTP_SEE_OTHER);
            }
            $user->setRole($role);
            $user->setUuid($password);
            $user->setCode($code);
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setTelephone(trim($indicatif . $telephone));
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $password
                )
            );
            $user->setBV($bureauVote);
            $user->setCommune($bureauVote->getCommune());
            $user->setLieu($bureauVote->getLieu());
            $user->setUsername($number);
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Ce représentant a été bien ajouté');
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('representant/add-infos.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
