<?php

namespace App\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private RequestStack $requestStack;
    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(RequestStack $requestStack, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->requestStack = $requestStack;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('ws_stream_key', [$this, 'getWsStreamKey']),
        ];
    }

    public function getWsStreamKey()
    {
        $cookies = $this->requestStack->getCurrentRequest()->cookies;
        if ($this->authorizationChecker->isGranted('ROLE_USER') && $cookies->has('PHPSESSID')) {
            return $cookies->get('PHPSESSID');
        }
        return null;
    }

}