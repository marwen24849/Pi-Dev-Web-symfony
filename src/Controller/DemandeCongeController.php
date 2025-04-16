<?php

namespace App\Controller;

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
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $demande = new DemandeConge();
        $form = $this->createForm(DemandeCongeType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted() ) {
            if ($form->isValid()) {
                $demande->setStatus('PENDING');


                $certificateFile = $form->get('certificate')->getData();
                if ($certificateFile) {
                    try {
                        $contents = file_get_contents($certificateFile->getPathname());
                        $demande->setCertificate(base64_encode($contents));
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
        $form = $this->createForm(DemandeCongeType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $type = $demande->getTypeConge();
            $joursDemandes = $demande->getDateDebut()->diff($demande->getDateFin())->days + 1;
            $soldeConge = 30;

            if ($joursDemandes > $soldeConge) {
                $this->addFlash('error', 'Solde insuffisant.');
            } elseif ($type === 'Maladie' && !$form->get('certificate')->getData()) {
                $this->addFlash('error', 'Certificat médical requis.');
            } elseif ($type === 'Autre' && empty($demande->getAutre())) {
                $this->addFlash('error', 'Veuillez spécifier le type de congé.');
            } else {
                $certificate = $form->get('certificate')->getData();
                if ($certificate) {
                    $this->handleCertificateUpload($certificate, $demande);
                }

                $demande->setStatus('PENDING');
                $em->flush();

                $this->addFlash('success', 'Demande modifiée avec succès.');
                return $this->redirectToRoute('app_liste_conges');
            }
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
    private function handleCertificateUpload($certificate, DemandeConge $demande): void
    {
        if ($certificate) {
            $filename = uniqid() . '.' . $certificate->guessExtension();
            try {
                $certificate->move(
                    $this->getParameter('kernel.project_dir') . '/public/uploads',
                    $filename
                );
                $demande->setCertificate($filename);
            } catch (FileException $e) {
                $this->addFlash('error', 'Erreur de téléchargement du fichier.');
            }
        }
    }
}
