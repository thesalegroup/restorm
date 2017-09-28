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

namespace TheSaleGroup\Restorm;

use TheSaleGroup\Restorm\Configuration\Configuration;
use TheSaleGroup\Restorm\Mapping\EntityMappingRegister;
use TheSaleGroup\Restorm\Connection\ConnectionRegister;
use TheSaleGroup\Restorm\Entity\EntityMetadataRegister;
use TheSaleGroup\Restorm\Mapping\EntityBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use TheSaleGroup\Restorm\EntityStore;

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

        $queryBuilder = new Query\QueryBuilder($this);

        if ($knownState) {
            // Filter only mapped fields
            $entityMetadata = $this->entityMetadataRegister->getEntityMetadata($entity);
            $writableProperties = $entityMetadata->getWritableProperties();
            $mappedKnownState = array_intersect_key((array) $knownState, array_flip($writableProperties));

            // Get normalised entity
            $currentState = $entityMetadata->getWritablePropertyValues();

            // Diff arrays to find changes
            $queryData = array_diff($currentState, $mappedKnownState);

            if (!$queryData) {
                return;
            }

            $queryBuilder->patch($entity);
        } else {
            $entityMetadata = new Entity\EntityMetadata($entity, $this->entityMappingRegister->getEntityMapping(get_class($entity)));
            $this->entityMetadataRegister->addEntityMetadata($entityMetadata);

            $writableProperties = $entityMetadata->getWritableProperties();

            $queryData = $entityMetadata->getWritablePropertyValues();

            $queryBuilder->post($entity);
        }

        // Build query and set array as body of PATCH request
        $queryBuilder
            ->setData($queryData)
            ->getQuery()
            ->getResult();
    }
}
