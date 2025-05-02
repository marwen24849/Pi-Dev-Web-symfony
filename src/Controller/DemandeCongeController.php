<?php

namespace App\Controller;


use App\Entity\Conge;
use App\Entity\User;
use App\Service\OcrService;
use App\Entity\DemandeConge;
use App\Form\DemandeCongeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Service\PdfGeneratorService;

final class DemandeCongeController extends AbstractController
{
    #[Route('/demandeconge', name: 'app_demande_conge')]
    public function index(Request $request, EntityManagerInterface $em, OcrService $ocrService): Response
    {
        $demande = new DemandeConge();
        $form = $this->createForm(DemandeCongeType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $session = $request->getSession();
            $userId = $session->get('user_id');

            if (!$userId) {
                $this->addFlash('error', 'Aucun utilisateur trouvé en session.');
                return $this->redirectToRoute('app_demande_conge');
            }

            $user = $em->getRepository(User::class)->find($userId);
            if (!$user) {
                $this->addFlash('error', 'Utilisateur introuvable.');
                return $this->redirectToRoute('app_demande_conge');
            }

            $dateDebut = $demande->getDateDebut();
            $dateFin = $demande->getDateFin();
            $nbJours = $dateDebut->diff($dateFin)->days + 1;

            if ($user->getSoldeConge() < $nbJours) {
                $this->addFlash('error', "❌ Solde insuffisant : vous avez {$user->getSoldeConge()} jour(s), mais vous avez demandé $nbJours jour(s).");
                return $this->render('demande_conge/index.html.twig', ['form' => $form->createView()]);
            }

            $demande->setUser_id($user);
            $demande->setStatus('PENDING');

            $certificateFile = $form->get('certificate')->getData();
            if ($certificateFile) {
                try {
                    $filename = uniqid() . '.' . $certificateFile->guessExtension();
                    $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads';
                    $certificateFile->move($uploadDir, $filename);
                    $demande->setCertificate($filename);

                    $text = $ocrService->extractText($uploadDir . '/' . $filename);
                    $requiredWords = ['certificat', 'medical'];

                    foreach ($requiredWords as $word) {
                        if (stripos($text, $word) === false) {
                            $this->addFlash('error', "❌ Certificat non valide : mot-clé \"$word\" manquant.");
                            return $this->render('demande_conge/index.html.twig', ['form' => $form->createView()]);
                        }
                    }

                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur de téléchargement du certificat.');
                    return $this->render('demande_conge/index.html.twig', ['form' => $form->createView()]);
                }
            }

            $em->persist($demande);
            $em->flush();

            $this->addFlash('success', '✅ Demande envoyée avec succès.');
            return $this->redirectToRoute('app_liste_conges');
        }

        return $this->render('demande_conge/index.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/liste-conges', name: 'app_liste_conges')]
    public function liste(Request $request, EntityManagerInterface $em): Response
    {
        $session = $request->getSession();
        $userId = $session->get('user_id');

        if (!$userId) {
            $this->addFlash('error', 'Utilisateur non connecté.');
            return $this->redirectToRoute('app_demande_conge');
        }

        $user = $em->getRepository(User::class)->find($userId);
        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('app_demande_conge');
        }

        $search = $request->query->get('search');

        $queryBuilder = $em->getRepository(DemandeConge::class)->createQueryBuilder('d')
            ->where('d.user_id = :user')
            ->setParameter('user', $user);

        if ($search) {
            $queryBuilder->andWhere('d.typeConge LIKE :search OR d.autre LIKE :search OR d.justification LIKE :search')
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
        $session = $request->getSession();
        $userId = $session->get('user_id');
        $user = $em->getRepository(User::class)->find($userId);

        if (!$user || $demande->getUser_id()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que vos propres demandes.');
        }

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
        $session = $request->getSession();
        $userId = $session->get('user_id');
        $user = $em->getRepository(User::class)->find($userId);

        if (!$user || $conge->getUser_id()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException('Vous ne pouvez supprimer que vos propres demandes.');
        }

        $em->remove($conge);
        $em->flush();
        $this->addFlash('success', 'Demande supprimée avec succès.');
        return $this->redirectToRoute('app_liste_conges');
    }

    #[Route('/demandeconge/show/{id}', name: 'app_conge_show')]
    public function show(Request $request, DemandeConge $demande, EntityManagerInterface $em): Response
    {
        $session = $request->getSession();
        $userId = $session->get('user_id');
        $user = $em->getRepository(User::class)->find($userId);

        $isAdmin = $user && $user->getRole() === 'ADMIN';
        $isOwner = $user && $demande->getUser_id()->getId() === $user->getId();

        if (!$isAdmin && !$isOwner) {
            throw $this->createAccessDeniedException('Accès refusé à cette demande.');
        }

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
    public function traitementDesConges(
        Request $request,
        EntityManagerInterface $em,
        PdfGeneratorService $pdfService,
        MailerInterface $mailer
    ): Response {
        $id = $request->request->get('demande_id');
        $action = $request->request->get('action');

        if ($id && in_array($action, ['approve', 'reject'])) {
            $demande = $em->getRepository(DemandeConge::class)->find($id);

            if ($demande && $demande->getStatus() === 'PENDING') {
                $nouveauStatut = $action === 'approve' ? 'APPROVED' : 'REJECTED';
                $demande->setStatus($nouveauStatut);
                $em->flush();

                if ($nouveauStatut === 'APPROVED') {
                    $user = $demande->getUser_id();
                    $nbJours = $demande->getDateDebut()->diff($demande->getDateFin())->days + 1;


                    $newSolde = max(0, $user->getSoldeConge() - $nbJours);
                    $user->setSoldeConge($newSolde);


                    $conge = new Conge();
                    $conge->setUser_id($user);
                    $conge->setConge_id($demande);
                    $conge->setStart_date($demande->getDateDebut());
                    $conge->setEnd_date($demande->getDateFin());

                    $em->persist($conge);
                    $em->flush();


                    $html = $this->renderView('suivi_conge/conge_confirmation.html.twig', [
                        'demande' => $demande,
                        'user' => $user
                    ]);

                    $pdfContent = $pdfService->generateFromHtml($html);


                    $email = (new Email())
                        ->from($_ENV['MAILER_FROM'])
                        ->to($user->getEmail())
                        ->subject('Votre demande de congé a été approuvée')
                        ->html('<p>Bonjour, veuillez trouver en pièce jointe la confirmation de votre congé approuvé.</p>')
                        ->attach($pdfContent, 'confirmation_conge.pdf', 'application/pdf');

                    $mailer->send($email);
                }

                $this->addFlash('success', "Demande #{$id} a été " . strtolower($nouveauStatut) . " avec succès.");
            }
        }

        $conges = $em->getRepository(DemandeConge::class)->findBy(['status' => 'PENDING']);
        return $this->render('demande_conge/decision.html.twig', ['conges' => $conges]);
    }


}
