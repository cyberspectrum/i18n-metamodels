<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\MetaModels;

use CyberSpectrum\I18N\TranslationValue\WritableTranslationValueInterface;

/**
 * This handles reading and writing translations.
 */
class MetaModelTranslationValue implements WritableTranslationValueInterface
{
    /** The key. */
    private string $key;

    /** The item id. */
    private string $itemId;

    /** The attribute handler. */
    private MetaModelAttributeHandlerInterface $handler;

    /** The source language. */
    private string $sourceLanguage;

    /** The destination language. */
    private string $targetLanguage;

    /**
     * Create a new instance.
     *
     * @param string                             $key            The key of the entry.
     * @param string                             $itemId         The id of the entry.
     * @param MetaModelAttributeHandlerInterface $handler        The handler.
     * @param string                             $sourceLanguage The source language.
     * @param string                             $targetLanguage The destination language.
     */
    public function __construct(
        string $key,
        string $itemId,
        MetaModelAttributeHandlerInterface $handler,
        string $sourceLanguage,
        string $targetLanguage
    ) {
        $this->key            = $key;
        $this->itemId         = $itemId;
        $this->handler        = $handler;
        $this->sourceLanguage = $sourceLanguage;
        $this->targetLanguage = $targetLanguage;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getSource(): ?string
    {
        return $this->handler->getValueInLanguage($this->itemId, $this->sourceLanguage);
    }

    public function getTarget(): ?string
    {
        return $this->handler->getValueInLanguage($this->itemId, $this->targetLanguage);
    }

    public function isSourceEmpty(): bool
    {
        return empty($this->getSource());
    }

    public function isTargetEmpty(): bool
    {
        return empty($this->getTarget());
    }

    public function setSource(string $value): void
    {
        $this->handler->setValueInLanguage($this->itemId, $this->sourceLanguage, $value);
    }

    public function setTarget(string $value): void
    {
        $this->handler->setValueInLanguage($this->itemId, $this->targetLanguage, $value);
    }

    public function clearSource(): void
    {
        $this->handler->setValueInLanguage($this->itemId, $this->sourceLanguage, null);
    }

    public function clearTarget(): void
    {
        $this->handler->setValueInLanguage($this->itemId, $this->targetLanguage, null);
    }
}
