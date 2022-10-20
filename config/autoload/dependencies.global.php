<?php
use Mezzio\Application;
use Mezzio\Container\ApplicationFactory;
use Mezzio\Helper;

return [

    // Provides application-wide services.
    // We recommend using fully-qualified class names whenever possible as
    // service names.
    'dependencies' => [
        // Use 'invokables' for constructor-less services, or services that do
        // not require arguments to the constructor. Map a service name to the
        // class name.
        'invokables' => [
            // Fully\Qualified\InterfaceName::class => Fully\Qualified\ClassName::class,
            Helper\ServerUrlHelper::class => Helper\ServerUrlHelper::class,
        ],
        // Use 'factories' for services provided by callbacks/factory classes.
        'factories' => [
            Application::class => ApplicationFactory::class,
            Helper\UrlHelper::class => Helper\UrlHelperFactory::class,
            //new
            Helper\ServerUrlMiddleware::class => Helper\ServerUrlMiddlewareFactory::class,

            //new-adapter
            Laminas\Db\Adapter\Adapter::class => Laminas\Db\Adapter\AdapterServiceFactory::class,
            Laminas\Session\Container::class => VuBib\Factory\ZendSessionFactory::class,
        ],
    ],
];
