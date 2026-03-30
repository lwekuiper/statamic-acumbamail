<?php

namespace Lwekuiper\StatamicAcumbamail\Tests;

use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Facades\Addon;
use Statamic\Facades\Config;
use Statamic\Testing\AddonTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Lwekuiper\StatamicAcumbamail\ServiceProvider;

abstract class TestCase extends AddonTestCase
{
    use WithFaker;

    protected string $addonServiceProvider = ServiceProvider::class;

    protected function setSites($sites)
    {
        Site::setSites($sites);

        Config::set('statamic.system.multisite', Site::hasMultiple());
    }

    protected function setProEdition()
    {
        Config::set('statamic.editions.addons.lwekuiper/statamic-acumbamail', 'pro');
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        Addon::get('lwekuiper/statamic-acumbamail')->editions(['lite', 'pro']);

        $app['config']->set('statamic.acumbamail.store_directory', __DIR__.'/__fixtures__/resources/acumbamail');
        $app['config']->set('statamic.forms.forms', __DIR__.'/__fixtures__/content/forms');
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        // Assume the pro edition within tests
        $app['config']->set('statamic.editions.pro', true);
    }

    protected function assertEveryItemIsInstanceOf($class, $items)
    {
        if ($items instanceof \Illuminate\Support\Collection) {
            $items = $items->all();
        }

        $matches = 0;

        foreach ($items as $item) {
            if ($item instanceof $class) {
                $matches++;
            }
        }

        $this->assertEquals(count($items), $matches, 'Failed asserting that every item is an instance of '.$class);
    }
}
