<?php

namespace App\Controller;

use App\Entity\DemandeConge;
use App\Form\DemandeCongeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DemandeCongeController extends AbstractController
{
    #[Route('/demandeconge', name: 'app_demande_conge')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $soldeConge = 30;

        $demande = new DemandeConge();
        $form = $this->createForm(DemandeCongeType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $joursDemandes = $demande->getDateDebut()->diff($demande->getDateFin())->days + 1;

            if ($joursDemandes > $soldeConge) {
                $this->addFlash('error', 'Solde de congé insuffisant. Il vous reste 30 jours.');
            } else {
                $demande->setStatus('PENDING');

                // Récupération du fichier (non mappé)
                $certificate = $form->get('certificate')->getData();
                if ($certificate) {
                    $filename = uniqid().'.'.$certificate->guessExtension();
                    try {
                        $certificate->move($this->getParameter('kernel.project_dir') . '/public/uploads', $filename);
                        $demande->setCertificate($filename);
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Erreur de téléchargement du fichier.');
                    }
                }

                $em->persist($demande);
                $em->flush();

                $this->addFlash('success', 'Demande de congé envoyée avec succès. Jours restants : ' . ($soldeConge - $joursDemandes));
                return $this->redirectToRoute('app_demande_conge');
            }
        }

        return $this->render('demande_conge/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
