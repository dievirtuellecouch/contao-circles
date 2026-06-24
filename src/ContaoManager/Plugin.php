<?php

declare(strict_types=1);

namespace DVC\ContaoCircles\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Config\ConfigPluginInterface;
use MadeYourDay\RockSolidCustomElements\RockSolidCustomElementsBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use DVC\ContaoCircles\ContaoCirclesBundle;

final class Plugin implements BundlePluginInterface, ConfigPluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        $loadAfter = [ContaoCoreBundle::class];

        if (class_exists(RockSolidCustomElementsBundle::class)) {
            $loadAfter[] = RockSolidCustomElementsBundle::class;
        }

        return [
            BundleConfig::create(ContaoCirclesBundle::class)
                ->setLoadAfter($loadAfter),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader, array $managerConfig): void
    {
        $loader->load('@ContaoCirclesBundle/Resources/config/services.yaml');
    }
}
