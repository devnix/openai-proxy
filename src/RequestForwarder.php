<?php

declare(strict_types=1);

namespace Devnix\OpenaiProxy;

use Amp\Http\HttpStatus;
use Amp\Http\Server;
use Amp\Http\Client;
use function Psl\IO\write_line;

final class RequestForwarder implements Server\RequestHandler
{
    public function __construct(
        private Client\HttpClient $client,
        private string $openAiApiHost,
        private string $openAiApiKey,
    )
    {
    }

    public function handleRequest(Server\Request $request) : Server\Response
    {
        $uri = $this->buildOpenAiUri($request);
        $clientRequest = $this->buildClientRequest($request, $uri);

        $clientResponse = $this->client->request($clientRequest);

        // Retransmitir en streaming y sanear cabeceras problemáticas
        $response = new Server\Response(
            status: $clientResponse->getStatus() ?? HttpStatus::OK,
            headers: $clientResponse->getHeaders(),
            body: $clientResponse->getBody(),
        );

        // Quitar cabeceras que pueden causar truncado o inconsistencias
        $response->removeHeader('content-length');
        $response->removeHeader('transfer-encoding');
        $response->removeHeader('content-encoding');
        $response->removeHeader('connection');
        $response->removeHeader('keep-alive');
        $response->removeHeader('trailer');

        return $response;
    }

    private function buildOpenAiUri(Server\Request $serverRequest): string
    {
        $path = $serverRequest->getUri()->getPath();
        $query = $serverRequest->getUri()->getQuery();
        $uri = rtrim($this->openAiApiHost, '/').$path;
        if ($query !== '') {
            $uri .= '?' . $query;
        }
        return $uri;
    }

    private function buildClientRequest(Server\Request $serverRequest, string $uri): Client\Request
    {
        $clientRequest = new Client\Request(
            uri: $uri,
            method: $serverRequest->getMethod(),
        );

        $clientRequest->setHeaders($serverRequest->getHeaders());
        // Sanear cabeceras hop-by-hop y de longitud; el cliente las recalcula cuando corresponde
        $clientRequest->removeHeader('host');
        $clientRequest->removeHeader('content-length');
        $clientRequest->removeHeader('transfer-encoding');
        // Evitar respuestas comprimidas que luego desajustan Content-Length
        $clientRequest->removeHeader('accept-encoding');
        $clientRequest->setHeader('Authorization', 'Bearer ' . $this->openAiApiKey);

        // Solo enviar body cuando el método lo admite y de forma reintetable
        $method = strtoupper($serverRequest->getMethod());
        if (!in_array($method, ['GET', 'HEAD'], true)) {
            $payload = $serverRequest->getBody()->buffer();
            if ($payload !== '') {
                $clientRequest->setBody($payload);
            }
        }

        return $clientRequest;
    }
}
