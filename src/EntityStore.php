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

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Robwasripped\Restorm\Mapping\EntityMappingRegister;
use Robwasripped\Restorm\Event\PreBuildEvent;
use Robwasripped\Restorm\Event\PostBuildEvent;

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
    private $entityData;
    private $entityInstances;

    public function __construct(EntityMappingRegister $entityMappingRegister)
    {
        $this->entityMappingRegister = $entityMappingRegister;
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
        $entityIdentifierName = $this->getEntityIdentifierName($entityClass);

        $entityReflection = new \ReflectionClass($event->getEntity());
        $property = $entityReflection->getProperty($entityIdentifierName);
        $property->setAccessible(true);
        $identifier = $property->getValue($event->getEntity());
        $property->setAccessible(false);

        if (isset($this->entityInstances[$entityClass][$identifier]) && $this->entityInstances[$entityClass][$identifier] !== $event->getEntity()) {
            throw new \LogicException('this should not happen');
        }

        $this->entityInstances[$entityClass][$identifier] = $event->getEntity();
    }

    private function getEntityIdentifierName(string $entityClass): string
    {
        $entityMapping = $this->entityMappingRegister->getEntityMapping($entityClass);

        return $entityMapping->getIdentifierName();
    }
}
