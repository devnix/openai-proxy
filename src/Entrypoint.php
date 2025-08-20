<?php

declare(strict_types=1);

namespace Devnix\OpenaiProxy;
use Psl\Env;

final class Entrypoint
{
    public function __invoke()
    {
        $listenAddress = Env\get_var('LISTEN_ADDRESS');
        $openAiApiHost = getenv('OPENAI_API_HOST');
        $openAiApiKey = getenv('OPENAI_API_KEY');

        $application = new HttpApplication(
            $listenAddress,
            $openAiApiHost,
            $openAiApiKey,
        );

        $application->run();
    }
}