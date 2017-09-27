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

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use TheSaleGroup\Restorm\Mapping\EntityMappingRegister;
use TheSaleGroup\Restorm\Event\PreBuildEvent;
use TheSaleGroup\Restorm\Event\PostBuildEvent;
use TheSaleGroup\Restorm\Entity\EntityMetadataRegister;
use TheSaleGroup\Restorm\Entity\EntityMetadata;

/**
 * Description of EntityCache
 *
 * @author Rob Treacy <email@roberttreacy.com>
 */
class EntityStore implements EventSubscriberInterface
{
    /**
     * @var EntityMappingRegister
     */
    private $entityMappingRegister;

    /**
     * @var array
     */
    private $entityData;

    /**
     * @var array
     */
    private $entityInstances;
    
    /**
     * @var EntityMetadataRegister
     */
    private $entityMetadataRegister;

    public function __construct(EntityMappingRegister $entityMappingRegister, EntityMetadataRegister $entityMetadataRegister)
    {
        $this->entityMappingRegister = $entityMappingRegister;
        $this->entityMetadataRegister = $entityMetadataRegister;
    }

    public static function getSubscribedEvents(): array
    {
        return array(
            PreBuildEvent::NAME => [
                ['cacheEntityData', -10],
                ['findExistingEntity', -10],
            ],
            PostBuildEvent::NAME => [
                ['cacheEntity', 0],
            ],
        );
    }

    public function cacheEntityData(PreBuildEvent $event)
    {
        $entityIdentifierName = $this->getEntityIdentifierName($event->getEntityClass());

        $identifier = $event->getData()->$entityIdentifierName;
        $this->entityData[$event->getEntityClass()][$identifier] = $event->getData();
    }

    public function findExistingEntity(PreBuildEvent $event)
    {
        $entityIdentifierName = $this->getEntityIdentifierName($event->getEntityClass());

        $identifier = $event->getData()->$entityIdentifierName;

        if (isset($this->entityInstances[$event->getEntityClass()][$identifier])) {
            $event->setEntity($this->entityInstances[$event->getEntityClass()][$identifier]);
        }
    }

    public function cacheEntity(PostBuildEvent $event)
    {
        $entityClass = get_class($event->getEntity());
        
        $entityMetadata = $this->entityMetadataRegister->getEntityMetadata($event->getEntity());

        $identifier = $entityMetadata->getIdentifierValue();

        if (isset($this->entityInstances[$entityClass][$identifier]) && $this->entityInstances[$entityClass][$identifier] !== $event->getEntity()) {
            throw new \LogicException('this should not happen');
        }

        $this->entityInstances[$entityClass][$identifier] = $event->getEntity();
    }

    public function getEntityData($entity)
    {
        return $this->entityData[get_class($entity)][$this->getEntityIdentifier($entity)]
                ?? null;
    }

    private function getEntityIdentifier($entity)
    {
        return $this->entityMetadataRegister->getEntityMetadata($entity)->getIdentifierValue();
    }

    private function getEntityIdentifierName(string $entityClass): string
    {
        $entityMapping = $this->entityMappingRegister->getEntityMapping($entityClass);

        return $entityMapping->getIdentifierName();
    }
}
