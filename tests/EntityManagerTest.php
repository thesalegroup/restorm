<?php

namespace TheSaleGroup\Restorm\Tests;

use PHPUnit\Framework\TestCase;
use TheSaleGroup\Restorm\Mapping\EntityBuilder;
use TheSaleGroup\Restorm\Mapping\EntityMappingRegister;
use TheSaleGroup\Restorm\RepositoryRegister;
use TheSaleGroup\Restorm\Entity\EntityMetadataRegister;
use TheSaleGroup\Restorm\Connection\ConnectionRegister;
use TheSaleGroup\Restorm\EntityStore;
use TheSaleGroup\Restorm\Normalizer\Normalizer;
use TheSaleGroup\Restorm\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcher;
use ProxyManager\Factory\LazyLoadingGhostFactory;

/**
 * @covers TheSaleGroup\Restorm\EntityManager
 */
class EntityManagerTest extends TestCase
{
    /**
     * @var RepositoryRegister
     */
    private $repositoryRegister;

    /**
     * @var EntityMappingRegister
     */
    private $entityMappingRegister;
    /**
     * @var EntityMetadataRegister
     */
    private $entityMetadataRegister;

    /**
     * @var ConnectionRegister
     */
    private $connectionRegister;

    /**
     * @var EntityBuilder
     */
    private $entityBuilder;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var EntityStore
     */
    private $entityStore;

    /**
      * @var LazyLoadingGhostFactory
      */
    private $proxyFactory;

    /**
     * @var Normalizer
     */
    private $normalizer;

    private $entityManager;

    public function setUp()
    {
        $this->repositoryRegister = $this->createMock(RepositoryRegister::class);
        $this->entityMappingRegister = $this->createMock(EntityMappingRegister::class);
        $this->entityMetadataRegister = $this->createMock(EntityMetadataRegister::class);
        $this->connectionRegister = $this->createMock(ConnectionRegister::class);
        $this->entityBuilder = $this->createMock(EntityBuilder::class);
        $this->eventDispatcher = $this->createMock(EventDispatcher::class);
        $this->entityStore = $this->createMock(EntityStore::class);
        $this->proxyFactory = $this->createMock(LazyLoadingGhostFactory::class);
        $this->normalizer = $this->createMock(Normalizer::class);
    }

    /**
     * @test
     */
    public function doesThisWork()
    {
        $this->assertTrue(true);
    }
}
