<?php

namespace TheSaleGroup\Restorm\Normalizer\Transformer;

class BooleanTransformer extends ScalarTransformer
{

    protected function getExplicitType(): ?string
    {
        return 'boolean';
    }
}
