<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fusio\Adapter\Util\Action;

use Fusio\Adapter\Util\Component\RequestChainStorage;
use Fusio\Adapter\Util\Component\RequestFactory;
use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\Exception\ActionNotFoundException;
use Fusio\Engine\Exception\FactoryResolveException;
use Fusio\Engine\Form\BuilderInterface;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;

/**
 * UtilChain
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
class UtilChain extends ActionAbstract
{
    public function getName(): string
    {
        return 'Util-Chain';
    }

    /**
     * @throws FactoryResolveException
     * @throws ActionNotFoundException
     */
    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $actions = [
            'a',
            'b',
            'c',
            'd',
        ];

        // Clear the request-scoped storage at the beginning of chain execution
        // This ensures a clean state for each request
        RequestChainStorage::clear();

        // Execute all but the last action first
        for ($i = 0; $i < sizeof($actions) - 1; $i++) {
            $action = $actions[$i];
            $actionId = $configuration->get($action);
            if (empty($actionId)) {
                continue;
            }
            $_response = $this->processor->execute($actionId, $request, $context);
        }
        // Prepare the last action
        $actionId = $configuration->get($actions[sizeof($actions) - 1]);
        if (empty($actionId)) {
            throw new ActionNotFoundException('No action configured for the last chain element');
        }
        $lastRequest = $request;
        // Check if there are any headers to propagate before the last action
        if (!RequestChainStorage::isEmpty()) {
            // Prepare new headers
            $newHeaders = [];
            // Add X-Request-Id header if available
            if (RequestChainStorage::has('X-Request-Id')) {
                $newHeaders['X-Request-Id'] = RequestChainStorage::get('X-Request-Id');
            }
            // Add Api-Key header if available
            if (RequestChainStorage::has('X-API-Key')) {
                $newHeaders['X-API-Key'] = RequestChainStorage::get('X-API-Key');
            }
            // Create a new request with updated headers
            $lastRequest = RequestFactory::overrideRequest(
                $request,
                "",
                $newHeaders);
            // Clear storage for the next action
            RequestChainStorage::clear();
        }
        // Execute the last action
        $response = $this->processor->execute($actionId, $lastRequest, $context);

        return $response;
    }

    public function configure(BuilderInterface $builder, ElementFactoryInterface $elementFactory): void
    {
        $builder->add($elementFactory->newAction('a', 'Action A', 'Executes this action if provided, the response of the last action is returned'));
        $builder->add($elementFactory->newAction('b', 'Action B', 'Executes this action if provided, the response of the last action is returned'));
        $builder->add($elementFactory->newAction('c', 'Action C', 'Executes this action if provided, the response of the last action is returned'));
        $builder->add($elementFactory->newAction('d', 'Action D', 'Executes this action if provided, the response of the last action is returned'));
    }
}
