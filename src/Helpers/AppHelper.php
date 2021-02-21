<?php


namespace App\Helpers;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class AppHelper
{
    public const LANG_ES = 'es';
    public const LANG_EN = 'en';

    /**
     * Gets the application's dependency container.
     *
     * @return ContainerInterface|null
     */
    public static function getContainerInterface(): ?ContainerInterface {
        global $kernel;

        return $kernel->getContainer();
    }

    /**
     * Gets the translation container.
     *
     * @return TranslatorInterface
     */
    public static function getTranslatorInterface(): TranslatorInterface {
        return self::getContainerInterface()->get('translator');
    }

    /**
     * Gets the request stack
     *
     * @return RequestStack
     */
    public static function getRequestStack(): RequestStack{
        return self::getContainerInterface()->get('request_stack');
    }

    /**
     * Gets base path of the application.
     *
     * @return string
     */
    public static function getBaseUrl(): string {
        $request = self::getRequestStack()->getCurrentRequest();

        return $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
    }

    /**
     * Gets complete route using a route name.
     *
     * @param string $routeName
     * @return string
     */
    public static function getFullPathByRouteName(string $routeName): string {
        $baseUrl = self::getBaseUrl();
        $router = self::getContainerInterface()->get('router');
        $routesCollection = $router->getRouteCollection();

        return $baseUrl . $routesCollection->get($routeName)->getPath();
    }
}