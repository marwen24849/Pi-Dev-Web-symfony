<?php

namespace App\Service;

use App\Entity\Projet; // Assuming your Project entity namespace
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Google\Service\Calendar\ConferenceData;
use Google\Service\Calendar\CreateConferenceRequest;
use Google\Service\Calendar\ConferenceSolutionKey;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Psr\Log\LoggerInterface; // For logging errors

class GoogleCalendarService
{
    private Client $client;
    private RequestStack $requestStack;
    private UrlGeneratorInterface $router;
    private LoggerInterface $logger;
    private string $redirectUri;

    public function __construct(
        string $googleClientId,
        string $googleClientSecret,
        string $googleRedirectUri,
        RequestStack $requestStack,
        UrlGeneratorInterface $router,
        LoggerInterface $logger
    ) {
        $this->client = new Client();
        $this->client->setClientId($googleClientId);
        $this->client->setClientSecret($googleClientSecret);
        $this->redirectUri = $googleRedirectUri; // Store redirect URI
        $this->client->setRedirectUri($this->redirectUri);
        $this->client->setAccessType('offline'); // Important for getting refresh token
        $this->client->setPrompt('select_account consent'); // Force consent screen for refresh token
        $this->client->addScope(Calendar::CALENDAR_EVENTS); // Scope for creating events
        $this->client->addScope('email'); // Basic scopes usually included
        $this->client->addScope('profile');

        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->logger = $logger;
    }

    // Get an authenticated Google API Client
    public function getClient(): ?Client
    {
        $session = $this->requestStack->getSession();
        $accessToken = $session->get('google_access_token');

        if ($accessToken) {
            $this->client->setAccessToken($accessToken);
            if ($this->client->isAccessTokenExpired()) {
                $refreshToken = $session->get('google_refresh_token');
                if ($refreshToken) {
                    try {
                        $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
                        $newAccessToken = $this->client->getAccessToken();
                        // Persist the new access token and potentially the new refresh token
                        $session->set('google_access_token', $newAccessToken);
                        // Google might issue a new refresh token, update if provided
                        if (isset($newAccessToken['refresh_token'])) {
                            $session->set('google_refresh_token', $newAccessToken['refresh_token']);
                        }
                    } catch (\Exception $e) {
                        $this->logger->error('Failed to refresh Google token: ' . $e->getMessage());
                        // Failed to refresh token, might need re-authentication
                        $session->remove('google_access_token');
                        $session->remove('google_refresh_token');
                        return null; // Indicate auth is needed
                    }
                } else {
                    // No refresh token, cannot refresh
                    $session->remove('google_access_token');
                    return null; // Indicate auth is needed
                }
            }
            return $this->client;
        }
        return null; // Not authenticated
    }

    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    // Handle the callback from Google
    public function handleCallback(string $code): bool
    {
        try {
            $accessToken = $this->client->fetchAccessTokenWithAuthCode($code);
            if (isset($accessToken['error'])) {
                $this->logger->error('Google Auth Error: ' . $accessToken['error_description'] ?? $accessToken['error']);
                return false;
            }

            $this->client->setAccessToken($accessToken);

            // Store tokens in session (Consider storing refresh_token in DB against User entity for persistence)
            $session = $this->requestStack->getSession();
            $session->set('google_access_token', $accessToken);
            if (isset($accessToken['refresh_token'])) {
                $session->set('google_refresh_token', $accessToken['refresh_token']);
                // TODO: Persist $accessToken['refresh_token'] in your User entity database field
            }

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to fetch Google access token: ' . $e->getMessage());
            return false;
        }
    }

    // Create Calendar Event with Google Meet Link
    public function createEventWithMeet(Client $authenticatedClient, Projet $projet): ?Event
    {
        if (!$projet->getDateDebut()) {
            $this->logger->warning('Project has no start date, cannot create calendar event.', ['project_id' => $projet->getId()]);
            return null; // Cannot create event without start date
        }

        $calendarService = new Calendar($authenticatedClient);
        $calendarId = 'primary'; // Use the user's primary calendar

        $event = new Event([
            'summary' => 'Project: ' . $projet->getNomProjet(),
            'description' => sprintf(
                "Project Details:\nName: %s\nResponsable: %s\nTeam: %s\nDates: %s to %s",
                $projet->getNomProjet(),
                $projet->getResponsable() ?? 'N/A',
                $projet->getEquipe() ? $projet->getEquipe()->getName() : 'N/A', // Assuming Equipe has getName()
                $projet->getDateDebut()->format('Y-m-d'),
                $projet->getDateFin() ? $projet->getDateFin()->format('Y-m-d') : 'Ongoing'
            ),
            'start' => new EventDateTime([
                // Google Calendar API expects RFC3339 date/datetime
                // Use 'date' for all-day events if no specific time needed
                'date' => $projet->getDateDebut()->format('Y-m-d'),
                // 'dateTime' => $projet->getDateDebut()->format(\DateTimeInterface::RFC3339),
                // 'timeZone' => 'Your/Timezone', // e.g., 'Europe/Paris' or get from user profile
            ]),
            'end' => new EventDateTime([
                // If no end date, make it same as start for an all-day event placeholder
                'date' => $projet->getDateFin() ? $projet->getDateFin()->format('Y-m-d') : $projet->getDateDebut()->format('Y-m-d'),
                // 'dateTime' => $projet->getDateFin() ? $projet->getDateFin()->format(\DateTimeInterface::RFC3339) : null,
                // 'timeZone' => 'Your/Timezone',
            ]),
            // --- Request Google Meet Link ---
            'conferenceData' => new ConferenceData([
                'createRequest' => new CreateConferenceRequest([
                    'requestId' => uniqid('meet_', true), // Unique ID for the request
                    'conferenceSolutionKey' => new ConferenceSolutionKey(['type' => 'hangoutsMeet'])
                ])
            ]),
            // --- End Google Meet Request ---
        ]);

        try {
            // Request conference data to be returned in the response
            $optParams = ['conferenceDataVersion' => 1];
            $createdEvent = $calendarService->events->insert($calendarId, $event, $optParams);
            return $createdEvent;
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Failed to create Google Calendar event for project %d: %s', $projet->getId(), $e->getMessage()));
            // Handle error (e.g., throw exception, return null)
            return null;
        }
    }
}