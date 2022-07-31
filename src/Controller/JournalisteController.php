<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Resultat;
use App\Form\ResultatType;
use App\Entity\ResultatCoalition;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Repository\ResultatRepository;
use App\Repository\CoalitionRepository;
use App\Repository\BureauVoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DepartementRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @Route("/journaliste")
 */
class JournalisteController extends AbstractController
{
    private $roleRepository;
    private $userPasswordHasher;
    private $userRepository;
    private $bureauVoteRepository;
    private $departementRepository;
    private $session;
    private $resultatRepository;
    private $coalitionRepository;
    private $slugger;
    public function __construct(RoleRepository $roleRepository, UserPasswordHasherInterface $userPasswordHasher, UserRepository $userRepository, DepartementRepository $departementRepository, BureauVoteRepository $bureauVoteRepository, SessionInterface $session, ResultatRepository $resultatRepository, CoalitionRepository $coalitionRepository, SluggerInterface $slugger)
    {
        $this->roleRepository = $roleRepository;
        $this->session = $session;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->userRepository = $userRepository;
        $this->departementRepository = $departementRepository;
        $this->bureauVoteRepository = $bureauVoteRepository;
        $this->resultatRepository = $resultatRepository;
        $this->coalitionRepository = $coalitionRepository;
        $this->slugger = $slugger;
    }

