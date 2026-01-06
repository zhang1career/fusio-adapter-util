<?php

namespace Fusio\Adapter\Util\Action;

use Doctrine\DBAL\Connection;
use Fusio\Engine\ActionAbstract;
use Fusio\Engine\Exception\ConfigurationException;
use Fusio\Engine\Exception\ConnectionNotFoundException;
use Fusio\Engine\ParametersInterface;

/**
 * UtilAbstract
 * supported configuration:
 * - connection: name of the connection to use (optional, default: system)
 *
 * @author  Rongjin Zhang <rongjin.zh@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
abstract class UtilAbstract extends ActionAbstract
{
    /**
     * Get the database connection based on the configuration
     *
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