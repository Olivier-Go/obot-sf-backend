<?php

namespace App\Twig;

use App\Entity\Parameter;
use App\Repository\ParameterRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private RequestStack $requestStack;
    private AuthorizationCheckerInterface $authorizationChecker;
    private HubInterface $hub;
    private FlashBagInterface $flash;
    private ParameterRepository $parameterRepository;

    public function __construct(RequestStack $requestStack, AuthorizationCheckerInterface $authorizationChecker, HubInterface $hub, FlashBagInterface $flash, ParameterRepository $parameterRepository)
    {
        $this->requestStack = $requestStack;
        $this->authorizationChecker = $authorizationChecker;
        $this->hub = $hub;
        $this->flash = $flash;
        $this->parameterRepository = $parameterRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('ws_stream_key', [$this, 'getWsStreamKey']),
            new TwigFunction('mercure_hub_run', [$this, 'isMercureHubRun']),
            new TwigFunction('worker_send_order', [$this, 'getWorkerSendOrder']),
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

    public function isMercureHubRun(): bool
    {
        try {
            $this->hub->publish(new Update('notification'));
            return true;
        } catch(\Throwable $exception) {
            $this->flash->set('danger', 'Erreur Turbo Broadcast: ' . $exception->getPrevious()->getMessage());
        }
        return false;
    }

    public function getWorkerSendOrder(): bool
    {
        $parameter = $this->parameterRepository->findFirst();
        if ($parameter instanceof Parameter) {
            return $parameter->getWorkerSendOrder();
        }

        $this->flash->set('danger', 'Paramètres non définis');
        return false;
    }
}