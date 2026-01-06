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

use Doctrine\DBAL\Exception;
use Fusio\Adapter\Util\Component\AccountKeeper;
use Fusio\Adapter\Util\Component\RequestHelper;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\Exception\ConfigurationException;
use Fusio\Engine\Exception\ConnectionNotFoundException;
use Fusio\Engine\Form\BuilderInterface;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\Request\HttpRequestHeaderConstant;
use Fusio\Engine\RequestInterface;
use Paganini\Utils\AuthorizationUtil;
use PSX\Http\Environment\HttpResponseInterface;


/**
 * UtilDispatchEvent
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
class UtilDispatchEvent extends UtilAbstract
{
    public function getName(): string
    {
        return 'Util-Dispatch-Event';
    }

    /**
     * @throws ConfigurationException
     * @throws Exception
     * @throws ConnectionNotFoundException
     */
    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): HttpResponseInterface
    {
        $eventName = $configuration->get('event');
        if (empty($eventName)) {
            throw new ConfigurationException('No event defined');
        }

        // try to resolve the user name for this event from the database (optional connection)
        $userName = $this->getWebhookUserName($eventName, $configuration);

        $headers = RequestHelper::getHeaders($request);
        $newHeaders = [];
        // authorization
        $accessToken = AccountKeeper::getInstance()->queryAccessToken($userName);
        if ($accessToken) {
            $newHeaders[HttpRequestHeaderConstant::AUTHORIZATION] = AuthorizationUtil::buildBearerToken($accessToken);
        }
        // request id
        if (isset($headers[HttpRequestHeaderConstant::X_REQUEST_ID_LOWER])) {
            $newHeaders[HttpRequestHeaderConstant::X_REQUEST_ID] = $headers[HttpRequestHeaderConstant::X_REQUEST_ID_LOWER];
        }
        // api key
        if (isset($headers[HttpRequestHeaderConstant::X_API_KEY_LOWER])) {
            $newHeaders[HttpRequestHeaderConstant::X_API_KEY] = $headers[HttpRequestHeaderConstant::X_API_KEY_LOWER];
        }
        $this->dispatcher->dispatch($eventName, $request->getPayload(), $newHeaders);

        return $this->response->build(202, [], [
            'success' => true,
            'message' => 'Event successfully dispatched'
        ]);
    }

    /**
     * @param mixed $eventName
     * @param ParametersInterface $configuration
     * @return string
     * @throws ConfigurationException
     * @throws ConnectionNotFoundException
     * @throws Exception
     */
    private function getWebhookUserName(mixed $eventName, ParametersInterface $configuration): string
    {
        $connection = $this->getConnection($configuration);

        $sql = 'SELECT u.name FROM fusio_event e JOIN fusio_webhook w ON e.id=w.event_id JOIN fusio_user u ON u.id=w.user_id WHERE e.name=:name LIMIT 1';
        $result = $connection->fetchOne($sql, ['name' => $eventName]);
        if (!$result) {
            return '';
        }

        return (string)$result;
    }

    public function configure(BuilderInterface $builder, ElementFactoryInterface $elementFactory): void
    {
        $builder->add($elementFactory->newInput('event', 'Event', 'text', 'The event which gets dispatched'));
        $builder->add($elementFactory->newConnection('connection', 'Connection', 'Optional database connection to resolve the user for the event'));
    }
}
