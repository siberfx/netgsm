<?php

namespace Siberfx\NetGsm;

use GuzzleHttp\Client;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Siberfx\NetGsm\Exceptions\InvalidConfiguration;

class NetGsmServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadTranslationsFrom(dirname(__DIR__).'/resources/lang', 'netgsm');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                dirname(__DIR__).'/config/config.php' => config_path('netgsm.php'),
            ], ['netgsm-config', 'config']);

            $this->publishes([
                dirname(__DIR__).'/resources/lang' => $this->app->langPath('vendor/netgsm'),
            ], 'netgsm-lang');
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__).'/config/config.php', 'netgsm');

        $this->app->singleton(NetGsm::class, function (Application $app): NetGsm {
            $config = $app['config']->get('netgsm');

            if (empty($config['credentials'])) {
                throw InvalidConfiguration::configurationNotSet();
            }

            $client = new Client([
                'base_uri' => rtrim($config['defaults']['base_uri'], '/').'/',
                'timeout' => $config['defaults']['timeout'],
            ]);

            return new NetGsm($client, $config['credentials'], $config['defaults']);
        });
    }
}
