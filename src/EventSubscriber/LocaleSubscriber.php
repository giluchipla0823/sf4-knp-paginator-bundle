<?php

namespace App\EventSubscriber;

use App\Helpers\AppHelper;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleSubscriber implements EventSubscriberInterface
{
    private $defaultLocale;

    public function __construct(string $defaultLocale = AppHelper::LANG_EN)
    {
        $this->defaultLocale = $defaultLocale;
    }

    public function onKernelRequest(RequestEvent $event){
        $request = $event->getRequest();

        if(!$request->hasPreviousSession()){
            return;
        }

        $locale = $request->query->get('lang', $this->defaultLocale);

        if(!in_array($locale, [AppHelper::LANG_ES, AppHelper::LANG_EN])){
            $locale = $this->defaultLocale;
        }

        $request->getSession()->set('lang', $locale);

        $request->setLocale($locale);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }
}