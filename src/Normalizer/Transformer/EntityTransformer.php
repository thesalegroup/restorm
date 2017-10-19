<?php
/*
 * The MIT License
 *
 * Copyright 2017 Rob Treacy <robert.treacy@thesalegroup.co.uk>.
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

namespace TheSaleGroup\Restorm\Normalizer\Transformer;

use TheSaleGroup\Restorm\EntityManager;
use TheSaleGroup\Restorm\Event\PreBuildEvent;
use TheSaleGroup\Restorm\Entity\Agent;
use ProxyManager\Proxy\GhostObjectInterface;

/**
 * Description of EntityTransformer
 *
 * @author Rob Treacy <robert.treacy@thesalegroup.co.uk>
 */
class EntityTransformer implements AdvancedTransformerInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function denormalize($value, array $options)
    {
        $entityClass = $options['entity'];
        $entityMapping = $this->entityManager->getEntityMappingRegister()->getEntityMapping($entityClass);
        $identifierName = $entityMapping->getIdentifierName();
        $data = (object) [$identifierName => $value];

        $entity = $this->entityManager->getEntityBuilder()->buildEntity($entityClass, $data, true);
        
        return $entity;
    }

    public function normalize($value, array $options)
    {
        if ($value instanceof Agent) {
            return $value->getIdentifierValue();
        } else {
            $entityMetadata = $this->entityManager->getEntityMetadataRegister()->getEntityMetadata($value);
            return $entityMetadata->getIdentifierValue();
        }
    }

    public function setEntityManager(EntityManager $entityManager): void
    {
        $this->entityManager = $entityManager;
    }
}
