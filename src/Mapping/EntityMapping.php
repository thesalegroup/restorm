<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Robwasripped\Restorm\Mapping;

/**
 * Description of EntityMapping
 *
 * @author Rob Treacy <email@roberttreacy.com>
 */
class EntityMapping
{
    const PATH_LIST = 'list';
    const PATH_GET = 'get';
    const PATH_POST = 'post';
    const PATH_PUT = 'put';
    const PATH_PATCH = 'patch';
    const PATH_DELETE = 'delete';

    /**
     * @var string
     */
    private $entityClass;

    /**
     * @var string
     */
    private $repositoryName;

    /**
     * @var array
     */
    private $properties;

    /**
     * @var array
     */
    private $paths;

    /**
     * @var string
     */
    private $connection;

    /**
     * @var string
     */
    private $indentifier;

    public function __construct(string $entityClass, string $repositoryName, array $properties, array $paths, string $connection)
    {
        $this->entityClass = $entityClass;
        $this->repositoryName = $repositoryName;
        $this->properties = $properties;
        $this->paths = $paths;
        $this->connection = $connection;
    }

    public function getEntityClass()
    {
        return $this->entityClass;
    }

    public function getRepositoryName(): string
    {
        return $this->repositoryName;
    }

    public function getIdentifierName()
    {
        if (!$this->indentifier) {

            foreach ($this->properties as $propertyName => $property) {

                if (!isset($property['identifier']) || !$property['identifier'] === true) {
                    continue;
                }

                if ($this->indentifier) {
                    throw new \Exception('Cannot have more than one identifier per entity');
                }

                $this->indentifier = $propertyName;
            }
        }

        return $this->indentifier;
    }

    public function getpath($method)
    {
        return $this->paths[$method];
    }

    public function getConnection()
    {
        return $this->connection;
    }

    function getProperties()
    {
        return $this->properties;
    }
}
