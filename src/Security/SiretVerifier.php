<?php

namespace App\Security;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class SiretVerifier
{
    private $httpClient;
    private $apiToken;

    public function __construct(HttpClientInterface $httpClient, string $apiToken)
    {
        $this->httpClient = $httpClient;
        $this->apiToken = $apiToken;
    }

    // public function verifySiret(string $siret): bool
    // {
    //     $url = "https://api.insee.fr/entreprises/sirene/V3/siret/{$siret}";

    //     try {
    //         $response = $this->httpClient->request('GET', $url, [
    //             'headers' => [
    //                 'Authorization' => 'Bearer ' . $this->apiToken,
    //             ],
    //         ]);

    //         if (200 !== $response->getStatusCode()) {
    //             return false;
    //         }

    //         $data = $response->toArray();
            
    //         if (isset($data['etablissement']['uniteLegale']['etatAdministratifUniteLegale']) && 'A' === $data['etablissement']['uniteLegale']['etatAdministratifUniteLegale']) {
    //             return true;
    //         }

    //         return false;
    //     } catch (\Exception $exception) {
    //         return false;
    //     }
    // }

    public function fetchSiretData(string $siret): ?array
{
    $url = "https://api.insee.fr/entreprises/sirene/V3/siret/{$siret}";

    try {
        $response = $this->httpClient->request('GET', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiToken,
            ],
        ]);

        if (200 !== $response->getStatusCode()) {
            return null;
        }

        return $response->toArray();
    } catch (\Exception $exception) {
        return null;
    }
}

public function extractSiretInfo(array $siretData): ?array
{
    if (!isset($siretData['etablissement'])) {
        return null;
    }

    $etablissement = $siretData['etablissement'];
    $uniteLegale = $etablissement['uniteLegale'] ?? null;
    $adresseEtablissement = $etablissement['adresseEtablissement'] ?? null;

    if (null === $uniteLegale || null === $adresseEtablissement) {
        return null;
    }

    $denomination = $uniteLegale['denominationUniteLegale'] ?? 'N/A';
    $isActive = $uniteLegale['etatAdministratifUniteLegale'] === 'A';

    $adresseParts = [
        $adresseEtablissement['numeroVoieEtablissement'] ?? '',
        $adresseEtablissement['indiceRepetitionEtablissement'] ?? '',
        $adresseEtablissement['typeVoieEtablissement'] ?? '',
        $adresseEtablissement['libelleVoieEtablissement'] ?? '',
        $adresseEtablissement['codePostalEtablissement'] ?? '',
        $adresseEtablissement['libelleCommuneEtablissement'] ?? '',
    ];

    $adresse = implode(' ', array_filter($adresseParts, function ($part) {
        return !empty($part);
    }));

    return [
        'denomination' => $denomination,
        'isActive' => $isActive,
        'adresse' => $adresse ?: 'Adresse non disponible',
    ];
}
}