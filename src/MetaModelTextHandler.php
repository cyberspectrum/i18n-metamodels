<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\MetaModels;

use MetaModels\Attribute\ITranslated;

/** This handles translated text attributes. */
class MetaModelTextHandler implements MetaModelAttributeHandlerInterface
{
    /** The translated attribute. */
    private ITranslated $attribute;

    /** The prefix. */
    private string $prefix;

    public function __construct(ITranslated $attribute)
    {
        $this->attribute = $attribute;
        $this->prefix    = $this->attribute->getColName();
    }

    #[\Override]
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    #[\Override]
    public function getValueInLanguage(string $itemId, string $language): ?string
    {
        /** @var null|array<string, array{value: string}> $value */
        $value = $this->attribute->getTranslatedDataFor([$itemId], $language);

        return $value ? $value[$itemId]['value'] : '';
    }

    #[\Override]
    public function setValueInLanguage(string $itemId, string $language, ?string $value): void
    {
        $this->attribute->setTranslatedDataFor(
            [$itemId => $this->attribute->widgetToValue($value, $itemId)],
            $language
        );
    }
}
