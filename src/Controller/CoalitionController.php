<?php

namespace App\Controller;

use App\Entity\Upload;
use App\Form\UploadType;
use App\Entity\Coalition;
use App\Form\CoalitionType;
use App\Repository\CoalitionRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/coalition")
 */
class CoalitionController extends AbstractController
{

    private $slugger;
    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }


    /**
     * @Route("/", name="app_coalition_index", methods={"GET"})
     */
    public function index(CoalitionRepository $coalitionRepository): Response
    {
        return $this->render('coalition/index.html.twig', [
            'coalitions' => $coalitionRepository->findBy([], ["id" => "DESC"]),
        ]);
    }

    /**
     * @Route("/new", name="app_coalition_new", methods={"GET", "POST"})
     */
    public function new(Request $request, CoalitionRepository $coalitionRepository): Response
    {
        $coalition = new Coalition();
        $form = $this->createForm(CoalitionType::class, $coalition);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $coalition->setSlug($this->slugger->slug($data->getNom()));
            $coalitionRepository->add($coalition, true);
            $this->addFlash('success', 'Cette coalition a été bien ajoutée');
            return $this->redirectToRoute('app_coalition_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('coalition/new.html.twig', [
            'coalition' => $coalition,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_coalition_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Coalition $coalition, CoalitionRepository $coalitionRepository): Response
    {
        $form = $this->createForm(CoalitionType::class, $coalition);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $coalition->setSlug($this->slugger->slug($data->getNom()));
            $coalitionRepository->add($coalition, true);
            $this->addFlash('success', 'Cette coalition a été bien modifée');

            return $this->redirectToRoute('app_coalition_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('coalition/edit.html.twig', [
            'coalition' => $coalition,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/delete", name="app_coalition_delete")
     */
    public function delete(Coalition $coalition): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($coalition);
        $entityManager->flush();
        $this->addFlash('success', 'Cette coalition a été bien supprimée');

        return $this->redirectToRoute('app_coalition_index', [], Response::HTTP_SEE_OTHER);
    }
    /**
     * @Route("/add", name="app_coalition_add")
     *
     * @param Request $request
     * @param EntityManagerInterface $entityManagerInterface
     * @return Response
     */
    public function addByCSV(Request $request, CoalitionRepository $coalitionRepository, EntityManagerInterface $entityManagerInterface): Response

    {

        $upload = new Upload();
        $form = $this->createForm(UploadType::class, $upload);
        $coalitions = $coalitionRepository->findAll();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $fileName = $request->files->get("upload");
            $fileNamePath = $fileName['file']->getRealPath();
            // dd($fileName['file']->guessExtension());
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
            $data = array_filter($data, function ($v) {
                return array_filter($v) != array();
            });
            $count = "0";
            foreach ($data as $row) {
                if ($count > 0) {
                    try {
                        $coalition = new Coalition();
                        $nom = $row["0"];
                        $coalition->setNom($nom);
                        $coalition->setSlug($this->slugger->slug($nom));
                        foreach ($coalitions as $key => $value) {
                            if ($value->getNom() == $nom) {
                                $this->addFlash('error', 'Certaines coalitions existent dèja !');
                                return $this->redirectToRoute('app_coalition_add', [], Response::HTTP_SEE_OTHER);
                            }
                        }
                        $entityManagerInterface->persist($coalition);
                        $entityManagerInterface->flush();
                        // $this->addFlash('success', 'Votre fichier a été importé avec succés');
                    } catch (\Throwable $th) {
                        throw new \Exception("Impossible d'importer ce fichier.");
                        $this->addFlash('error', "Impossible d'importer ce fichier.");
                        return $this->redirectToRoute('app_coalition_add', [], Response::HTTP_SEE_OTHER);
                    }
                } else {
                    $count = "1";
                }
            }
            $this->addFlash('success', 'Votre fichier a été importé avec succés');
            return $this->redirectToRoute('app_coalition_index');
        }
        return $this->render('coalition/add-coalition.html.twig', [
            'form' => $form->createView()
            // 'erreur' => $erreur,
        ]);
    }
}
