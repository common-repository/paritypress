<?php

namespace ParityPress\Framework;

use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemServiceProvider;
use ParityPress\Framework\Support\ServiceProvider;

class Application extends Container
{
    /**
     * Indicates if the class aliases have been registered.
     *
     * @var bool
     */
    protected static $aliasesRegistered = false;

    /**
     * The base path of the application installation.
     *
     * @var string
     */
    protected $basePath;

    /**
     * All of the loaded configuration files.
     *
     * @var array
     */
    protected $loadedConfigurations = [];

    /**
     * Indicates if the application has "booted".
     *
     * @var bool
     */
    protected $booted = false;

    /**
     * The loaded service providers.
     *
     * @var array
     */
    protected $loadedProviders = [];

    /**
     * The service binding methods that have been executed.
     *
     * @var array
     */
    protected $ranServiceBinders = [];

    /**
     * The custom storage path defined by the developer.
     *
     * @var string
     */
    protected $storagePath;

    /**
     * The application namespace.
     *
     * @var string
     */
    protected $namespace;

    /**
     * The Router instance.
     *
     * @var \ParityPress\Framework\Routing\Router
     */
    public $router;

    /**
     * Create a new Lumen application instance.
     *
     * @param  string|null  $basePath
     * @return void
     */
    public function __construct($basePath = null)
    {
        $this->basePath = $basePath;

        $this->bootstrapContainer();
    }

    /**
     * Bootstrap the application container.
     *
     * @return void
     */
    protected function bootstrapContainer()
    {
        static::setInstance($this);

        $this->instance('app', $this);
        $this->instance(self::class, $this);

        $this->registerContainerAliases();
    }

    /**
     * Determine if the given service provider is loaded.
     *
     * @param  string  $provider
     * @return bool
     */
    public function providerIsLoaded(string $provider)
    {
        return isset($this->loadedProviders[$provider]);
    }

    /**
     * Register a service provider with the application.
     *
     * @param  \ParityPress\Framework\Support\ServiceProvider|string  $provider
     * @return void
     */
    public function register($provider)
    {
        if (!$provider instanceof ServiceProvider) {
            $provider = new $provider($this);
        }

        if (array_key_exists($providerName = get_class($provider), $this->loadedProviders)) {
            return;
        }

        $this->loadedProviders[$providerName] = $provider;

        if (method_exists($provider, 'register')) {
            $provider->register();
        }

        if ($this->booted) {
            $this->bootProvider($provider);
        }
    }

    /**
     * Register a deferred provider and service.
     *
     * @param  string  $provider
     * @return void
     */
    public function registerDeferredProvider($provider)
    {
        $this->register($provider);
    }

    /**
     * Boots the registered providers.
     */
    public function boot()
    {
        if ($this->booted) {
            return;
        }

        foreach ($this->loadedProviders as $provider) {
            $this->bootProvider($provider);
        }

        $this->booted = true;
    }

    /**
     * Boot the given service provider.
     *
     * @param  \ParityPress\Framework\Support\ServiceProvider  $provider
     * @return mixed
     */
    protected function bootProvider(ServiceProvider $provider)
    {
        if (method_exists($provider, 'boot')) {
            return $this->call([$provider, 'boot']);
        }
    }

