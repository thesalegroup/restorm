<?php

namespace TheSaleGroup\Restorm\Normalizer\Transformer;

class BooleanTransformer implements TransformerInterface
{
    public function normalize($value, array $options)
    {
        return (boolean) $value;
    }

    public function denormalize($value, array $options)
    {
        return (boolean) $value;
    }
}
