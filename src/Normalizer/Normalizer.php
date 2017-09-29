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

namespace TheSaleGroup\Restorm\Normalizer;

use TheSaleGroup\Restorm\Normalizer\Transformer\TransformerInterface;
use TheSaleGroup\Restorm\Entity\EntityMetadata;

/**
 * Description of Normalizer
 *
 * @author Rob Treacy <robert.treacy@thesalegroup.co.uk>
 */
class Normalizer
{
    /**
     * @var TransformerInterface[]
     */
    private $transformers;

    public function __construct(array $transformers)
    {
        $this->transformers = $transformers;
    }

    public function normalize(EntityMetadata $entityMetadata): \stdClass
    {
        $normalizedEntity = new \stdClass;

        foreach ($entityMetadata->getEntityMapping()->getProperties() as $propertyName => $propertyOptions) {

            $propertyType = $propertyOptions['type'];
            $propertyValue = $entityMetadata->getPropertyValue($propertyName);

            $transformer = $this->getTransformer($propertyType);

            $normalizedValue = $transformer->normalize($propertyValue);

            $normalizedEntity->$propertyName = $normalizedValue;
        }

        return $normalizedEntity;
    }

    public function denormalize(\stdClass $data, EntityMetadata $entityMetadata)
    {
        foreach ($entityMetadata->getEntityMapping()->getProperties() as $propertyName => $propertyOptions) {

            if (!property_exists($data, $propertyName)) {
                throw new Exception\MissingPropertyException(sprintf('Property "%s" was not available in the response.', $propertyName));
            }

            $propertyType = $propertyOptions['type'];
            $dataValue = $data->$propertyName;

            $transformer = $this->getTransformer($propertyType);

            $denormalizedValue = $transformer->denormalize($dataValue);

            $entityMetadata->setPropertyValue($propertyName, $denormalizedValue);
        }

        return $entityMetadata->getEntity();
    }

    private function getTransformer($type): TransformerInterface
    {
        if (!array_key_exists($type, $this->transformers)) {
            throw new Exception\UnknownEntityException(sprintf('No transformer for type "%s" exists.', $propertyType));
        }

        return $this->transformers[$type];
    }
}
