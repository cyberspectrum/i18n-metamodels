<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\MetaModels;

use Traversable;
use MetaModels\Attribute\ITranslated;
use MetaModels\IMetaModel;
use Psr\Container\ContainerInterface;

use function get_class;

/**
 * This generates the handlers.
 */
class MetaModelHandlerFactory
{
    /** The handler factories. */
    private ContainerInterface $handlerFactories;

    /**
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
     * @return Traversable<int, MetaModelAttributeHandlerInterface>
     */
    public function generate(IMetaModel $metaModel): Traversable
    {
        foreach ($metaModel->getAttributes() as $attribute) {
            if (!$attribute instanceof ITranslated) {
                continue;
            }

            if ($this->handlerFactories->has($class = get_class($attribute))) {
                /** @var MetaModelAttributeHandlerFactoryInterface $factory */
                $factory = $this->handlerFactories->get($class);

                yield $factory->create($attribute);
            }
        }
    }
}
