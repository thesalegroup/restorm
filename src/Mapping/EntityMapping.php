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
    private $indentifier;

    public function __construct(string $entityClass, string $repositoryName, array $properties, array $paths)
    {
        $this->entityClass = $entityClass;
        $this->repositoryName = $repositoryName;
        $this->properties = $properties;
        $this->paths = $paths;
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
}
