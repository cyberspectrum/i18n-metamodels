<?php

declare(strict_types=1);

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
use RuntimeException;
use Traversable;

use function iterator_to_array;

/**
 * This is the dictionary provider for MetaModels.
 */
class MetaModelDictionaryProvider implements DictionaryProviderInterface, WritableDictionaryProviderInterface
{
    use LoggerAwareTrait;

    /** The MetaModel factory. */
    private IFactory $factory;

    /** The handler factory. */
    private MetaModelHandlerFactory $handlerFactory;

    /**
     * @param IFactory                $factory        The MetaModels factory.
     * @param MetaModelHandlerFactory $handlerFactory The handler factory.
     */
    public function __construct(IFactory $factory, MetaModelHandlerFactory $handlerFactory)
    {
        $this->factory        = $factory;
        $this->handlerFactory = $handlerFactory;
    }

    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException When the MetaModel instance could not be obtained.
     */
    public function getAvailableDictionaries(): Traversable
    {
        foreach ($this->factory->collectNames() as $name) {
            $metaModel = $this->factory->getMetaModel($name);
            if (null === $metaModel) {
                throw new RuntimeException('Unable to get instance of MetaModel ' . $name);
            }
            if (null === ($languages = $metaModel->getAvailableLanguages())) {
                continue;
            }
            /** @var list<string> $languages */
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
        if ($this->logger) {
            $this->logger->debug('MetaModels: opening dictionary ' . $name);
        }

        $dictionary = new MetaModelDictionary(
            $sourceLanguage,
            $targetLanguage,
            $metaModel,
            $this->getHandlersFor($metaModel)
        );
        if ($this->logger) {
            $dictionary->setLogger($this->logger);
        }

        return $dictionary;
    }

    public function getAvailableWritableDictionaries(): Traversable
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
        if ($this->logger) {
            $this->logger->debug('MetaModels: opening writable dictionary ' . $name);
        }

        $dictionary = new MetaModelDictionary(
            $sourceLanguage,
            $targetLanguage,
            $metaModel,
            $this->getHandlersFor($metaModel)
        );
        if ($this->logger) {
            $dictionary->setLogger($this->logger);
        }

        return $dictionary;
    }

    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException Creating dictionaries is not supported.
     */
    public function createDictionary(
        string $name,
        string $sourceLanguage,
        string $targetLanguage,
        array $customData = []
    ): WritableDictionaryInterface {
        throw new RuntimeException('Creating MetaModels is not supported.');
    }

    /**
     * Generate the handlers for the passed MetaModel.
     *
     * @param IMetaModel $metaModel The metamodel instance.
     *
     * @return Traversable<int, MetaModelAttributeHandlerInterface>
     */
    private function getHandlersFor(IMetaModel $metaModel): Traversable
    {
        return $this->handlerFactory->generate($metaModel);
    }
}
