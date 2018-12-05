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

use CyberSpectrum\I18N\Dictionary\WritableDictionaryInterface;
use CyberSpectrum\I18N\Exception\NotSupportedException;
use CyberSpectrum\I18N\Exception\TranslationNotFoundException;
use CyberSpectrum\I18N\TranslationValue\TranslationValueInterface;
use CyberSpectrum\I18N\TranslationValue\WritableTranslationValueInterface;
use MetaModels\IMetaModel;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * This provides an interface to translate MetaModel contents.
 */
class MetaModelDictionary implements WritableDictionaryInterface
{
    use LoggerAwareTrait;

    /**
     * The MetaModel instance.
     *
     * @var IMetaModel
     */
    private $metaModel;

    /**
     * The source language.
     *
     * @var string
     */
    private $sourceLanguage;

    /**
     * The target language.
     *
     * @var string
     */
    private $targetLanguage;

    /**
     * The handlers.
     *
     * @var MetaModelAttributeHandlerInterface[]
     */
    private $handlers;

    /**
     * The id list.
     *
     * @var int[]
     */
    private $ids;

    /**
     * Create a new instance.
     *
     * @param string                               $sourceLanguage The source language.
     * @param string                               $targetLanguage The target language.
     * @param IMetaModel                           $metaModel      The translation buffer.
     * @param MetaModelAttributeHandlerInterface[] $handlers       The attribute handlers.
     *
     * @throws NotSupportedException When the MetaModel does not support either language.
     */
    public function __construct(
        string $sourceLanguage,
        string $targetLanguage,
        IMetaModel $metaModel,
        array $handlers
    ) {
        $languages = $metaModel->getAvailableLanguages();
        if (!\in_array($sourceLanguage, $languages, true)) {
            throw new NotSupportedException(
                $this,
                'MetaModel "' . $metaModel->getTableName() . '" does not support language "' . $sourceLanguage . '""'
            );
        }
        if (!\in_array($targetLanguage, $languages, true)) {
            throw new NotSupportedException(
                $this,
                'MetaModel "' . $metaModel->getTableName() . '" does not support language "' . $targetLanguage . '""'
            );
        }

        $this->sourceLanguage = $sourceLanguage;
        $this->targetLanguage = $targetLanguage;
        $this->metaModel      = $metaModel;

        $this->handlers = $handlers;

        $this->setLogger(new NullLogger());
    }

    /**
     * {@inheritDoc}
     */
    public function keys(): \Traversable
    {
        return $this->getAttributeKeys();
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key): TranslationValueInterface
    {
        return $this->getWritable($key);
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $key): bool
    {
        foreach ($this->getAttributeKeys() as $candidate) {
            if ($key === $candidate) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getSourceLanguage(): string
    {
        return $this->sourceLanguage;
    }

    /**
     * {@inheritDoc}
     */
    public function getTargetLanguage(): string
    {
        return $this->targetLanguage;
    }

    /**
     * {@inheritDoc}
     *
     * @throws NotSupportedException This class does not support adding.
     */
    public function add(string $key): WritableTranslationValueInterface
    {
        throw new NotSupportedException($this, 'Adding to MetaModels is not supported at the moment.');
    }

    /**
     * {@inheritDoc}
     *
     * @throws NotSupportedException This class does not support removing.
     */
    public function remove(string $key): void
    {
        throw new NotSupportedException($this, 'Removing from MetaModels is not supported at the moment.');
    }

    /**
     * {@inheritDoc}
     *
     * @throws TranslationNotFoundException When the translation is not contained.
     */
    public function getWritable($key): WritableTranslationValueInterface
    {
        $explode = explode('.', $key);
        $itemId  = (int) array_shift($explode);
        $prefix  = implode('.', $explode);
        foreach ($this->handlers as $handler) {
            if ($handler->getPrefix() === $prefix) {
                return new MetaModelTranslationValue(
                    $key,
                    $itemId,
                    $handler,
                    $this->sourceLanguage,
                    $this->targetLanguage
                );
            }
        }

        throw new TranslationNotFoundException($key, $this);
    }

    /**
     * Obtain all attribute keys.
     *
     * @return \Generator
     */
    private function getAttributeKeys(): \Generator
    {
        foreach ($this->handlers as $attributeHandler) {
            /** @var MetaModelAttributeHandlerInterface $attributeHandler */
            $prefix = $attributeHandler->getPrefix();
            foreach ($this->idListGenerator() as $id) {
                yield $id . '.' . $prefix;
            }
        }
    }

    /**
     * Obtain all ids in the MetaModel.
     *
     * @return \Generator
     */
    private function idListGenerator(): \Generator
    {
        if (null === $this->ids) {
            $this->ids = $this->metaModel->getIdsFromFilter(null);
        }

        yield from $this->ids;
    }
}
