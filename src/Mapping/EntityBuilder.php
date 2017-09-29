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

namespace TheSaleGroup\Restorm\Mapping;

use TheSaleGroup\Restorm\Mapping\EntityMappingRegister;
use TheSaleGroup\Restorm\Entity\EntityMetadataRegister;
use TheSaleGroup\Restorm\Entity\EntityMetadata;
use TheSaleGroup\Restorm\Normalizer\Normalizer;

/**
 * Description of EntityBuilder
 *
 * @author Rob Treacy <email@roberttreacy.com>
 */
class EntityBuilder
{
    /**
     * @var EntityMappingRegister
     */
    private $entityMappingRegister;
    
    /**
     * @var EntityMetadataRegister
     */
    private $entityMetadataRegister;
    
    /**
     * @var Normalizer
     */
    private $normalizer;

    public function __construct(EntityMappingRegister $entityMappingRegister, EntityMetadataRegister $entityMetadataRegister, Normalizer $normalizer)
    {
        $this->entityMappingRegister = $entityMappingRegister;
        $this->entityMetadataRegister = $entityMetadataRegister;
        $this->normalizer = $normalizer;
    }

    public function buildEntity(string $entityClass)
    {
        $entityMapping = $this->entityMappingRegister->getEntityMapping($entityClass);
        
        $entity = new $entityClass;
        $entityMetadata = new EntityMetadata($entity, $entityMapping);
        $this->entityMetadataRegister->addEntityMetadata($entityMetadata);
        
        return $entity;
    }
    
    public function populateEntity($entity, \stdClass $data)
    {
        $entityMetadata = $this->entityMetadataRegister->getEntityMetadata($entity);
        
        return $this->normalizer->denormalize($data, $entityMetadata);
    }
}
