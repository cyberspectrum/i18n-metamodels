<?php

/**
 * This file is part of cyberspectrum/i18n-metamodels.
 *
 * (c) 2018 CyberSpectrum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    cyberspectrum/i18n-metamodels
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2018 CyberSpectrum.
 * @license    https://github.com/cyberspectrum/i18n-metamodels/blob/master/LICENSE MIT
 * @filesource
 */

declare(strict_types = 1);

namespace CyberSpectrum\I18N\MetaModels;

/**
 * This provides an interface to translate MetaModel contents.
 */
interface MetaModelAttributeHandlerInterface
{
    /**
     * Get the prefix of the handler.
     *
     * @return string
     */
    public function getPrefix(): string;

    /**
     * Fetch a value in the passed language from the item with the given id.
     *
     * @param int    $itemId   The id of the item to query.
     * @param string $language The language to retrieve.
     *
     * @return string|null
     */
    public function getValueInLanguage(int $itemId, string $language): ?string;

    /**
     * Set a value in the passed language for the given id.
     *
     * @param int         $itemId   The id of the item to update.
     * @param string      $language The language to update.
     * @param string|null $value    The value to set.
     *
     * @return void
     */
    public function setValueInLanguage(int $itemId, string $language, string $value = null): void;
}