    /**
     * Resolve the given type from the container.
     *
     * @param  string  $abstract
     * @param  array  $parameters
     * @return mixed
     */
    public function make($abstract, array $parameters = [])
    {
        $abstract = $this->getAlias($abstract);

        if (
            !$this->bound($abstract) &&
            array_key_exists($abstract, $this->availableBindings) &&
            !array_key_exists($this->availableBindings[$abstract], $this->ranServiceBinders)
        ) {
            $this->{$method = $this->availableBindings[$abstract]}();

            $this->ranServiceBinders[$method] = true;
        }

        return parent::make($abstract, $parameters);
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerConfigBindings()
    {
        $this->singleton('config', function () {
            return new ConfigRepository;
        });
    }       

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerFilesBindings()
    {
        $this->singleton('files', function () {
            return new Filesystem;
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerFilesystemBindings()
    {
        $this->singleton('filesystem', function () {
            return $this->loadComponent('filesystems', FilesystemServiceProvider::class, 'filesystem');
        });
        $this->singleton('filesystem.disk', function () {
            return $this->loadComponent('filesystems', FilesystemServiceProvider::class, 'filesystem.disk');
        });
        $this->singleton('filesystem.cloud', function () {
            return $this->loadComponent('filesystems', FilesystemServiceProvider::class, 'filesystem.cloud');
        });
    }

    /**
     * Configure and load the given component and provider.
     *
     * @param  string  $config
     * @param  array|string  $providers
     * @param  string|null  $return
     * @return mixed
     */
    public function loadComponent($config, $providers, $return = null)
    {
        $this->configure($config);

        foreach ((array) $providers as $provider) {
            $this->register($provider);
        }

        return $this->make($return ?: $config);
    }

    /**
     * Load a configuration file into the application.
     *
     * @param  string  $name
     * @return void
     */
    public function configure($name)
    {
        if (isset($this->loadedConfigurations[$name])) {
            return;
        }

        $this->loadedConfigurations[$name] = true;

        $path = $this->getConfigurationPath($name);

        if ($path) {
            $this->make('config')->set($name, require $path);
        }
    }

    /**
     * Get the path to the given configuration file.
     *
     * If no name is provided, then we'll return the path to the config folder.
     *
     * @param  string|null  $name
     * @return string
     */
    public function getConfigurationPath($name = null)
    {
        if (!$name) {
            $appConfigDir = $this->basePath('config') . '/';

            if (file_exists($appConfigDir)) {
                return $appConfigDir;
            } elseif (file_exists($path = __DIR__ . '/../config/')) {
                return $path;
            }
        } else {
            $appConfigPath = $this->basePath('config') . '/' . $name . '.php';

            if (file_exists($appConfigPath)) {
                return $appConfigPath;
            } elseif (file_exists($path = __DIR__ . '/../config/' . $name . '.php')) {
                return $path;
            }
        }
    }

    /**
     * Get the path to the application "app" directory.
     *
     * @return string
     */
    public function path()
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'app';
    }

    /**
     * Get the base path for the application.
     *
     * @param  string|null  $path
     * @return string
     */
    public function basePath($path = null)
    {
        if (isset($this->basePath)) {
            return $this->basePath . ($path ? '/' . $path : $path);
        }

        if ($this->runningInConsole()) {
            $this->basePath = getcwd();
        } else {
            $this->basePath = realpath(getcwd() . '/../');
        }

        return $this->basePath($path);
    }

    /**
     * Get the path to the application configuration files.
     *
     * @param  string  $path
     * @return string
     */
    public function configPath($path = '')
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'config' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Flush the container of all bindings and resolved instances.
     *
     * @return void
     */
    public function flush()
    {
        parent::flush();

        $this->loadedProviders = [];
        $this->reboundCallbacks = [];
        $this->resolvingCallbacks = [];
        $this->availableBindings = [];
        $this->ranServiceBinders = [];
        $this->loadedConfigurations = [];
        $this->afterResolvingCallbacks = [];

        static::$instance = null;
    }

    /**
     * Determine if application locale is the given locale.
     *
     * @param  string  $locale
     * @return bool
     */
    public function isLocale($locale)
    {
        return $this->getLocale() == $locale;
    }

    /**
     * Register the core container aliases.
     *
     * @return void
     */
    protected function registerContainerAliases()
    {
        $this->aliases = [
            \Illuminate\Contracts\Foundation\Application::class => 'app',
            \Illuminate\Contracts\Config\Repository::class => 'config',
            \Illuminate\Config\Repository::class => 'config',
            \Illuminate\Container\Container::class => 'app',
            \Illuminate\Contracts\Container\Container::class => 'app',
        ];
    }

    /**
     * The available container bindings and their respective load methods.
     *
     * @var array
     */
    public $availableBindings = [
        'config' => 'registerConfigBindings',
    ];
}
