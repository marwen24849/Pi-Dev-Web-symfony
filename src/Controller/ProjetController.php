<?php

namespace App\Controller;

use App\Entity\Projet;
use App\Form\ProjetType;
use App\Repository\ProjetRepository; // <-- Import Repository
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Service\GoogleCalendarService; // <-- Import service
use Symfony\Component\HttpFoundation\JsonResponse; // <-- For AJAX response
use Symfony\Component\HttpFoundation\RedirectResponse; // <-- For redirect


#[Route('/projet')]
class ProjetController extends AbstractController
{
    // Combines Index and New functionality
    #[Route('/', name: 'app_projet_manage', methods: ['GET', 'POST'])] // Changed route name and path
    public function manage(Request $request, ProjetRepository $projetRepository, EntityManagerInterface $entityManager): Response // Inject Repository
    {
        // --- Form for creating a new Projet ---
        $projet = new Projet();
        $creation_form = $this->createForm(ProjetType::class, $projet);
        $creation_form->handleRequest($request);

        if ($creation_form->isSubmitted() && $creation_form->isValid()) {
            $entityManager->persist($projet);
            $entityManager->flush();
            $this->addFlash('success', 'Projet ajouté avec succès.');
            // Redirect back to the same manage page
            return $this->redirectToRoute('app_projet_manage', [], Response::HTTP_SEE_OTHER);
        }
        // --- End Form Handling ---

        // --- Filtering ---
        $filterMonth = $request->query->get('filter_month');
        $filterYear = $request->query->get('filter_year');
        $filterInProgress = $request->query->has('filter_in_progress'); // Check if checkbox exists

        // Ensure findByFilters exists in your ProjetRepository
        $projets = $projetRepository->findByFilters(
            $filterMonth ? (int)$filterMonth : null,
            $filterYear ? (int)$filterYear : null,
            $filterInProgress ?: null // Pass true only if checkbox was present
        );
        // --- End Filtering ---

        // Data for filter dropdowns
        $currentYear = date('Y');
        $years = range($currentYear - 5, $currentYear + 5);
        $months = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin',
            7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];

        // Render the combined management template
        return $this->render('projet/manage.html.twig', [
            'projets' => $projets,
            'creation_form' => $creation_form->createView(), // Pass form view
            'filter_months' => $months,
            'filter_years' => $years,
            'current_filter_month' => $filterMonth,
            'current_filter_year' => $filterYear,
            'current_filter_in_progress' => $filterInProgress,
        ]);
    }

    // Show action (keep if needed) - Ensure route parameter has regex
    #[Route('/{id<\d+>}', name: 'app_projet_show', methods: ['GET'])]
    public function show(Projet $projet): Response
    {
        // Make sure show.html.twig exists and uses correct variable names (nomProjet etc)
        return $this->render('projet/show.html.twig', [
            'projet' => $projet,
        ]);
    }

    // Edit action - Update redirect, use distinct form variable name
    #[Route('/{id<\d+>}/edit', name: 'app_projet_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Projet $projet, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Projet mis à jour avec succès.');
            // Redirect back to the manage page
            return $this->redirectToRoute('app_projet_manage', [], Response::HTTP_SEE_OTHER);
        }

        // Render the EDIT template
        return $this->render('projet/edit.html.twig', [
            'projet' => $projet,
            'edit_form' => $form->createView(), // Pass form view as 'edit_form'
        ]);
    }

    // Delete action - Update redirect, fix CSRF check
    #[Route('/{id<\d+>}', name: 'app_projet_delete', methods: ['POST'])]
    public function delete(Request $request, Projet $projet, EntityManagerInterface $entityManager): Response
    {
        // Check CSRF token from POST request body
        if ($this->isCsrfTokenValid('delete'.$projet->getId(), $request->request->get('_token'))) {
            $entityManager->remove($projet);
            $entityManager->flush();
            $this->addFlash('success', 'Projet supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Jeton CSRF invalide.');
        }

        // Redirect back to the manage page
        return $this->redirectToRoute('app_projet_manage', [], Response::HTTP_SEE_OTHER);
    }

    // --- ADD TO CALENDAR Action ---
    #[Route('/{id<\d+>}/add-to-calendar', name: 'app_projet_add_to_calendar', methods: ['POST'])] // Changed to POST for AJAX best practice
    public function addToCalendar(
        Request $request,
        Projet $projet,
        GoogleCalendarService $calendarService,
        EntityManagerInterface $entityManager // Keep if needed for other logic
    ): Response {
        // Check if user is authenticated with Google Calendar
        $client = $calendarService->getClient();

        if (!$client) {
            // --- Needs Authentication ---
            // Store the current URL (or intended target) so we can redirect back after auth
            $request->getSession()->set('_google_auth_target_path', $this->generateUrl('app_projet_manage')); // Or specific project show page

            // Get the Google Auth URL
            $authUrl = $calendarService->getAuthUrl();

            // For AJAX requests, return JSON telling frontend to redirect
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['status' => 'auth_required', 'authUrl' => $authUrl]);
            } else {
                // For non-AJAX, redirect directly
                return new RedirectResponse($authUrl);
            }
        }

        // --- User is Authenticated - Proceed ---
        $createdEvent = $calendarService->createEventWithMeet($client, $projet);

        if ($createdEvent) {
            // Event created successfully
            $eventData = [
                'status' => 'success',
                'message' => 'Event added to Google Calendar!',
                'eventId' => $createdEvent->getId(),
                'eventUrl' => $createdEvent->getHtmlLink(),
                'meetUrl' => $createdEvent->getHangoutLink(), // This is the Google Meet link
                'summary' => $createdEvent->getSummary(),
                'start' => $createdEvent->getStart()->getDate() ?? $createdEvent->getStart()->getDateTime(),
                'end' => $createdEvent->getEnd()->getDate() ?? $createdEvent->getEnd()->getDateTime(),
            ];
            // Add Flash message for non-AJAX fallback (though less likely now)
            $this->addFlash('success', 'Event added to Google Calendar!');

        } else {
            // Failed to create event
            $eventData = [
                'status' => 'error',
                'message' => 'Failed to add event to Google Calendar. Check logs.'
            ];
            $this->addFlash('error', 'Failed to add event to Google Calendar.');
        }

        // Return JSON response for AJAX request
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse($eventData);
        } else {
            // Fallback redirect if not AJAX (less ideal user experience)
            return $this->redirectToRoute('app_projet_manage');
        }
    }
}