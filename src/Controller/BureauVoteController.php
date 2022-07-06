<?php

namespace App\Controller;

use Exception;
use App\Entity\Upload;
use App\Form\UploadType;
use App\Entity\BureauVote;
use App\Form\BureauVoteType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Repository\BureauVoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/bureau/vote")
 * @IsGranted("ROLE_ADMIN")
 */
class BureauVoteController extends AbstractController
{
    /**
     * @Route("/", name="app_bureau_vote_index", methods={"GET"})
     */
    public function index(BureauVoteRepository $bureauVoteRepository): Response
    {
        return $this->render('bureau_vote/index.html.twig', [
            'bureau_votes' => $bureauVoteRepository->findBy([], ['nomCir' => 'DESC']),
        ]);
    }

    /**
     * @Route("/new", name="app_bureau_vote_new", methods={"GET", "POST"})
     */
    public function new(Request $request, BureauVoteRepository $bureauVoteRepository): Response
    {
        $bureauVote = new BureauVote();
        $form = $this->createForm(BureauVoteType::class, $bureauVote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bureauVoteRepository->add($bureauVote, true);
            $this->addFlash('success', 'Ce bureau de vote a été bien ajouté');
            return $this->redirectToRoute('app_bureau_vote_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('bureau_vote/new.html.twig', [
            'bureau_vote' => $bureauVote,
            'form' => $form,
        ]);
    }


    /**
     * @Route("/{id}/edit", name="app_bureau_vote_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, BureauVote $bureauVote, BureauVoteRepository $bureauVoteRepository): Response
    {
        $form = $this->createForm(BureauVoteType::class, $bureauVote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bureauVoteRepository->add($bureauVote, true);
            $this->addFlash('success', 'Ce bureau de vote a été bien modifié');
            return $this->redirectToRoute('app_bureau_vote_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('bureau_vote/edit.html.twig', [
            'bureau_vote' => $bureauVote,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/delete", name="app_bureau_vote_delete")
     */
    public function delete(BureauVote $bureauVote): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($bureauVote);
        $entityManager->flush();
        $this->addFlash('success', 'Ce bureau de vote a été bien supprimé');
        return $this->redirectToRoute('app_bureau_vote_index', [], Response::HTTP_SEE_OTHER);
    }


    /**
     * @Route("/add", name="app_bureau_vote_add")
     */
    public function addBy(Request $request, EntityManagerInterface $entityManagerInterface): Response

    {

        $upload = new Upload();
        $form = $this->createForm(UploadType::class, $upload);

        $form->handleRequest($request);
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
            $data = array_filter($data, function ($v) {
                return array_filter($v) != array();
            });
            // $spreadsheet = IOFactory::load($fileNamePath);
            // $data = $spreadsheet->getActiveSheet()->toArray();
            // dd($data);
            $count = "0";
            foreach ($data as $row) {
                if ($count > 0) {
                    try {
                        $bureauVote = new BureauVote();
                        $nomCir = $row["0"];
                        $nomBV  = $row["1"];
                        $bureauVote->setNomBV("$nomBV")
                            ->setNomCir($nomCir);
                        $entityManagerInterface->persist($bureauVote);
                        $entityManagerInterface->flush();
                    } catch (\Throwable $th) {
                        // throw new Exception("Impossible d'importer ce fichier.");
                        $this->addFlash('error', "Impossible d'importer ce fichier.");
                        return $this->redirectToRoute('app_bureau_vote_add', [], Response::HTTP_SEE_OTHER);
                    }
                } else {
                    $count = "1";
                }
            }
            $this->addFlash('success', 'Votre fichier a été importé avec succès');
            return $this->redirectToRoute('app_bureau_vote_index');
        }
        return $this->render('bureau_vote/add-bureau-vote.html.twig', [
            'form' => $form->createView()
            // 'erreur' => $erreur,
        ]);
    }
}
