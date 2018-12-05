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
use MetaModels\IMetaModel;
use Psr\Container\ContainerInterface;

/**
 * This generates the handlers.
 */
class MetaModelHandlerFactory
{
    /**
     * The handler factories.
     *
     * @var ContainerInterface
     */
    private $handlerFactories;

    /**
     * Create a new instance.
     *
     * @param ContainerInterface $handlerFactories The handler factories as lookup list.
     */
    public function __construct(ContainerInterface $handlerFactories)
    {
        $this->handlerFactories = $handlerFactories;
    }

    /**
     * Generate the attribute handlers.
     *
     * @param IMetaModel $metaModel The MetaModel.
     *
     * @return \Generator
     */
    public function generate(IMetaModel $metaModel): \Generator
    {
        foreach ($metaModel->getAttributes() as $attribute) {
            if (!$attribute instanceof ITranslated) {
                continue;
            }

            if ($this->handlerFactories->has($class = \get_class($attribute))) {
                yield $attribute->getColName() => $this->handlerFactories->get($class)->create($attribute);
            }
        }
    }
}
