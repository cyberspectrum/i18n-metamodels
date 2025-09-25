<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\MetaModels;

/**
 * This provides an interface to translate MetaModel contents.
 *
 * @api
 */
interface MetaModelAttributeHandlerInterface
{
    /** Get the prefix of the handler. */
    public function getPrefix(): string;

    /**
     * Fetch a value in the passed language from the item with the given id.
     *
     * @param string $itemId   The id of the item to query.
     * @param string $language The language to retrieve.
     */
    public function getValueInLanguage(string $itemId, string $language): ?string;

    /**
     * Set a value in the passed language for the given id.
     *
     * @param string      $itemId   The id of the item to update.
     * @param string      $language The language to update.
     * @param string|null $value    The value to set.
     */
    public function setValueInLanguage(string $itemId, string $language, ?string $value): void;
}
