<?php

namespace Fusio\Adapter\Util\Component;

use Fusio\Engine\Request;
use Fusio\Engine\Request\HttpRequestContext;
use Fusio\Engine\RequestInterface;

/**
 * RequestHelper
 *
 * @author  Rongjin Zhang <rongjin.zh@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
class RequestHelper
{
    /**
     * Override the headers of a given request.
     *
     * @param RequestInterface $request
     * @param string $method
     * @param array $newHeaders
     * @return RequestInterface
     */
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

    /**
     * Retrieve headers from the request context if available.
     *
     * @param RequestInterface $request
     * @return array
     */
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