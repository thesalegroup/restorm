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
use TheSaleGroup\Restorm\Normalizer\Exception\InvalidValueException;
use TheSaleGroup\Restorm\Mapping\EntityMapping;
use TheSaleGroup\Restorm\EntityCollection;

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
        if ($value === null) {
            return null;
        }

        $entityClass = $options['entity'];
        $entityMapping = $this->entityManager->getEntityMappingRegister()->getEntityMapping($entityClass);

        if ($options['multiple'] ?? false) {
            if (!is_array($value)) {
                throw new InvalidValueException('The value passed to the entity transformer must be an array if option "multiple" is true.');
            }

            $entities = array();

            foreach ($value as $entityIdentifierValue) {
                $entity = $this->buildEntity($entityMapping, $entityIdentifierValue);
                $entities[] = $entity;
            }

            return new EntityCollection($entities);
        } else {

            $entity = $this->buildEntity($entityMapping, $value, true);
            return $entity;
        }
    }

    public function normalize($value, array $options)
    {
        if ($value === null) {
            return null;
        }

        if ($options['multiple'] ?? false) {
            if (!$value instanceof EntityCollection) {
                throw new InvalidValueException('The value passed to the entity transformer must be an EntityCollection instance if option "multiple" is true.');
            }
            $entityValues = array();
            foreach ($value as $entity) {
                $entityValues[] = $this->getEntityIdentifierValue($entity);
            }

            return $entityValues;
        } else {
            return $this->getEntityIdentifierValue($value);
        }
    }

    public function setEntityManager(EntityManager $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    private function buildEntity(EntityMapping $entityMapping, $identifierValue)
    {
        $entityClass = $entityMapping->getEntityClass();
        $identifierName = $entityMapping->getIdentifierMappedFromName();

        $data = (object) [$identifierName => $identifierValue];

        return $this->entityManager->getEntityBuilder()->buildEntity($entityClass, $data, true);
    }

    private function getEntityIdentifierValue($entity)
    {
        return $this->entityManager->getEntityMetadataRegister()->getEntityMetadata($entity)->getIdentifierValue();
    }
}
