<?php

namespace App\Service;

use App\Entity\Opportunity;
use App\Repository\MarketRepository;
use App\Repository\OpportunityRepository;
use App\Utils\Tools;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;

class OpportunityService extends Tools
{
    private DenormalizerInterface $denormalizer;
    private ValidatorInterface $validator;
    private OpportunityRepository $opportunityRepository;
    private MarketRepository $marketRepository;
    private ManagerRegistry $doctrine;
    private PaginatorInterface $paginator;
    private HubInterface $hub;
    private Environment $twig;

    public function __construct(DenormalizerInterface $denormalizer, ValidatorInterface $validator, OpportunityRepository $opportunityRepository, MarketRepository $marketRepository, ManagerRegistry $doctrine, PaginatorInterface $paginator, HubInterface $hub, Environment $twig)
    {
        $this->denormalizer = $denormalizer;
        $this->validator = $validator;
        $this->opportunityRepository = $opportunityRepository;
        $this->marketRepository = $marketRepository;
        $this->doctrine = $doctrine;
        $this->paginator = $paginator;
        $this->hub = $hub;
        $this->twig = $twig;
    }

    public function denormalizeOpportunity(string $data)
    {
        $data = json_decode($data);
        $data->received = $this->convertTimestampSec($data->received);

        $opportunity = $this->denormalizer->denormalize($data, Opportunity::class);
        $errors = $this->validator->validate($opportunity);
        if (count($errors) > 0) {
            $msgErrors = [];
            foreach ($errors as $error) {
                $msgErrors[] = [
                    'field' => $error->getPropertyPath(),
                    'message' => $error->getMessage(),
                ];
            }
            return $msgErrors;
        }

        $opportunity->setBuyMarket($this->marketRepository->findOneBy(['name' => strtolower($data->buyMarket)]));
        $opportunity->setSellMarket($this->marketRepository->findOneBy(['name' => strtolower($data->sellMarket)]));

        return $opportunity;
    }

    public function createOpportunity(Opportunity $opportunity): Opportunity
    {
        $em = $this->doctrine->getManager();
        $em->persist($opportunity);
        $em->flush();

        $this->hub->publish(new Update(
            'notification',
            $this->twig->render('broadcast/Notification.stream.html.twig', [
                'type' => 'info',
                'message' => 'Nouvelle opportunité ' . $opportunity->getTicker()
            ])
        ));

        return $opportunity;
    }

    public function paginateOpportunities(int $page, int $maxItemPerPage): PaginationInterface
    {
        $query = $this->opportunityRepository->findAllQB();
        $limit = $maxItemPerPage;
        if ($maxItemPerPage === 0) {
            $limit = count($query->getResult()) > 0 ? count($query->getResult()) : 20;
        }

        return $this->paginator->paginate(
            $query,
            $page,
            $limit
        );
    }

}