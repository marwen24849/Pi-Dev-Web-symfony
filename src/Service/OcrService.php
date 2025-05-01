<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class OcrService
{
    private HttpClientInterface $client;
    private string $apiKey = 'helloworld'; // Demo key, you can replace with your free API key later

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function extractText(string $filePath): ?string
    {
        $imageData = base64_encode(file_get_contents($filePath));

        $response = $this->client->request('POST', 'https://api.ocr.space/parse/image', [
            'headers' => [
                'apikey' => $this->apiKey,
            ],
            'body' => [
                'base64Image' => 'data:image/png;base64,' . $imageData,
                'language' => 'fre',
            ],
        ]);

        $content = $response->toArray(false);

        if (isset($content['ParsedResults'][0]['ParsedText'])) {
            return $content['ParsedResults'][0]['ParsedText'];
        }

        return null;
    }
}
