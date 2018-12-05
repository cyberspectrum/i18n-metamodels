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

namespace CyberSpectrum\I18N\MetaModels\Test;

use CyberSpectrum\I18N\Dictionary\DictionaryInformation;
use CyberSpectrum\I18N\Exception\DictionaryNotFoundException;
use CyberSpectrum\I18N\MetaModels\MetaModelDictionary;
use CyberSpectrum\I18N\MetaModels\MetaModelDictionaryProvider;
use CyberSpectrum\I18N\MetaModels\MetaModelHandlerFactory;
use MetaModels\IFactory;
use MetaModels\IMetaModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * This tests the xliff provider.
 *
 * @covers \CyberSpectrum\I18N\MetaModels\MetaModelDictionaryProvider
 */
class MetaModelDictionaryProviderTest extends TestCase
{
    /**
     * Test.
     *
     * @return void
     */
    public function testGetAvailableDictionaries(): void
    {
        $provider = new MetaModelDictionaryProvider(
            $this->mockFactory(['mm_test' => ['en', 'de']]),
            $this->mockHandler()
        );

        $result = \iterator_to_array($provider->getAvailableDictionaries());
        $this->assertCount(2, $result);
        /** @var DictionaryInformation[] $result */

        $this->assertInstanceOf(DictionaryInformation::class, $result[0]);
        $this->assertSame('mm_test', $result[0]->getName());
        $this->assertSame('en', $result[0]->getSourceLanguage());
        $this->assertSame('de', $result[0]->getTargetLanguage());
        $this->assertInstanceOf(DictionaryInformation::class, $result[1]);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testGetDictionary(): void
    {
        $provider = new MetaModelDictionaryProvider(
            $this->mockFactory(['mm_test' => ['en', 'de']]),
            $this->mockHandler()
        );

        $this->assertInstanceOf(MetaModelDictionary::class, $provider->getDictionary('mm_test', 'en', 'de'));
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testThrowsForUnknownDictionary(): void
    {
        $provider = new MetaModelDictionaryProvider($this->mockFactory([]), $this->mockHandler());

        $this->expectException(DictionaryNotFoundException::class);
        $this->expectExceptionMessage(
            'Dictionary unknown not found (requested source language: "en", requested target language: "de").'
        );

        $provider->getDictionary('unknown', 'en', 'de');
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testGetWritable(): void
    {
        $provider = new MetaModelDictionaryProvider(
            $this->mockFactory(['mm_test' => ['en', 'de']]),
            $this->mockHandler()
        );

        $this->assertInstanceOf(MetaModelDictionary::class, $provider->getDictionaryForWrite('mm_test', 'en', 'de'));
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testThrowsForUnknownDictionaryForWrite(): void
    {
        $provider = new MetaModelDictionaryProvider($this->mockFactory([]), $this->mockHandler());

        $this->expectException(DictionaryNotFoundException::class);
        $this->expectExceptionMessage(
            'Dictionary unknown not found (requested source language: "en", requested target language: "de").'
        );

        $provider->getDictionaryForWrite('unknown', 'en', 'de');
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testCreateDictionaryIsNotSupported(): void
    {
        $provider = new MetaModelDictionaryProvider($this->mockFactory([]), $this->mockHandler());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Creating MetaModels is not supported.');

        $provider->createDictionary('unsupported', 'en', 'de');
    }

    /**
     * Mock a MetaModel factory.
     *
     * @param array $metaModels The metamodel list.
     *
     * @return IFactory
     */
    private function mockFactory(array $metaModels): IFactory
    {
        $mock = $this->getMockForAbstractClass(IFactory::class);
        $mock->method('collectNames')->willReturn(array_keys($metaModels));
        foreach ($metaModels as $name => $languages) {
            $mock
                ->method('getMetaModel')
                ->with($name)
                ->willReturn($metaModel = $this->getMockForAbstractClass(IMetaModel::class));
            $metaModel->method('getAvailableLanguages')->willReturn($languages);
        }

        return $mock;
    }

    /**
     * Mock a handler factory.
     *
     * @return MetaModelHandlerFactory|MockObject
     */
    private function mockHandler(): MetaModelHandlerFactory
    {
        $mock = $this->getMockBuilder(MetaModelHandlerFactory::class)->disableOriginalConstructor()->getMock();

        return $mock;
    }
}
