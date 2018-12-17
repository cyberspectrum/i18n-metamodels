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

use MetaModels\Attribute\ITranslated;

/**
 * This handles translated text attributes.
 */
class MetaModelTextHandler implements MetaModelAttributeHandlerInterface
{
    /**
     * The translated attribute.
     *
     * @var ITranslated
     */
    private $attribute;

    /**
     * The prefix.
     *
     * @var string
     */
    private $prefix;

    /**
     * Create a new instance.
     *
     * @param ITranslated $attribute
     */
    public function __construct(ITranslated $attribute)
    {
        $this->attribute = $attribute;
        $this->prefix    = $this->attribute->getColName();
    }

    /**
     * {@inheritDoc}
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * {@inheritDoc}
     */
    public function getValueInLanguage(int $itemId, string $language): ?string
    {
        $value = $this->attribute->getTranslatedDataFor([$itemId], $language);

        return $value ? $value[$itemId]['value'] : '';
    }

    /**
     * {@inheritDoc}
     */
    public function setValueInLanguage(int $itemId, string $language, string $value = null): void
    {
        $this->attribute->setTranslatedDataFor(
            [$itemId => $this->attribute->widgetToValue($value, $itemId)],
            $language
        );
    }
}
