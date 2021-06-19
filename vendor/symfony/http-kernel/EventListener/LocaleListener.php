<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ConfigTransformer202106194\Symfony\Component\HttpKernel\EventListener;

use ConfigTransformer202106194\Symfony\Component\EventDispatcher\EventSubscriberInterface;
use ConfigTransformer202106194\Symfony\Component\HttpFoundation\Request;
use ConfigTransformer202106194\Symfony\Component\HttpFoundation\RequestStack;
use ConfigTransformer202106194\Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use ConfigTransformer202106194\Symfony\Component\HttpKernel\Event\KernelEvent;
use ConfigTransformer202106194\Symfony\Component\HttpKernel\Event\RequestEvent;
use ConfigTransformer202106194\Symfony\Component\HttpKernel\KernelEvents;
use ConfigTransformer202106194\Symfony\Component\Routing\RequestContextAwareInterface;
/**
 * Initializes the locale based on the current request.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @final
 */
class LocaleListener implements \ConfigTransformer202106194\Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    private $router;
    private $defaultLocale;
    private $requestStack;
    public function __construct(\ConfigTransformer202106194\Symfony\Component\HttpFoundation\RequestStack $requestStack, string $defaultLocale = 'en', \ConfigTransformer202106194\Symfony\Component\Routing\RequestContextAwareInterface $router = null)
    {
        $this->defaultLocale = $defaultLocale;
        $this->requestStack = $requestStack;
        $this->router = $router;
    }
    public function setDefaultLocale(\ConfigTransformer202106194\Symfony\Component\HttpKernel\Event\KernelEvent $event)
    {
        $event->getRequest()->setDefaultLocale($this->defaultLocale);
    }
    public function onKernelRequest(\ConfigTransformer202106194\Symfony\Component\HttpKernel\Event\RequestEvent $event)
    {
        $request = $event->getRequest();
        $this->setLocale($request);
        $this->setRouterContext($request);
    }
    public function onKernelFinishRequest(\ConfigTransformer202106194\Symfony\Component\HttpKernel\Event\FinishRequestEvent $event)
    {
        if (null !== ($parentRequest = $this->requestStack->getParentRequest())) {
            $this->setRouterContext($parentRequest);
        }
    }
    private function setLocale(\ConfigTransformer202106194\Symfony\Component\HttpFoundation\Request $request)
    {
        if ($locale = $request->attributes->get('_locale')) {
            $request->setLocale($locale);
        }
    }
    private function setRouterContext(\ConfigTransformer202106194\Symfony\Component\HttpFoundation\Request $request)
    {
        if (null !== $this->router) {
            $this->router->getContext()->setParameter('_locale', $request->getLocale());
        }
    }
    public static function getSubscribedEvents() : array
    {
        return [\ConfigTransformer202106194\Symfony\Component\HttpKernel\KernelEvents::REQUEST => [
            ['setDefaultLocale', 100],
            // must be registered after the Router to have access to the _locale
            ['onKernelRequest', 16],
        ], \ConfigTransformer202106194\Symfony\Component\HttpKernel\KernelEvents::FINISH_REQUEST => [['onKernelFinishRequest', 0]]];
    }
}
