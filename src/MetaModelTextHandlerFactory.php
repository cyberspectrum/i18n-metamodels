<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\MetaModels;

use MetaModels\Attribute\ITranslated;

/**
 * This interface describes an attribute handler factory.
 */
class MetaModelTextHandlerFactory implements MetaModelAttributeHandlerFactoryInterface
{
    #[\Override]
    public function create(ITranslated $attribute): MetaModelAttributeHandlerInterface
    {
        return new MetaModelTextHandler($attribute);
    }
}
