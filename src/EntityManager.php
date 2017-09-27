<?php
/*
 * The MIT License
 *
 * Copyright 2017 Rob Treacy <email@roberttreacy.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Robwasripped\Restorm;

use Robwasripped\Restorm\Configuration\Configuration;
use Robwasripped\Restorm\Mapping\EntityMappingRegister;
use Robwasripped\Restorm\Connection\ConnectionRegister;
use Robwasripped\Restorm\Entity\EntityMetadataRegister;
use Robwasripped\Restorm\Mapping\EntityBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Robwasripped\Restorm\EntityStore;

/**
 * Description of EntityManager
 *
 * @author Rob Treacy <email@roberttreacy.com>
 */
class EntityManager
{
    /**
     * @var EntityManager
     */
    private static $instance;

    /**
     *
     * @var RepositoryRegister
     */
    protected $repositoryRegister;

    /**
     *
     * @var EntityMappingRegister
     */
    protected $entityMappingRegister;

    /**
     * @var EntityMetadataRegister
     */
    protected $entityMetadataRegister;

    /**
     *
     * @var ConnectionRegister
     */
    protected $connectionRegister;

    /**
     *
     * @var EntityBuilder
     */
    protected $entityBuilder;

    /**
     *
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     *
     * @var EntityStore
     */
    protected $entityStore;

    protected function __construct(EntityMappingRegister $entityMappingRegister, ConnectionRegister $connectionRegister, EventDispatcherInterface $eventDispatcher)
    {
        $this->entityMappingRegister = $entityMappingRegister;
        $this->connectionRegister = $connectionRegister;
        $this->eventDispatcher = $eventDispatcher;
        $this->repositoryRegister = new RepositoryRegister;
        $this->entityMetadataRegister = new EntityMetadataRegister;
        $this->entityStore = new EntityStore($this->entityMappingRegister, $this->entityMetadataRegister);
        $this->entityBuilder = new EntityBuilder($this->entityMappingRegister, $this->entityMetadataRegister);

        $this->eventDispatcher->addSubscriber($this->entityStore);
    }

    public static function createFromConfiguration(Configuration $configuration): EntityManager
    {
        return self::$instance = new EntityManager($configuration->getEntityMappingRegister(), $configuration->getConnectionRegister(), $configuration->getEventDispatcher());
    }

    public function getRepository($entity): EntityRepository
    {
        $entityClass = is_object($entity) ? get_class($entity) : $entity;

        $entityMapping = $this->entityMappingRegister->getEntityMapping($entityClass);
        $repositoryClass = $entityMapping->getRepositoryName();

        if (!$this->repositoryRegister->hasRepository($entityClass)) {

            if (!is_a($repositoryClass, RepositoryInterface::class, true)) {
                throw new \Exception('Repository must extend RepositoryInterface');
            }

            $repository = new $repositoryClass($this, $entityClass);
            $this->repositoryRegister->addRepository($entityClass, $repository);
        }

        return $this->repositoryRegister->getRepository($entityClass);
    }

    public function getEntityMappingRegister(): EntityMappingRegister
    {
        return $this->entityMappingRegister;
    }

    public function getConnectionRegister(): ConnectionRegister
    {
        return $this->connectionRegister;
    }

    public function getEntityMetadataRegister(): EntityMetadataRegister
    {
        return $this->entityMetadataRegister;
    }

    public function getEntityBuilder(): EntityBuilder
    {
        return $this->entityBuilder;
    }

    public function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }

    public function persist($entity)
    {
        $knownState = $this->entityStore->getEntityData($entity);

        // Filter only mapped fields
        $entityMetadata = $this->entityMetadataRegister->getEntityMetadata($entity);
        $writableProperties = $entityMetadata->getWritableProperties();
        $mappedKnownState = array_intersect(array_keys((array) $knownState), $writableProperties);

        // Get normalised entity
        $currentState = array();
        foreach ($writableProperties as $propertyName) {
            $currentState[$propertyName] = $entityMetadata->getPropertyValue($propertyName);
        }

        // Diff arrays to find changes
        $changes = array_diff($currentState, $mappedKnownState);

        if (!$changes) {
            return;
        }

        // Build query and set array as body of PATCH request
        $queryBuilder = new Query\QueryBuilder($this);
        $queryBuilder->patch($entity)
            ->setData($changes)
            ->getQuery()
            ->getResult();
    }
}
