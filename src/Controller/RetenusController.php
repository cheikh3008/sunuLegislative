<?php

namespace App\Controller;

use Exception;
use App\Entity\Upload;
use App\Entity\Retenus;
use App\Form\UploadType;
use App\Form\RetenusType;
use App\Repository\RetenusRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/retenus")
 * @IsGranted("ROLE_ADMIN")
 */
class RetenusController extends AbstractController
{
    /**
     * @Route("/", name="app_retenus_index", methods={"GET"})
     */
    public function index(RetenusRepository $retenusRepository): Response
    {
        // dd($retenusRepository->findAll());
        return $this->render('retenus/index.html.twig', [
            'retenuses' => $retenusRepository->findBy([], ['id' => 'DESC']),
        ]);
    }

    /**
     * @Route("/new", name="app_retenus_new", methods={"GET", "POST"})
     */
    public function new(Request $request, RetenusRepository $retenusRepository): Response
    {
        $retenu = new Retenus();
        $form = $this->createForm(RetenusType::class, $retenu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $retenusRepository->add($retenu, true);
            $this->addFlash('success', 'Votre coalition a été bien ajouté');
            return $this->redirectToRoute('app_retenus_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('retenus/new.html.twig', [
            'retenu' => $retenu,
            'form' => $form,
        ]);
    }



    /**
     * @Route("/{id}/edit", name="app_retenus_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Retenus $retenu, RetenusRepository $retenusRepository): Response
    {
        $form = $this->createForm(RetenusType::class, $retenu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $retenusRepository->add($retenu, true);

            return $this->redirectToRoute('app_retenus_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('retenus/edit.html.twig', [
            'retenu' => $retenu,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/delete", name="app_retenus_delete")
     */
    public function delete(Retenus $retenu): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($retenu);
        $entityManager->flush();
        $this->addFlash('success', 'Votre liste a été bien supprimé');
        return $this->redirectToRoute('app_retenus_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/add", name="app_retenus_add")
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
            // dd($data);
            $count = "0";
            foreach ($data as $row) {
                if ($count > 0) {
                    try {
                        $retenus = new Retenus();
                        $nom = $row["0"];
                        $retenus->setNom($nom);
                        $entityManagerInterface->persist($retenus);
                        $entityManagerInterface->flush();
                        // $this->addFlash('success', 'Votre fichier a été importé avec succés');
                    } catch (\Throwable $th) {
                        // throw new Exception("Impossible d'importer ce fichier.");
                        $this->addFlash('error', "Impossible d'importer ce fichier.");
                        return $this->redirectToRoute('app_retenus_add', [], Response::HTTP_SEE_OTHER);
                    }
                } else {
                    $count = "1";
                }
            }
            $this->addFlash('success', 'Votre fichier a été importé avec succés');
            return $this->redirectToRoute('app_retenus_index');
        }
        return $this->render('retenus/add-retenus.html.twig', [
            'form' => $form->createView()
            // 'erreur' => $erreur,
        ]);
    }
}