    /**
     * @Route("/", name="app_journaliste")
     */
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $user = new User();
        $role = $this->roleRepository->findOneBy(["libelle" => "ROLE_JOURNALISTE"]);

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
            ->add('presse', TextType::class, [
                'label' => 'Organe de presse',
                'attr' => [
                    'placeholder' => 'Entrez l\'organe de presse',
                ],
                'constraints' => new NotBlank([
                    'message' => 'Veuillez entrer l\'organe de presse.'
                ]),
            ])
            ->add('check', CheckboxType::class, [
                'label' => 'J\'accepte les termes et conditions',
                'constraints' => new NotBlank([
                    'message' => 'Veuillez accepter les termes et conditions.'
                ]),
            ])
            ->add('telephone', IntegerType::class, [
                'label' => 'Numéro de téléphone',
                'attr' => [
                    'placeholder' => 'Entrez le numéro de téléphone',
                ],
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
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les deux mots de passe ne sont pas identiques.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'trim' => true,
                'first_options'  => ['label' => 'Mot de passe', 'attr' => [
                    'placeholder' => "Entrer le mot de passe"
                ]],
                'second_options' => ['label' => 'Confirmez le mot de passe', 'attr' => [
                    'placeholder' => "Veuillez confirmer le mot de passe"
                ]],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de saisir le mot de passe.'
                    ]),
                ],
            ])

            ->getForm();
        $form->handleRequest($request);
        $usersAll = $this->userRepository->findAll();
        if ($form->isSubmitted() && $form->isValid()) {
            $telephone =  $form->get('telephone')->getData();
            $nom = $form->get('nom')->getData();
            $prenom = $form->get('prenom')->getData();
            $password = $form->get('password')->getData();
            $presse = $form->get('presse')->getData();
            foreach ($usersAll as $value) {
                if ($value->getTelephone() === (int)(221 . $telephone)) {
                    $this->addFlash('error', 'Ce numéro de téléphone existe dèja !');
                    return $this->redirectToRoute('app_journaliste', [], Response::HTTP_SEE_OTHER);
                }
            }
            $user->setRole($role);
            $user->setUuid($password);
            $user->setCode("SN");
            $user->setNom($nom);
            $user->setPresse($presse);
            $user->setPrenom($prenom);
            $user->setSlug($this->slugger->slug(trim(221 . $telephone)));
            $user->setTelephone(trim(221 . $telephone));
            $user->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $user,
                    $password
                )
            );
            $user->setUsername($telephone);
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Votre inscription a réussi avec succès. Vous allez utiliser votre numéro de téléphone et votre mot de passe pour vous connecter.');
            return $this->redirectToRoute('app_success_journaliste', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('journaliste/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/add-circonscription", name="app_journaliste_circonscription_add")
     * @IsGranted("ROLE_JOURNALISTE")
     */
    public function add_journaliste(Request $request)
    {
        $ressultDepartements = [];
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
            return $this->redirectToRoute('app_circonscription_commune_add', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('journaliste/add-circonscription.html.twig', [
            'form' => $form->createView(),
            'data' => $dt
        ]);
    }

    /**
     * @Route("/add-commune", name="app_circonscription_commune_add")
     * @IsGranted("ROLE_JOURNALISTE")
     */
    public function addcommune(Request $request): Response
    {
        $departement = $this->session->get("circonscription", []);
        if ($departement == []) {
            return $this->redirectToRoute('app_journaliste_circonscription_add', [], Response::HTTP_SEE_OTHER);
        }
        $communes  = $this->departementRepository->findBy(["nom" => $departement['circonscription']]);
        $com = [];
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
            return $this->redirectToRoute('app_journaliste_lieu_add', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('journaliste/add-commune.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/add-lieu", name="app_journaliste_lieu_add")
     * @IsGranted("ROLE_JOURNALISTE")
     */
    public function addlieu(Request $request): Response
    {
        $commune = $this->session->get("commune", []);
        if ($commune == []) {
            return $this->redirectToRoute('app_circonscription_commune_add', [], Response::HTTP_SEE_OTHER);
        }
        $bv = [];
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
            return $this->redirectToRoute('app_journaliste_bv_add', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('journaliste/add-lieu.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/add-bv", name="app_journaliste_bv_add")
     * @IsGranted("ROLE_JOURNALISTE")
     */
    public function addbv(Request $request): Response
    {

        $lieu = $this->session->get("lieu", []);
        if ($lieu == []) {
            return $this->redirectToRoute('app_journaliste_lieu_add', [], Response::HTTP_SEE_OTHER);
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
                'placeholder' => 'Choisir le Bureau de vote  pour lequel, vous donnez les résultats',
                'choices' => $bv_nom,
                'data' => $dt ? $dt['nom_bv'] : '',
                'constraints' => new NotBlank([
                    'message' => 'Veuillez choisir le Bureau  de vote pour lequel, vous donnez les résultats.'
                ]),
            ])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $this->session->set("nom_bv", $data);
            return $this->redirectToRoute('app_journaliste_resultats_add', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('journaliste/bv.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/add-resultats", name="app_journaliste_resultats_add")
     * @IsGranted("ROLE_JOURNALISTE")
     */
    public function add_resultat(Request $request, EntityManagerInterface $manager): Response
    {

        $nom_bv1 = $this->session->get("nom_bv", []);
        if ($nom_bv1 == []) {
            return $this->redirectToRoute('app_journaliste_bv_add', [], Response::HTTP_SEE_OTHER);
        }
        $resultat = new Resultat();
        $form = $this->createForm(ResultatType::class);
        $form->handleRequest($request);
        $userConnected = $this->getUser();
        if ($form->isSubmitted() && $form->isValid()) {
            $resultat->setUser($userConnected);
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
            return $this->redirectToRoute('app_journaliste_resultats_confirm', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('journaliste/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/add-resultats/confirm", name="app_journaliste_resultats_confirm")
     * @IsGranted("ROLE_JOURNALISTE")
     */
    public function confirm_resultat(Request $request, EntityManagerInterface $manager): Response
    {
        $resultat = new Resultat();
        $form = $this->createForm(ResultatType::class);
        $form->handleRequest($request);
        $userConnected = $this->getUser();

        $resultat_session = $this->session->get("data_session", []);
        $nom_vb = $this->session->get("nom_bv", []);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $resultat->setUser($userConnected);
            $new_user = new User();
            if ($resultat_session != $data) {
                $this->addFlash('error', "Les résultats ne sont pas les mêmes !");
                return $this->redirectToRoute('app_journaliste_resultats_add', [], Response::HTTP_SEE_OTHER);
            } else {
                $user_id = $this->userRepository->find($userConnected->getId());
                $bvv = $this->bureauVoteRepository->findOneBy(['slug' => $nom_vb]);
                if ($user_id->getBV() == null) {
                    // dd('ok null');
                    $resultat->setNbVotant($data['nbVotant'])
                        ->setBulletinNull($data['bulletinNull'])
                        ->setBulletinExp($data['bulletinExp']);
                    $resultat->setUser($userConnected);

                    $bvv->setIsValid(true);
                    $user_id->setBV($bvv);
                    $user_id->setCommune($bvv->getCommune());
                    $manager->persist($user_id);
                    $manager->persist($resultat);
                    $manager->persist($bvv);
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
                    $manager->flush();
                    $this->session->remove("data_session");
                    $this->addFlash('success', "Les résultats ont été ajoutés avec succès");
                    $this->session->remove("circonscription");
                    $this->session->remove("commune");
                    $this->session->remove("lieu");
                    $this->session->remove("nom_bv");
                    return $this->redirectToRoute('app_resultat_journaliste', [], Response::HTTP_SEE_OTHER);
                } else {
                    $resultat->setNbVotant($data['nbVotant'])
                        ->setBulletinNull($data['bulletinNull'])
                        ->setBulletinExp($data['bulletinExp']);
                    $resultat->setUser($userConnected);
                    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
                    $new_username = substr(str_shuffle($chars), 0, 8);
                    $new_user->setUsername($new_username)
                        ->setPassword($user_id->getPassword())
                        ->setRole($user_id->getRole())
                        ->setNom($user_id->getNom())
                        ->setPrenom($user_id->getPrenom())
                        ->setTelephone($user_id->getTelephone())
                        ->setUuid($user_id->getUuid())
                        ->setCode($user_id->getCode())
                        ->setBV($bvv)
                        ->setSlug($this->slugger->slug($user_id->getTelephone()))
                        ->setCommune($bvv->getCommune())
                        ->setPresse($user_id->getPresse());
                    $bvv->setIsValid(true);
                    $manager->persist($bvv);
                    $manager->persist($new_user);
                    $resultat->setUser($new_user);
                    $manager->persist($resultat);
                    // dd($resultat, $new_user);
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
                    $manager->flush();
                    $this->session->remove("data_session");
                    $this->addFlash('success', "Les résultats ont été ajoutés avec succès");
                    $this->session->remove("circonscription");
                    $this->session->remove("commune");
                    $this->session->remove("lieu");
                    $this->session->remove("nom_bv");
                    return $this->redirectToRoute('app_resultat_journaliste', [], Response::HTTP_SEE_OTHER);
                }
            }
        }
        return $this->render('journaliste/new-confirm.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/resultats", name="app_resultat_journaliste")
     * @IsGranted("ROLE_JOURNALISTE")
     */

    public function res()
    {
        $role = $this->getUser()->getRoles()["0"];
        $user = $this->getUser();
        if ($role === "ROLE_JOURNALISTE") {
            $coalitions = $this->coalitionRepository->findBy([], []);
            $resultats_rep = $this->resultatRepository->findBy([], []);

            return $this->render('journaliste/resultat-journaliste.html.twig', [
                'resultats' => $resultats_rep,
                'coalitions' => $coalitions,
            ]);
        }
    }

    /**
     * @Route("/termes-conditions", name="app_termes_conditions_journaliste")
     */
    public function app_termes_conditions(): Response
    {
        return $this->render('journaliste/termes-conditions.html.twig');
    }

    /**
     * @Route("/success", name="app_success_journaliste")
     */
    public function success(): Response
    {
        return $this->render('journaliste/success.html.twig');
    }
}
