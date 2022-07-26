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
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
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
        $dt =  $this->session->get("circonscription", []);
        // dd($ressultDepartements);
        $form = $this->createFormBuilder()
            ->add('circonscription', ChoiceType::class, [
                'label' => false,
                'placeholder' => 'Choisir le département',
                'choices' => $ressultDepartements,
                'data' => $dt ? $dt['circonscription'] : '',
                // 'class' => Departement::class,
                'constraints' => new NotBlank([
                    'message' => 'Veuillez choisir le département.'
                ]),
            ])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();
            $this->session->set("circonscription", $data);
            // $this->addFlash('success', 'Le partenaire a été bien ajouté');
            return $this->redirectToRoute('app_commune_add', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('representant/add-circonscription.html.twig', [
            'form' => $form->createView(),
            'data' => $dt
        ]);
    }

    /**
     * @Route("/representant/add-commune", name="app_commune_add")
     */
    public function addcommune(Request $request): Response
    {
        $departement = $this->session->get("circonscription", []);
        if ($departement == []) {
            return $this->redirectToRoute('app_circonscription_add', [], Response::HTTP_SEE_OTHER);
        }
        $communes  = $this->departementRepository->findBy(["nom" => $departement['circonscription']]);
        foreach ($communes as $key => $value) {
            $com[$value->getCommune()] = $value->getCommune();
        }
        $dt = $this->session->get("commune", []);
        $form = $this->createFormBuilder()
            ->add('commune', ChoiceType::class, [
                'label' => false,
                'placeholder' => 'Choisir la commune',
                'choices' => $com,
                'data' => $dt ? $dt['commune'] : '',
                'constraints' => new NotBlank([
                    'message' => 'Veuillez choisir la commune.'
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
        if ($commune == []) {
            return $this->redirectToRoute('app_commune_add', [], Response::HTTP_SEE_OTHER);
        }
        $communes  = $this->departementRepository->findBy(["commune" => $commune['commune']]);
        $bureauVote = $this->bureauVoteRepository->findBy(['commune' => $communes[0]]);
        foreach ($bureauVote as $key => $value) {
            $bv[$value->getLieu()] = $value->getLieu();
        }
        $dt = $this->session->get("lieu");
        $form = $this->createFormBuilder()
            ->add('lieu', ChoiceType::class, [
                'label' => false,
                'placeholder' => 'Choisir le lieu/ centre de vote',
                'choices' => $bv,
                'data' => $dt ? $dt['lieu'] : '',
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
            // if ($userRS) {
            //     $this->addFlash('error', "Ce bureau a été dèja affecté par un représentant");
            //     return $this->redirectToRoute('app_lieu_add', [], Response::HTTP_SEE_OTHER);
            // }
            $this->session->set("lieu", $data);
            // $this->addFlash('success', 'Le partenaire a été bien ajouté');
            return $this->redirectToRoute('app_bv_add', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('representant/add-lieu.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/representant/add-bv", name="app_bv_add")
     */
    public function addbv(Request $request): Response
    {

        $lieu = $this->session->get("lieu", []);
        if ($lieu == []) {
            return $this->redirectToRoute('app_lieu_add', [], Response::HTTP_SEE_OTHER);
        }
        $bureauVote = $this->bureauVoteRepository->findBy(['lieu' => $lieu['lieu']]);

        foreach ($bureauVote as $key => $value) {
            if ($value->isIsValid() == false) {
                $bv_nom[$value->getNomBV()] = $value->getSlug();
            } else {
                $bv_nom[] = [];
            }
        }
        $dt = $this->session->get("nom_bv", []);
        $form = $this->createFormBuilder()
            ->add('nom_bv', ChoiceType::class, [
                'label' => false,
                'placeholder' => 'Choisir le Bureau de vote  pour lequel, vous désirez être Représentant',
                'choices' => $bv_nom,
                'data' => $dt ? $dt['nom_bv'] : '',
                'constraints' => new NotBlank([
                    'message' => 'Veuillez choisir le Bureau  de vote pour lequel, vous désirez être Représentant.'
                ]),
            ])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $bv_res = $this->bureauVoteRepository->findOneBy(['slug' => $data['nom_bv']]);
            $userRS = $this->userRepository->findOneBy(['BV' => $bv_res]);
            if ($userRS) {
                $this->addFlash('error', "Ce bureau a été dèja affecté par un représentant");
                return $this->redirectToRoute('app_bv_add', [], Response::HTTP_SEE_OTHER);
            }

            $this->session->set("nom_bv", $data);
            return $this->redirectToRoute('app_infos_add', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('representant/bv.html.twig', [
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
        $slugBV = $this->session->get("nom_bv", []);
        if ($slugBV == []) {
            return $this->redirectToRoute('app_bv_add', [], Response::HTTP_SEE_OTHER);
        }
        $bureauVote = $this->bureauVoteRepository->findOneBy(['slug' => $slugBV]);
        // dd($bureauVote);
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $password = substr(str_shuffle($chars), 0, 8);

        $form = $this->createFormBuilder()
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Entrez le nom',
                ],
                'constraints' => new NotBlank([
                    'message' => 'Veuillez entrer le nom.'
                ]),
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'placeholder' => 'Entrez le prénom',
                ],
                'constraints' => new NotBlank([
                    'message' => 'Veuillez entrer le prénom.'
                ]),
            ])
            ->add('check', CheckboxType::class, [
                'label' => 'J\'accepte les termes et conditions',
                'constraints' => new NotBlank([
                    'message' => 'Veuillez accepter les termes et conditions.'
                ]),
            ])
            ->add('telephone', RepeatedType::class, [
                'type' => IntegerType::class,
                'invalid_message' => 'Les deux numéros de téléphone ne sont pas identiques.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'trim' => true,
                'first_options'  => ['label' => 'Numéro de téléphone', 'attr' => [
                    'placeholder' => "Veuillez entrer votre numéro de téléphone sans espace"
                ]],
                'second_options' => ['label' => 'Confirmez votre numéro de téléphone', 'attr' => [
                    'placeholder' => "Veuillez confirmer votre numéro de téléphone sans espace"
                ]],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de saisir votre numéro de téléphone sans espace.'
                    ]),
                    new Regex([
                        'pattern'  => '#^(77||78||76||70||75)[0-9]{9}$#',
                        'message' => 'Veuillez entrer un numéro de téléphone valide.',
                    ]),
                ],
            ])
            // ->add('telephone', NumberType::class, [
            //     'label' => 'Téléphone',
            //     'attr' => [
            //         'placeholder' => 'Entrez le numéro de téléphone',
            //         'id' => 'phone'
            //     ],
            //     'constraints' => [
            //         new NotBlank([
            //             'message' => 'Veuillez entrer le numéro du téléphone.'
            //         ]),
            //         new Regex([
            //             'pattern'  => '#^(77||78||76||70||75)[0-9]{9}$#',
            //             'message' => 'Veuillez entrer un numéro de téléphone valide.',
            //         ])
            //     ],

            // ])
            ->getForm();
        $form->handleRequest($request);
        $usersAll = $this->userRepository->findAll();
        if ($form->isSubmitted() && $form->isValid()) {
            $telephone =  $form->get('telephone')->getData();
            $nom = $form->get('nom')->getData();
            $prenom = $form->get('prenom')->getData();

            foreach ($usersAll as $key => $value) {
                if ($value->getTelephone() === (int)(221 . $telephone)) {
                    $this->addFlash('error', 'Ce numéro de téléphone existe dèja !');
                    return $this->redirectToRoute('app_infos_add', [], Response::HTTP_SEE_OTHER);
                }
            }
            $new_bv = $this->bureauVoteRepository->find($bureauVote->getId());
            $new_bv->setIsValid(true);
            $user->setRole($role);
            $user->setUuid($password);
            $user->setCode("SN");
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setTelephone(trim(221 . $telephone));
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $password
                )
            );
            $user->setBV($bureauVote);
            $user->setCommune($bureauVote->getCommune());
            $user->setLieu($bureauVote->getLieu());
            $user->setUsername($telephone);
            $em->persist($user);
            $em->flush();
            $bvv = $user->getBV()->getNombv();
            $ll = $user->getLieu();
            $this->addFlash('success', "
            Félicitations, vous êtes bien enregistré comme Représentant du centre/ lieu de vote  $ll / $bvv. Vos identifiants de connexion pour saisir les résultats du Bureau de Vote pour lequel vous êtes Représentant vous seront ultérieurement envoyés par SMS.\r\nMerci de bien noter votre numéro de Bureau de Vote d'affectation(pour lequel vous êtes représentant).
            ");
            $this->session->remove("circonscription");
            $this->session->remove("commune");
            $this->session->remove("lieu");
            $this->session->remove("nom_bv");
            return $this->redirectToRoute('app_representant', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('representant/add-infos.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/representant/termes-conditions", name="app_termes_conditions")
     */
    public function app_termes_conditions(): Response
    {
        return $this->render('representant/termes-conditions.html.twig');
    }
}
