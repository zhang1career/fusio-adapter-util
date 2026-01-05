<?php

namespace Fusio\Adapter\Util\Action;

use Doctrine\DBAL\Connection;
use Fusio\Engine\ActionAbstract;
use Fusio\Engine\Exception\ConfigurationException;
use Fusio\Engine\Exception\ConnectionNotFoundException;
use Fusio\Engine\ParametersInterface;

abstract class UtilAbstract extends ActionAbstract
{
    /**
     * @throws ConfigurationException
     * @throws ConnectionNotFoundException
     */
    protected function getConnection(ParametersInterface $configuration): Connection
    {
        $connection = $this->connector->getConnection($configuration->get('connection') ?? 'system');
        if (!$connection instanceof Connection) {
            throw new ConfigurationException('Given connection must be a DBAL connection');
        }

        return $connection;
    }
}