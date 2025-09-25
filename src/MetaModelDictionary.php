<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\MetaModels;

use CyberSpectrum\I18N\Dictionary\WritableDictionaryInterface;
use CyberSpectrum\I18N\Exception\NotSupportedException;
use CyberSpectrum\I18N\Exception\TranslationNotFoundException;
use CyberSpectrum\I18N\TranslationValue\TranslationValueInterface;
use CyberSpectrum\I18N\TranslationValue\WritableTranslationValueInterface;
use IteratorIterator;
use MetaModels\IMetaModel;
use Psr\Log\LoggerAwareTrait;
use Traversable;

use function array_shift;
use function explode;
use function implode;
use function in_array;

/**
 * This provides an interface to translate MetaModel contents.
 *
 * @psalm-type TTraversableHandlers=Traversable<MetaModelAttributeHandlerInterface>
 *
 * @psalm-suppress InvalidTemplateParam - Somehow psalm cokes on the annotation for property $iterator.
 */
final class MetaModelDictionary implements WritableDictionaryInterface
{
    use LoggerAwareTrait;

    /** The MetaModel instance. */
    private IMetaModel $metaModel;

    /** The source language. */
    private string $sourceLanguage;

    /** The target language. */
    private string $targetLanguage;

    /**
     * The handlers.
     *
     * @var TTraversableHandlers
     */
    private Traversable $handlers;

    /** @var IteratorIterator<mixed, MetaModelAttributeHandlerInterface, TTraversableHandlers>|null */
    private ?IteratorIterator $iterator;

    /** @var array<int, MetaModelAttributeHandlerInterface> */
    private array $iteratorBuffer;

    /**
     * The id list.
     *
     * @var null|list<string>
     */
    private ?array $ids;

    /**
     * @param string                                          $sourceLanguage The source language.
     * @param string                                          $targetLanguage The target language.
     * @param IMetaModel                                      $metaModel      The translation buffer.
     * @param Traversable<MetaModelAttributeHandlerInterface> $handlers       The attribute handlers.
     *
     * @throws NotSupportedException When the MetaModel does not support either language.
     */
    public function __construct(
        string $sourceLanguage,
        string $targetLanguage,
        IMetaModel $metaModel,
        Traversable $handlers
    ) {
        $languages = $metaModel->getAvailableLanguages() ?? [];
        if (!in_array($sourceLanguage, $languages, true)) {
            throw new NotSupportedException(
                $this,
                'MetaModel "' . $metaModel->getTableName() . '" does not support language "' . $sourceLanguage . '""'
            );
        }
        if (!in_array($targetLanguage, $languages, true)) {
            throw new NotSupportedException(
                $this,
                'MetaModel "' . $metaModel->getTableName() . '" does not support language "' . $targetLanguage . '""'
            );
        }

        $this->sourceLanguage = $sourceLanguage;
        $this->targetLanguage = $targetLanguage;
        $this->metaModel      = $metaModel;

        $this->handlers = $handlers;
        $this->iterator = null;
        $this->iteratorBuffer = [];
        $this->ids = null;
    }

    #[\Override]
    public function keys(): Traversable
    {
        return $this->getAttributeKeys();
    }

    #[\Override]
    public function get(string $key): TranslationValueInterface
    {
        return $this->getWritable($key);
    }

    #[\Override]
    public function has(string $key): bool
    {
        foreach ($this->getAttributeKeys() as $candidate) {
            if ($key === $candidate) {
                return true;
            }
        }

        return false;
    }

    #[\Override]
    public function getSourceLanguage(): string
    {
        return $this->sourceLanguage;
    }

    #[\Override]
    public function getTargetLanguage(): string
    {
        return $this->targetLanguage;
    }

    /**
     * {@inheritDoc}
     *
     * @throws NotSupportedException This class does not support adding.
     */
    #[\Override]
    public function add(string $key): WritableTranslationValueInterface
    {
        throw new NotSupportedException($this, 'Adding to MetaModels is not supported at the moment.');
    }

    /**
     * {@inheritDoc}
     *
     * @throws NotSupportedException This class does not support removing.
     */
    #[\Override]
    public function remove(string $key): void
    {
        throw new NotSupportedException($this, 'Removing from MetaModels is not supported at the moment.');
    }

    /**
     * {@inheritDoc}
     *
     * @throws TranslationNotFoundException When the translation is not contained.
     */
    #[\Override]
    public function getWritable(string $key): WritableTranslationValueInterface
    {
        $explode = explode('.', $key);
        $itemId  = array_shift($explode);
        $prefix  = implode('.', $explode);
        foreach ($this->getHandlerIterator() as $handler) {
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
     * @return Traversable<int, string>
     */
    private function getAttributeKeys(): Traversable
    {
        foreach ($this->getHandlerIterator() as $attributeHandler) {
            $prefix = $attributeHandler->getPrefix();
            foreach ($this->idListGenerator() as $id) {
                yield $id . '.' . $prefix;
            }
        }
    }

    /**
     * Obtain all ids in the MetaModel.
     *
     * @return Traversable<int, string>
     */
    private function idListGenerator(): Traversable
    {
        if (null === $this->ids) {
            /** @var list<string> $ids */
            $ids = $this->metaModel->getIdsFromFilter(null);
            $this->ids = $ids;
        }

        yield from $this->ids;
    }

    /**
     * Create a generator reading all lines from the file.
     *
     * @return Traversable<MetaModelAttributeHandlerInterface>
     */
    public function getHandlerIterator(): Traversable
    {
        if (null === $this->iterator) {
            $this->iterator = new IteratorIterator($this->handlers);
            // See https://www.php.net/manual/en/class.iteratoriterator.php#120999
            $this->iterator->rewind();
        }

        yield from call_user_func(function (): Traversable {
            $index = 0;
            while (true) {
                // We can yield from buffer.
                if (count($this->iteratorBuffer) > $index) {
                    yield $this->iteratorBuffer[$index];
                    $index++;
                    continue;
                }
                // Fetch next handler if there is one.
                assert($this->iterator instanceof IteratorIterator);
                if (!$this->iterator->valid()) {
                    break;
                }
                $handler = $this->iterator->current();
                $this->iterator->next();
                yield $this->iteratorBuffer[$index++] = $handler;
            }
        });
    }
}
