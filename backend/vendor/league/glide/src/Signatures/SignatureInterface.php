<?php

declare(strict_types=1);

namespace League\Glide\Signatures;

interface SignatureInterface
{
    /**
     * Add an HTTP signature to manipulation params.
     *
     * @param string               $path   The resource path.
     * @param array<string, mixed> $params The manipulation params.
     *
     * @return array<string, mixed> The updated manipulation params.
     */
    public function addSignature(string $path, array $params): array;

    /**
     * Validate a request signature.
     *
     * @param string               $path   The resource path.
     * @param array<string, mixed> $params The manipulation params.
     *
     * @throws SignatureException
     */
    public function validateRequest(string $path, array $params): void;
}
