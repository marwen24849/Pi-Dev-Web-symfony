<?php

namespace App\Controller;

use App\Service\OcrService;
use App\Entity\DemandeConge;
use App\Form\DemandeCongeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class DemandeCongeController extends AbstractController
{
    #[Route('/demandeconge', name: 'app_demande_conge')]
    public function index(Request $request, EntityManagerInterface $em,OcrService $ocrService): Response
    {
        $demande = new DemandeConge();
        $form = $this->createForm(DemandeCongeType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $demande->setStatus('PENDING');



            $certificateFile = $form->get('certificate')->getData();
            if ($certificateFile) {
                try {
                    $filename = uniqid() . '.' . $certificateFile->guessExtension();
                    $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads';
                    $certificateFile->move($uploadDir, $filename);
                    $demande->setCertificate($filename);

                    // ✅ OCR.Space API instead of Tesseract
                    $fullPath = $uploadDir . '/' . $filename;
                    $text = $ocrService->extractText($fullPath); // Use the OCR Service

                    if (!$text) {
                        $this->addFlash('error', '❌ Impossible d’extraire le texte du certificat.');
                        return $this->render('demande_conge/index.html.twig', [
                            'form' => $form->createView(),
                        ]);
                    }

                    // 🔍 Check required words
                    $requiredWords = ['certificat', 'medical'];
                    foreach ($requiredWords as $word) {
                        if (stripos($text, $word) === false) {
                            $this->addFlash('error', "❌ Certificat non valide : le mot-clé \"$word\" est manquant.");
                            return $this->render('demande_conge/index.html.twig', [
                                'form' => $form->createView(),
                            ]);
                        }
                    }

                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement du certificat.');
                    return $this->render('demande_conge/index.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }
            }

            $em->persist($demande);
            $em->flush();

            $this->addFlash('success', 'Votre demande de congé a été envoyée avec succès.');
            return $this->redirectToRoute('app_demande_conge');
        }

        return $this->render('demande_conge/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/liste-conges', name: 'app_liste_conges')]
    public function liste(Request $request, EntityManagerInterface $em): Response
    {
        $search = $request->query->get('search');

        $queryBuilder = $em->getRepository(DemandeConge::class)->createQueryBuilder('d');

        if ($search) {
            $queryBuilder
                ->where('d.typeConge LIKE :search OR d.autre LIKE :search OR d.justification LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $conges = $queryBuilder->getQuery()->getResult();

        return $this->render('demande_conge/liste.html.twig', [
            'conges' => $conges,
            'search' => $search,
        ]);
    }

    #[Route('/demandeconge/edit/{id}', name: 'app_demande_conge_edit')]
    public function edit(Request $request, DemandeConge $demande, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(DemandeCongeType::class, $demande, [
            'edit_justification_only' => true,
            'is_edit_mode' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Justification modifiée avec succès.');
            return $this->redirectToRoute('app_liste_conges');
        }

        return $this->render('demande_conge/edit.html.twig', [
            'form' => $form->createView(),
            'demande' => $demande,
        ]);
    }

    #[Route('/demandeconge/delete/{id}', name: 'app_conge_delete', methods: ['POST'])]
    public function delete(Request $request, DemandeConge $conge, EntityManagerInterface $em): Response
    {
        $em->remove($conge);
        $em->flush();
        $this->addFlash('success', 'Demande supprimée avec succès.');
        return $this->redirectToRoute('app_liste_conges');
    }

    #[Route('/demandeconge/show/{id}', name: 'app_conge_show')]
    public function show(DemandeConge $demande): Response
    {
        $filename = $demande->getCertificate();
        $filePath = $filename ? '/uploads/' . $filename : null;

        $mime = null;
        $isImage = false;

        if ($filename) {
            $absolutePath = $this->getParameter('kernel.project_dir') . '/public' . $filePath;

            if (file_exists($absolutePath)) {
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($absolutePath);
                $isImage = str_starts_with($mime, 'image/');
            }
        }

        return $this->render('demande_conge/show.html.twig', [
            'demande' => $demande,
            'filePath' => $filePath,
            'mime' => $mime ?? 'application/octet-stream',
            'isImage' => $isImage,
        ]);
    }
    #[Route('/traitement-conges', name: 'app_traitement_conges')]
    public function traitementDesConges(Request $request, EntityManagerInterface $em): Response
    {
        $id = $request->request->get('demande_id');
        $action = $request->request->get('action');

        if ($id && in_array($action, ['approve', 'reject'])) {
            $demande = $em->getRepository(DemandeConge::class)->find($id);
            if ($demande && $demande->getStatus() === 'PENDING') {
                $nouveauStatut = $action === 'approve' ? 'APPROVED' : 'REJECTED';
                $demande->setStatus($nouveauStatut);
                $em->flush();
                $this->addFlash('success', "Demande #{$id} a été " . strtolower($nouveauStatut) . " avec succès.");
            }
        }

        $conges = $em->getRepository(DemandeConge::class)->findBy(['status' => 'PENDING']);

        return $this->render('demande_conge/decision.html.twig', [
            'conges' => $conges,
        ]);
    }



}
