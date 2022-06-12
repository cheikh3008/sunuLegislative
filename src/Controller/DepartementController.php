<?php

namespace App\Controller;

use Exception;
use App\Entity\Upload;
use App\Form\UploadType;
use App\Entity\Departement;
use App\Form\DepartementType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DepartementRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use function PHPUnit\Framework\returnSelf;

/**
 * @Route("/departement")
 */
class DepartementController extends AbstractController
{
    /**
     * @Route("/", name="app_departement_index", methods={"GET"})
     */
    public function index(DepartementRepository $departementRepository): Response
    {
        return $this->render('departement/index.html.twig', [
            'departements' => $departementRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="app_departement_new", methods={"GET", "POST"})
     */
    public function new(Request $request, DepartementRepository $departementRepository): Response
    {
        $departement = new Departement();
        $form = $this->createForm(DepartementType::class, $departement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $departementRepository->add($departement, true);

            return $this->redirectToRoute('app_departement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('departement/new.html.twig', [
            'departement' => $departement,
            'form' => $form,
        ]);
    }

    // /**
    //  * @Route("/{id}", name="app_departement_show", methods={"GET"})
    //  */
    // public function show(Departement $departement): Response
    // {
    //     return $this->render('departement/show.html.twig', [
    //         'departement' => $departement,
    //     ]);
    // }

    /**
     * @Route("/{id}/edit", name="app_departement_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Departement $departement, DepartementRepository $departementRepository): Response
    {
        $form = $this->createForm(DepartementType::class, $departement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $departementRepository->add($departement, true);

            return $this->redirectToRoute('app_departement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('departement/edit.html.twig', [
            'departement' => $departement,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/delete", name="app_departement_delete")
     */
    public function delete(Departement $departement): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($departement);
        $entityManager->flush();
        $this->addFlash('success', 'Votre Departement a été bien supprimé');

        return $this->redirectToRoute('app_departement_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/add-csv", name="app_add_csv")
     */
    public function addBy(Request $request, EntityManagerInterface $entityManagerInterface): Response

    {

        $upload = new Upload();
        $form = $this->createForm(UploadType::class, $upload);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $fileName = $request->files->get("upload");
            $fileNamePath = $fileName['file']->getRealPath();
            $spreadsheet = IOFactory::load($fileNamePath);
            $data = $spreadsheet->getActiveSheet()->toArray();

            $count = "0";
            foreach ($data as  $row) {
                if ($count > 0) {
                    $departement = new Departement();
                    $nom = $row['0'];
                    $NBBV  = $row['1'];
                    $NBin = $row['2'];
                    // dd($departement);
                    try {
                        $departement->setNom($nom)
                            ->setNbBV($NBBV)
                            ->setNbInscrit($NBin);
                        $entityManagerInterface->persist($departement);
                        $entityManagerInterface->flush();
                    } catch (\Throwable $th) {
                        throw new Exception("Impossible d'importer ce fichier.");
                    }
                } else {
                    $count = "1";
                }
            }
            $this->addFlash('success', 'Votre fichier a été importé avec succés');
            return $this->redirectToRoute('app_departement_index');
        }
        return $this->render('departement/add-csv.html.twig', [
            'form' => $form->createView()
            // 'erreur' => $erreur,
        ]);
    }
}
