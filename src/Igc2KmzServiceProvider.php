<?php

namespace M165437\Igc2KmzPhp;

use Illuminate\Support\ServiceProvider;

/**
 * @codeCoverageIgnore
 */
class Igc2KmzServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfiguration();

        $this->registerIgc2Kmz();
    }

    /**
     * Register configuration
     *
     * @return void
     */
    public function registerConfiguration()
    {
        $configPath = __DIR__ . '/../config/igc2kmz.php';
        $this->mergeConfigFrom($configPath, 'igc2kmz');
    }

    /**
     * Register Igc2Kmz.
     *
     * @return void
     */
    private function registerIgc2Kmz()
    {
        $this->app->bind(Igc2Kmz::class, function ($app) {
            $binary = $app['config']->get('igc2kmz.binary', []);
            return new Igc2Kmz($binary);
        });
    }
}