<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\MetaModels;

use MetaModels\Attribute\ITranslated;

/**
 * @api
 */
class MetaModelTextHandlerFactory implements MetaModelAttributeHandlerFactoryInterface
{
    #[\Override]
    public function create(ITranslated $attribute): MetaModelAttributeHandlerInterface
    {
        return new MetaModelTextHandler($attribute);
    }
}
