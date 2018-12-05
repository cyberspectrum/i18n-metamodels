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

use CyberSpectrum\I18N\Exception\NotSupportedException;
use CyberSpectrum\I18N\Exception\TranslationNotFoundException;
use CyberSpectrum\I18N\MetaModels\MetaModelAttributeHandlerInterface;
use CyberSpectrum\I18N\MetaModels\MetaModelDictionary;
use CyberSpectrum\I18N\MetaModels\MetaModelTranslationValue;
use MetaModels\IMetaModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * This tests the simple translation dictionary.
 *
 * @covers \CyberSpectrum\I18N\MetaModels\MetaModelDictionary
 */
class MetaModelDictionaryTest extends TestCase
{
    /**
     * Test.
     *
     * @return void
     */
    public function testInstantiation(): void
    {
        $dictionary = $this->mockDictionary([
            1 => [
                'attribute1' => [
                    'source' => 'source value 1.1',
                    'target' => 'target value 1.1',
                ],
                'attribute2' => [
                    'source' => 'source value 2.1',
                    'target' => 'target value 2.1',
                ],
            ],
            2 => [
                'attribute1' => [
                    'source' => 'source value 1.2',
                    'target' => 'target value 1.2',
                ],
                'attribute2' => [
                    'source' => 'source value 2.2',
                    'target' => 'target value 2.2',
                ],
            ],
            3 => [
                'attribute1' => [
                    'source' => 'source value 1.3',
                    'target' => 'target value 1.3',
                ],
                'attribute2' => [
                    'source' => 'source value 2.3',
                    'target' => 'target value 2.3',
                ],
            ],
        ]);

        $this->assertFalse($dictionary->has('unknown-key'));

        $this->assertSame('en', $dictionary->getSourceLanguage());
        $this->assertSame('de', $dictionary->getTargetLanguage());
        $this->assertSame([
            '1.attribute1',
            '2.attribute1',
            '3.attribute1',
            '1.attribute2',
            '2.attribute2',
            '3.attribute2',
        ], \iterator_to_array($dictionary->keys()));

        $this->assertTrue($dictionary->has('1.attribute1'));
        $this->assertTrue($dictionary->has('2.attribute1'));
        $this->assertTrue($dictionary->has('3.attribute1'));
        $this->assertTrue($dictionary->has('1.attribute2'));
        $this->assertTrue($dictionary->has('2.attribute2'));
        $this->assertTrue($dictionary->has('3.attribute2'));
        $this->assertInstanceOf(MetaModelTranslationValue::class, $value = $dictionary->get('1.attribute1'));
        $this->assertSame('1.attribute1', $value->getKey());
        $this->assertSame('source value 1.1', $value->getSource());
        $this->assertSame('target value 1.1', $value->getTarget());
        $this->assertFalse($value->isSourceEmpty());
        $this->assertFalse($value->isTargetEmpty());
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testThrowsForUnknownSourceLanguage(): void
    {
        $metaModel = $this->mockMetaModel();
        $metaModel->expects($this->once())->method('getTableName')->willReturn('mm_test');
        $metaModel->expects($this->once())->method('getAvailableLanguages')->willReturn([]);

        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage('MetaModel "mm_test" does not support language "en"');

        new MetaModelDictionary('en', 'de', $metaModel, []);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testThrowsForUnknownTargetLanguage(): void
    {
        $metaModel = $this->mockMetaModel();
        $metaModel->expects($this->once())->method('getTableName')->willReturn('mm_test');
        $metaModel->expects($this->once())->method('getAvailableLanguages')->willReturn(['en']);

        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage('MetaModel "mm_test" does not support language "de"');

        new MetaModelDictionary('en', 'de', $metaModel, []);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testThrowsForUnknownKey(): void
    {
        $dictionary = $this->mockDictionary([]);

        $this->expectException(TranslationNotFoundException::class);
        $this->expectExceptionMessage('Key "unknown-key" not found');

        $dictionary->get('unknown-key');
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testHandlingOfEmptyValuesWorks(): void
    {
        $dictionary = $this->mockDictionary([
            1 => [
                'attribute1' => [
                    'source' => null,
                    'target' => null,
                ],
            ],
        ]);

        $this->assertSame(['1.attribute1'], \iterator_to_array($dictionary->keys()));

        $this->assertInstanceOf(MetaModelTranslationValue::class, $value = $dictionary->get('1.attribute1'));
        $this->assertSame('1.attribute1', $value->getKey());
        $this->assertNull($value->getSource());
        $this->assertNull($value->getTarget());
        $this->assertTrue($value->isSourceEmpty());
        $this->assertTrue($value->isTargetEmpty());
    }
    /**
     * Test.
     *
     * @return void
     */
    public function testAddingValuesThrows(): void
    {
        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage('Adding to MetaModels is not supported at the moment.');

        $dictionary = $this->mockDictionary([]);

        $dictionary->add('test-key');
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testRemovalThrows(): void
    {
        $dictionary = $this->mockDictionary([]);

        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage('Removing from MetaModels is not supported at the moment.');

        $dictionary->remove('test-key');
    }

    /**
     * Mock a dictionary.
     *
     * @param array $values The database values.
     *
     * @return MetaModelDictionary
     */
    protected function mockDictionary(array $values): MetaModelDictionary
    {
        $ids = array_keys($values);

        $metaModel = $this->mockMetaModel();
        $metaModel->expects($this->never())->method('getTableName');
        $metaModel->method('getIdsFromFilter')->with(null)->willReturn($ids);
        $metaModel->expects($this->never())->method('getAttributes');
        $metaModel->method('getAvailableLanguages')->willReturn(['en', 'de']);

        $attValues = [];
        foreach ($values as $id => $contents) {
            foreach ($contents as $attribute => $langValues) {
                $attValues[$attribute][$id] = $langValues;
            }
        }

        $handlers = array_map(function ($values, $attribute) {
            return $this->mockMetaModelAttributeHandler($attribute, $values);
        }, $attValues, array_keys($attValues));

        return new MetaModelDictionary('en', 'de', $metaModel, $handlers);
    }

    /**
     * Obtain a mock.
     *
     * @return IMetaModel|MockObject
     */
    private function mockMetaModel(): IMetaModel
    {
        return $this->getMockForAbstractClass(IMetaModel::class);
    }

    /**
     * Obtain a mock.
     *
     * @param string $prefix The prefix for the handler.
     * @param array  $values The values to return (id => value).
     *
     * @return MetaModelAttributeHandlerInterface|MockObject
     */
    private function mockMetaModelAttributeHandler(string $prefix, array $values): MetaModelAttributeHandlerInterface
    {
        $handler = $this
            ->getMockBuilder(MetaModelAttributeHandlerInterface::class)
            ->getMockForAbstractClass();

        $handler->method('getPrefix')->willReturn($prefix);
        $handler->method('getValueInLanguage')->willReturnMap(array_merge(array_map(function ($value, $index) {
            return [$index, 'en', $value['source']];
        }, $values, array_keys($values)), array_map(function ($value, $index) {
            return [$index, 'de', $value['target']];
        }, $values, array_keys($values))));

        return $handler;
    }
}
