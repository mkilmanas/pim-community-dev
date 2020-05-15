<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\UIBundle\Install;

/**
 * @copyright 2020 Akeneo SAS (https://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface AssetsInstaller
{
    public function installAssets(bool $shouldSymLink): void;
}
