<?php declare(strict_types=1);

namespace Pipes\PhpSdk;

use Exception;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * Class Kernel
 *
 * @package Pipes\PhpSdk
 * @codeCoverageIgnore
 */
final class Kernel extends BaseKernel
{

    use MicroKernelTrait;

    public const CONFIG_EXT = '.{yaml}';

    /**
     * @return BundleInterface[]
     */
    public function registerBundles(): iterable
    {
        $contents = require sprintf('%s/config/Bundles.php', $this->getProjectDir());
        foreach ($contents as $class => $envs) {
            if ($envs[$this->environment] ?? $envs['all'] ?? FALSE) {
                yield new $class();
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param LoaderInterface  $loader
     *
     * @throws Exception
     */
    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $container->addResource(new FileResource(sprintf('%s/config/Bundles.php', $this->getProjectDir())));
        $container->setParameter('container.dumper.inline_class_loader', TRUE);
        $confDir = $this->getConfigDir();
        $loader->load(sprintf('%s/*%s', $confDir, self::CONFIG_EXT), 'glob');
        $loader->load(sprintf('%s/{application}/*%s', $confDir, self::CONFIG_EXT), 'glob');
        $loader->load(sprintf('%s/{connector}/*%s', $confDir, self::CONFIG_EXT), 'glob');
        $loader->load(sprintf('%s/{custom_node}/*%s', $confDir, self::CONFIG_EXT), 'glob');
        $loader->load(sprintf('%s/{packages}/*%s', $confDir, self::CONFIG_EXT), 'glob');
        $loader->load(sprintf('%s/{packages}/%s/*%s', $confDir, $this->environment, self::CONFIG_EXT), 'glob');
    }

    /**
     * @param RoutingConfigurator $routes
     */
    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $confDir = $this->getConfigDir();
        $routes->import(sprintf('%s/{routes}/*%s', $confDir, self::CONFIG_EXT), 'glob');
        $routes->import(sprintf('%s/{routes}/%s/*%s', $confDir, $this->environment, self::CONFIG_EXT), 'glob');
    }

    /**
     * @return string
     */
    private function getConfigDir(): string
    {
        return sprintf('%s/config', $this->getProjectDir());
    }

}
