<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\MetaModels;

use MetaModels\Attribute\ITranslated;

/**
 * This interface describes an attribute handler factory.
 *
 * @SuppressWarnings(PHPMD.LongClassName)
 */
interface MetaModelAttributeHandlerFactoryInterface
{
    /**
     * Create a handler for the passed attribute.
     *
     * @param ITranslated $attribute The attribute.
     *
     * @return MetaModelAttributeHandlerInterface
     */
    public function create(ITranslated $attribute): MetaModelAttributeHandlerInterface;
}
