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

use CyberSpectrum\I18N\Dictionary\DictionaryInformation;
use CyberSpectrum\I18N\Dictionary\DictionaryInterface;
use CyberSpectrum\I18N\Dictionary\DictionaryProviderInterface;
use CyberSpectrum\I18N\Dictionary\WritableDictionaryInterface;
use CyberSpectrum\I18N\Dictionary\WritableDictionaryProviderInterface;
use CyberSpectrum\I18N\Exception\DictionaryNotFoundException;
use MetaModels\IFactory;
use MetaModels\IMetaModel;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * This is the dictionary provider for MetaModels.
 */
class MetaModelDictionaryProvider implements DictionaryProviderInterface, WritableDictionaryProviderInterface
{
    use LoggerAwareTrait;

    /**
     * The MetaModel factory.
     *
     * @var IFactory
     */
    private $factory;

    /**
     * The handler factory.
     *
     * @var MetaModelHandlerFactory
     */
    private $handlerFactory;

    /**
     * Create a new instance.
     *
     * @param IFactory                $factory        The MetaModels factory.
     * @param MetaModelHandlerFactory $handlerFactory The handler factory.
     */
    public function __construct(IFactory $factory, MetaModelHandlerFactory $handlerFactory)
    {
        $this->factory        = $factory;
        $this->handlerFactory = $handlerFactory;
        $this->setLogger(new NullLogger());
    }

    /**
     * {@inheritDoc}
     *
     * @return \Traversable|DictionaryInformation[]
     *
     * @throws \RuntimeException When the MetaModel instance could not be obtained.
     */
    public function getAvailableDictionaries(): \Traversable
    {
        foreach ($this->factory->collectNames() as $name) {
            $metaModel = $this->factory->getMetaModel($name);
            if (null === $metaModel) {
                throw new \RuntimeException('Unable to get instance of MetaModel ' . $name);
            }
            $languages = $metaModel->getAvailableLanguages();
            foreach ($languages as $sourceLanguage) {
                foreach ($languages as $targetLanguage) {
                    if ($sourceLanguage === $targetLanguage) {
                        continue;
                    }
                    yield new DictionaryInformation($name, $sourceLanguage, $targetLanguage);
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     *
     * @throws DictionaryNotFoundException When the MetaModel does not exist.
     */
    public function getDictionary(
        string $name,
        string $sourceLanguage,
        string $targetLanguage,
        array $customData = []
    ): DictionaryInterface {
        if (null === $metaModel = $this->factory->getMetaModel($name)) {
            throw new DictionaryNotFoundException($name, $sourceLanguage, $targetLanguage);
        }
        $this->logger->debug('MetaModels: opening dictionary ' . $name);

        $dictionary = new MetaModelDictionary(
            $sourceLanguage,
            $targetLanguage,
            $metaModel,
            $this->getHandlersFor($metaModel)
        );
        $dictionary->setLogger($this->logger);

        return $dictionary;
    }

    /**
     * {@inheritDoc}
     *
     * @return \Traversable|DictionaryInformation[]
     */
    public function getAvailableWritableDictionaries(): \Traversable
    {
        foreach ($this->getAvailableDictionaries() as $item) {
            yield new DictionaryInformation($item->getName(), $item->getSourceLanguage(), $item->getTargetLanguage());
        }
    }

    /**
     * {@inheritDoc}
     *
     * @throws DictionaryNotFoundException When the MetaModel does not exist.
     */
    public function getDictionaryForWrite(
        string $name,
        string $sourceLanguage,
        string $targetLanguage,
        array $customData = []
    ): WritableDictionaryInterface {
        if (null === $metaModel = $this->factory->getMetaModel($name)) {
            throw new DictionaryNotFoundException($name, $sourceLanguage, $targetLanguage);
        }
        $this->logger->debug('MetaModels: opening writable dictionary ' . $name);

        $dictionary = new MetaModelDictionary(
            $sourceLanguage,
            $targetLanguage,
            $metaModel,
            $this->getHandlersFor($metaModel)
        );
        $dictionary->setLogger($this->logger);

        return $dictionary;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \RuntimeException Creating dictionaries is not supported.
     */
    public function createDictionary(
        string $name,
        string $sourceLanguage,
        string $targetLanguage,
        array $customData = []
    ): WritableDictionaryInterface {
        throw new \RuntimeException('Creating MetaModels is not supported.');
    }

    /**
     * Generate the handlers for the passed MetaModel.
     *
     * @param IMetaModel $metaModel The metamodel instance.
     *
     * @return MetaModelAttributeHandlerInterface[]
     */
    private function getHandlersFor(IMetaModel $metaModel): array
    {
        return \iterator_to_array($this->handlerFactory->generate($metaModel));
    }
}
