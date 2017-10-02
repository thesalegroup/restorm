<?php

namespace TheSaleGroup\Restorm\Normalizer\Transformer;

class BooleanTransformer implements TransformerInterface
{
    public function normalize($value)
    {
        return (boolean) $value;
    }

    public function denormalize($value)
    {
        return (boolean) $value;
    }
}
