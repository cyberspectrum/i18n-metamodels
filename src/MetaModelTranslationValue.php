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

use CyberSpectrum\I18N\TranslationValue\WritableTranslationValueInterface;

/**
 * This handles reading and writing translations.
 */
class MetaModelTranslationValue implements WritableTranslationValueInterface
{
    /**
     * The key.
     *
     * @var string
     */
    private $key;

    /**
     * The item id.
     *
     * @var int
     */
    private $itemId;

    /**
     * The attribute handler.
     *
     * @var MetaModelAttributeHandlerInterface
     */
    private $handler;

    /**
     * The source language.
     *
     * @var string
     */
    private $sourceLanguage;

    /**
     * The destination language.
     *
     * @var string
     */
    private $targetLanguage;

    /**
     * Create a new instance.
     *
     * @param string                             $key            The key of the entry.
     * @param int                                $itemId         The id of the entry.
     * @param MetaModelAttributeHandlerInterface $handler        The handler.
     * @param string                             $sourceLanguage The source language.
     * @param string                             $targetLanguage The destination language.
     */
    public function __construct($key, $itemId, $handler, string $sourceLanguage, string $targetLanguage)
    {
        $this->key            = $key;
        $this->itemId         = $itemId;
        $this->handler        = $handler;
        $this->sourceLanguage = $sourceLanguage;
        $this->targetLanguage = $targetLanguage;
    }

    /**
     * {@inheritDoc}
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * {@inheritDoc}
     */
    public function getSource(): ?string
    {
        return $this->handler->getValueInLanguage($this->itemId, $this->sourceLanguage);
    }

    /**
     * {@inheritDoc}
     */
    public function getTarget(): ?string
    {
        return $this->handler->getValueInLanguage($this->itemId, $this->targetLanguage);
    }

    /**
     * Check if the source value is empty.
     *
     * @return bool
     */
    public function isSourceEmpty(): bool
    {
        return empty($this->getSource());
    }

    /**
     * Check if the target value is empty.
     *
     * @return bool
     */
    public function isTargetEmpty(): bool
    {
        return empty($this->getTarget());
    }

    /**
     * {@inheritDoc}
     */
    public function setSource(string $value)
    {
        $this->handler->setValueInLanguage($this->itemId, $this->sourceLanguage, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function setTarget(string $value)
    {
        $this->handler->setValueInLanguage($this->itemId, $this->targetLanguage, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function clearSource()
    {
        $this->handler->setValueInLanguage($this->itemId, $this->sourceLanguage, null);
    }

    /**
     * {@inheritDoc}
     */
    public function clearTarget()
    {
        $this->handler->setValueInLanguage($this->itemId, $this->targetLanguage, null);
    }
}
