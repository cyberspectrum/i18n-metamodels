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
 * This interface describes an attribute handler factory.
 */
interface MetaModelAttributeHandlerFactoryInterface
{
    /**
     * Create a handler for the passed attribute.
     *
     * @param ITranslated $attribute The attribute.
     *
     * @return MetaModelAttributeHandlerInterface
     */
    public function create(ITranslated $attribute): MetaModelAttributeHandlerInterface;
}
