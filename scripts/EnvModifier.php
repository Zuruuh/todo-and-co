<?php

namespace Scripts;

use stdClass;

class EnvModifier
{
    private const ROOT = '/srv/';
    private const ENV = 'APP_ENV=';
    private const DEV = 'dev';
    private const TEST = 'test';

    private const ENV_FILE = '.env.local';
    private const ENV_REGEX = '/^' . self::ENV . '\w*$/m';

    private string $path;

    public function __construct(array $params)
    {
        $this->path = sprintf('%s/%s', self::ROOT, self::ENV_FILE);
        $env = $this->getEnvParam((object) $params);
        if (!$env->set) {
            $this->toggleEnv();

            return;
        }

        $this->createEnvFile();
        $this->replaceEnv($env->value);
    }

    private function getEnvParam(stdClass $params): stdClass
    {
        $env = property_exists($params, 'e')
            ? $params->e
            : (property_exists($params, 'env')
                ? $params->env
                : null
            );

        return (object) ['set' => (bool) $env, 'value' => $env];
    }

    private function toggleEnv(): void
    {
        $env = $this->getCurrentEnv() ?? self::DEV;

        if (self::DEV === $env || self::TEST !== $env) {
            $this->replaceEnv(self::TEST);

            return;
        }

        $this->replaceEnv(self::DEV);
    }

    private function replaceEnv(string $env): void
    {
        $content = file_get_contents($this->path);
        $this->setEnv($env, $content);
    }

    // === #

    private function setEnv(string $env, string $content): void
    {
        file_put_contents(
            $this->path,
            preg_match(self::ENV_REGEX, $content)
                ? preg_replace(
                    self::ENV_REGEX,
                    self::ENV . $env,
                    $content
                ) : sprintf(
                    '%s%s%s',
                    self::ENV . $env,
                    "\n\n",
                    $content
                )
        );
    }

    private function createEnvFile(): void
    {
        if (!file_exists($this->path)) {
            file_put_contents($this->path, '');
        }
    }

    private function getCurrentEnv(): ?string
    {
        $this->createEnvFile();

        $content = file_get_contents($this->path);
        $matches = [];
        preg_match('/APP_ENV=(\w+)/', $content, $matches);

        return array_key_exists(1, $matches) ? $matches[1] : null;
    }
}

new EnvModifier(
    getopt('e:', ['env:'])
);
