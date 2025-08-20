<?php

declare(strict_types=1);

namespace Devnix\OpenaiProxy;
use Psl\Env;

final class Entrypoint
{
    public function __invoke()
    {
        $listenAddress = Env\get_var('LISTEN_ADDRESS') ?:
            throw new \RuntimeException('Missing "LISTEN_ADDRESS" env variable (e.g. LISTEN_ADDRESS=127.0.0.1:8080)');

        $openAiApiHost = getenv('OPENAI_API_HOST') ?:
            throw new \RuntimeException('Missing "OPENAI_API_HOST" env variable (e.g. OPENAI_API_HOST=https://api.openai.com)');
        $openAiApiKey = getenv('OPENAI_API_KEY') ?:
            throw new \RuntimeException('Missing "OPENAI_API_KEY" env variable (e.g. OPENAI_API_KEY=sk-0123456789');

        $application = new HttpApplication(
            $listenAddress,
            $openAiApiHost,
            $openAiApiKey,
        );

        $application->run();
    }
}