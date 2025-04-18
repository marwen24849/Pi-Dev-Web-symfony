<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ZoomService
{
    private $httpClient;
    private $clientId;
    private $clientSecret;
    private $accountId;

    public function __construct(HttpClientInterface $httpClient, ParameterBagInterface $params)
    {
        $this->httpClient = $httpClient;
        $this->clientId = $_ENV['ZOOM_CLIENT_ID'];  // Use direct variable name
        $this->clientSecret = $_ENV['ZOOM_CLIENT_SECRET'];
        $this->accountId = $_ENV['ZOOM_ACCOUNT_ID'];
    }

    public function getAccessToken(): string
    {
        $credentials = base64_encode($this->clientId . ':' . $this->clientSecret);
        $url = "https://zoom.us/oauth/token?grant_type=account_credentials&account_id={$this->accountId}";

        $response = $this->httpClient->request('POST', $url, [
            'headers' => [
                'Authorization' => 'Basic ' . $credentials
            ]
        ]);

        $data = $response->toArray();
        return $data['access_token'];
    }

    public function createMeeting(): ?string
    {
        $accessToken = $this->getAccessToken();

        $payload = [
            'topic' => 'Symfony Zoom Meeting',
            'type' => 2, // Scheduled meeting
            'start_time' => (new \DateTime('+1 day'))->format('Y-m-d\TH:i:s\Z'),
            'duration' => 60,
            'timezone' => 'UTC',
            'agenda' => 'Discuss Zoom API integration in Symfony',
            'settings' => [
                'host_video' => true,
                'participant_video' => true,
                'mute_upon_entry' => true,
                'waiting_room' => true,
            ]
        ];

        $response = $this->httpClient->request('POST', 'https://api.zoom.us/v2/users/me/meetings', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => $payload
        ]);

        $data = $response->toArray();

        return $data['join_url'] ?? null;
    }
}
