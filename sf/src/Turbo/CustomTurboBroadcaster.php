<?php

namespace App\Turbo;

use App\Entity\Opportunity;
use App\Entity\Order;
use App\Service\OpportunityService;
use App\Service\OrderService;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\UX\Turbo\Broadcaster\BroadcasterInterface;
use Twig\Environment;

class CustomTurboBroadcaster implements BroadcasterInterface
{
    public const TOPIC_PATTERN = 'https://symfony.com/ux-turbo/%s/%s';
    public const TRANSPORT_NAME = 'custom';

    private HubInterface $hub;
    private OpportunityService $opportunityService;
    private Environment $twig;
    private FlashBagInterface $flash;
    private OrderService $orderService;

    public function __construct(HubInterface $hub, OpportunityService $opportunityService, OrderService $orderService, Environment $twig, FlashBagInterface $flash)
    {
        $this->hub = $hub;
        $this->opportunityService = $opportunityService;
        $this->twig = $twig;
        $this->flash = $flash;
        $this->orderService = $orderService;
    }

    public function broadcast(object $entity, string $action, array $options): void
    {
        if (isset($options['transports']) && !\in_array(self::TRANSPORT_NAME, (array) $options['transports'], true)) {
            return;
        }

        $entityClass = \get_class($entity);

        if (!isset($options['rendered_action'])) {
            throw new \InvalidArgumentException(sprintf('Cannot broadcast entity of class "%s" as option "rendered_action" is missing.', $entityClass));
        }

        if (!isset($options['topic']) && !isset($options['id'])) {
            throw new \InvalidArgumentException(sprintf('Cannot broadcast entity of class "%s": either option "topics" or "id" is missing, or the PropertyAccess component is not installed. Try running "composer require property-access".', $entityClass));
        }

        $options['topics'] = (array) ($options['topics'] ?? sprintf(self::TOPIC_PATTERN, rawurlencode($entityClass), rawurlencode(implode('-', (array) $options['id']))));

        if ($entity instanceof Opportunity) {
            $pagination = $this->opportunityService->paginateOpportunities(1, 20);
            $pagination->setUsedRoute('opportunity_index');
            $template = $this->twig->load('broadcast/Opportunity.stream.html.twig');
            $options['rendered_action'] = $template->renderBlock($action, ['pagination' => $pagination]);
        }

        if ($entity instanceof Order) {
            $pagination = $this->orderService->paginateOrders(1, 20);
            $pagination->setUsedRoute('order_index');
            $template = $this->twig->load('broadcast/Order.stream.html.twig');
            $options['rendered_action'] = $template->renderBlock($action, ['pagination' => $pagination]);
        }

        $update = new Update(
            $options['topics'],
            $options['rendered_action'],
            $options['private'] ?? false,
            $options['sse_id'] ?? null,
            $options['sse_type'] ?? null,
            $options['sse_retry'] ?? null
        );

        try {
            $this->hub->publish($update);
        }
        catch(\Throwable $exception) {
            $this->flash->set('danger', 'Erreur Turbo Broadcast: ' . $exception->getPrevious()->getMessage());
        }

    }
}
