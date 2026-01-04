<?php

namespace Fusio\Adapter\Util\Component;

use Fusio\Engine\Request;
use Fusio\Engine\Request\HttpRequestContext;
use Fusio\Engine\RequestInterface;

class RequestHelper
{
    public const X_REQUEST_ID = 'X-Request-Id';

    public const X_API_KEY = 'X-API-Key';

    public static function overrideRequest(RequestInterface $request,
                                           string           $method,
                                           array            $newHeaders): RequestInterface
    {
        // update context
        $newContext = null;
        $originContext = $request->getContext();
        if ($originContext instanceof HttpRequestContext) {
            // merge existing headers and add X-Request-Id
            $originHeaders = $originContext->getRequest()->getHeaders();
            $newHeaders = array_merge($newHeaders, $originHeaders);

            // Create a new request with updated fields
            $originContextRequest = $originContext->getRequest();
            $newContextRequest = clone $originContextRequest;
            if ($method) {
                $newContextRequest->setMethod($method);
            }
            if ($newHeaders) {
                $newContextRequest->setHeaders($newHeaders);
            }

            // Create a new HttpRequestContext with the modified request
            $newContext = new HttpRequestContext(
                $newContextRequest,
                $originContext->getParameters()
            );
        }

        // Create a new request with the updated context
        return new Request(
            $request->getArguments(),
            $request->getPayload(),
            $newContext ?? $request->getContext()
        );
    }

    public static function getHeaders(RequestInterface $request): array
    {
        $headers = [];
        $context = $request->getContext();
        if ($context instanceof HttpRequestContext) {
            $headers = $context->getRequest()->getHeaders();
        }
        return $headers;
    }
}