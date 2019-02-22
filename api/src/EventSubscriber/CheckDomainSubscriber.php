<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class CheckDomainSubscriber implements EventSubscriberInterface
{
    public function onKernelRequest(FilterControllerEvent $event)
    {
        $request = $event->getRequest();
        if ($request->getHost() != parse_url($request->headers->get('referer'), PHP_URL_HOST)) {
//            throw new BadRequestHttpException('Request incorrect-' . $request->getHost() . '-' . parse_url($request->headers->get('referer'), PHP_URL_HOST));
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => 'onKernelRequest',
        );
    }
}