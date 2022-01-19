<?php

declare(strict_types=1);

namespace BehatApiContext\Service\ResetManager;

use Symfony\Component\HttpKernel\KernelInterface;

interface ResetManagerInterface
{
    public function needsReset(string $httpMethod): bool;

    public function reset(KernelInterface $kernel): void;
}
