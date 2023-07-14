<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Bridge\Laravel;

use EonX\EasyRandom\Bridge\Laravel\EasyRandomServiceProvider;
use EonX\EasyRandom\Tests\AbstractTestCase;
use Laravel\Lumen\Application;

abstract class AbstractLumenTestCase extends AbstractTestCase
{
    private ?Application $app = null;

    protected function getApp(): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $this->app = new Application(__DIR__);
        $this->app->register(EasyRandomServiceProvider::class);

        return $this->app;
    }
}
