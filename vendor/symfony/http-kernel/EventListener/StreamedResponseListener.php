<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ConfigTransformer202108165\Symfony\Component\HttpKernel\EventListener;

use ConfigTransformer202108165\Symfony\Component\EventDispatcher\EventSubscriberInterface;
use ConfigTransformer202108165\Symfony\Component\HttpFoundation\StreamedResponse;
use ConfigTransformer202108165\Symfony\Component\HttpKernel\Event\ResponseEvent;
use ConfigTransformer202108165\Symfony\Component\HttpKernel\KernelEvents;
/**
 * StreamedResponseListener is responsible for sending the Response
 * to the client.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @final
 */
class StreamedResponseListener implements \ConfigTransformer202108165\Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    /**
     * Filters the Response.
     * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
     */
    public function onKernelResponse($event)
    {
        if (!$event->isMainRequest()) {
            return;
        }
        $response = $event->getResponse();
        if ($response instanceof \ConfigTransformer202108165\Symfony\Component\HttpFoundation\StreamedResponse) {
            $response->send();
        }
    }
    public static function getSubscribedEvents() : array
    {
        return [\ConfigTransformer202108165\Symfony\Component\HttpKernel\KernelEvents::RESPONSE => ['onKernelResponse', -1024]];
    }
}
