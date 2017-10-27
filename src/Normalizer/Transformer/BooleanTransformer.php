<?php

namespace TheSaleGroup\Restorm\Normalizer\Transformer;

class BooleanTransformer implements TransformerInterface
{

    public function normalize($value, array $options)
    {
        if ($value === null) {
            return null;
        }

        return (boolean) $value;
    }

    public function denormalize($value, array $options)
    {
        if ($value === null) {
            return null;
        }

        return (boolean) $value;
    }
}
