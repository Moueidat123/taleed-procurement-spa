<?php

namespace App\Support;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

/**
 * Stable JSON error envelope for /api/* (docs/production/07-API-CONTRACT.md):
 *   {"error": {"code", "message", "fields"?}, "requestId"}
 * Never includes traces, SQL, config or model attributes.
 */
final class ApiErrorRenderer
{
    public static function render(Throwable $e, Request $request): JsonResponse
    {
        [$status, $code, $message, $fields] = match (true) {
            $e instanceof ValidationException => [422, 'validation_failed', 'The submitted data is invalid.', $e->errors()],
            $e instanceof AuthenticationException => [401, 'unauthenticated', 'Sign in to continue.', null],
            $e instanceof AuthorizationException => [403, 'forbidden', 'You do not have access to this action.', null],
            $e instanceof ModelNotFoundException => [404, 'not_found', 'The requested resource was not found.', null],
            $e instanceof TokenMismatchException => [419, 'csrf_token_mismatch', 'Your session expired. Refresh and try again.', null],
            $e instanceof TooManyRequestsHttpException => [429, 'rate_limited', 'Too many attempts. Try again later.', null],
            $e instanceof HttpExceptionInterface => self::fromHttp($e),
            default => [500, 'server_error', 'An unexpected error occurred.', null],
        };

        $body = ['error' => array_filter(['code' => $code, 'message' => $message, 'fields' => $fields], fn ($v) => $v !== null)];
        $body['requestId'] = $request->attributes->get('request_id');

        $headers = $e instanceof HttpExceptionInterface ? $e->getHeaders() : [];

        return new JsonResponse($body, $status, $headers);
    }

    /** @return array{int, string, string, null} */
    private static function fromHttp(HttpExceptionInterface $e): array
    {
        $status = $e->getStatusCode();

        return match ($status) {
            404 => [404, 'not_found', 'The requested resource was not found.', null],
            405 => [405, 'method_not_allowed', 'This method is not allowed for the resource.', null],
            409 => [409, 'conflict', 'The resource changed. Reload and try again.', null],
            419 => [419, 'csrf_token_mismatch', 'Your session expired. Refresh and try again.', null],
            429 => [429, 'rate_limited', 'Too many attempts. Try again later.', null],
            503 => [503, 'unavailable', 'The service is temporarily unavailable.', null],
            default => [$status, 'http_error', 'The request could not be completed.', null],
        };
    }
}
