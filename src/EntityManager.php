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
     *
     * @var ConnectionRegister
     */
    protected $connectionRegister;

    protected function __construct(EntityMappingRegister $entityMappingRegister, ConnectionRegister $connectionRegister)
    {
        $this->entityMappingRegister = $entityMappingRegister;
        $this->connectionRegister = $connectionRegister;
        $this->repositoryRegister = new RepositoryRegister;
    }

    public static function createFromConfiguration(Configuration $configuration): EntityManager
    {
        return self::$instance = new EntityManager($configuration->getEntityMappingRegister(), $configuration->getConnectionRegister());
    }

    public function getRepository($entity): EntityRepository
    {
        $entityName = is_object($entity) ? get_class($entity) : $entity;

        $entityMapping = $this->entityMappingRegister->getEntityMapping($entityName);
        $repositoryClass = $entityMapping->getRepositoryName();

        if (!$this->repositoryRegister->hasRepository($repositoryClass)) {

            if (!is_a($repositoryClass, RepositoryInterface::class, true)) {
                throw new \Exception('Repository must extend RepositoryInterface');
            }

            $repository = new $repositoryClass($this, $entityName);
            $this->repositoryRegister->addRepository($repository);
        }

        return $this->repositoryRegister->getRepository($repositoryClass);
    }
    
    function getEntityMappingRegister(): EntityMappingRegister
    {
        return $this->entityMappingRegister;
    }

    function getConnectionRegister(): ConnectionRegister
    {
        return $this->connectionRegister;
    }

    
    public function persist($entity)
    {
        
    }
}
