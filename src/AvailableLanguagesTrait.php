<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\MetaModels;

use MetaModels\IMetaModel;
use MetaModels\ITranslatedMetaModel;

/**
 * This is a helper trait to provide the available languages via new ways and then legacy way.
 *
 * @internal
 */
trait AvailableLanguagesTrait
{
    /** @return list<string> */
    private function getAvailableLanguagesFrom(IMetaModel $metaModel): ?array
    {
        if ($metaModel instanceof ITranslatedMetaModel) {
            return $metaModel->getLanguages();
        }

        /** @psalm-suppress DeprecatedMethod */
        return $metaModel->getAvailableLanguages();
    }
}
