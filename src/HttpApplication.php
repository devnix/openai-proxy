<?php

declare(strict_types=1);

namespace Devnix\OpenaiProxy;

use Amp;
use Amp\ByteStream;
use Amp\Http\Server\DefaultErrorHandler;
use Amp\Http\Server\SocketHttpServer;
use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;

final class HttpApplication
{
    public function __construct(
        private string $listenAddress,
        private string $openAiApiHost,
        private string $openAiApiKey,
    ) {
    }

    public function run(): void
    {
        // Note any PSR-3 logger may be used, Monolog is only an example.
        $logHandler = new StreamHandler(ByteStream\getStdout());
        $logHandler->pushProcessor(new PsrLogMessageProcessor());
        $logHandler->setFormatter(new ConsoleFormatter());

        $logger = new Logger('server');
        $logger->pushHandler($logHandler);

        $requestHandler = new RequestForwarder(
            Amp\Http\Client\HttpClientBuilder::buildDefault(),
            $this->openAiApiHost,
            $this->openAiApiKey,
        );

        $errorHandler = new DefaultErrorHandler();

        $server = SocketHttpServer::createForDirectAccess($logger);
        $server->expose($this->listenAddress);
        $server->start($requestHandler, $errorHandler);

        // Serve requests until SIGINT or SIGTERM is received by the process.
        Amp\trapSignal([SIGINT, SIGTERM]);

        $server->stop();
    }
}
