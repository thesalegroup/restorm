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

    public function __construct(string $entityClass, string $repositoryName, array $properties)
    {
        $this->entityClass = $entityClass;
        $this->repositoryName = $repositoryName;
        $this->properties = $properties;
    }

    function getEntityClass()
    {
        return $this->entityClass;
    }

    function getRepositoryName(): string
    {
        return $this->repositoryName;
    }
}
