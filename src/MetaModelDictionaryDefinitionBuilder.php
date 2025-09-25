<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\MetaModels;

use CyberSpectrum\I18N\Configuration\Configuration;
use CyberSpectrum\I18N\Configuration\Definition\Definition;
use CyberSpectrum\I18N\Configuration\Definition\DictionaryDefinition;
use CyberSpectrum\I18N\Configuration\DefinitionBuilder\DefinitionBuilderInterface;
use InvalidArgumentException;

/**
 * Builds MetaModel dictionary definitions.
 *
 * @psalm-type TMetaModelDictionaryDefinitionConfigurationArray=array{
 *   name: string
 * }
 */
class MetaModelDictionaryDefinitionBuilder implements DefinitionBuilderInterface
{
    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function build(Configuration $configuration, array $data): Definition
    {
        $this->checkConfiguration($data);
        $name = $data['name'];
        unset($data['name']);
        $data['type'] = 'metamodels';

        return new DictionaryDefinition($name, $data);
    }

    /** @psalm-assert TMetaModelDictionaryDefinitionConfigurationArray $data */
    private function checkConfiguration(array $data): void
    {
        if (!array_key_exists('name', $data)) {
            throw new InvalidArgumentException('Missing key \'name\'');
        }
        if (!is_string($data['name'])) {
            throw new InvalidArgumentException('\'name\' must be a string');
        }
    }
}
