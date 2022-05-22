<?php

namespace App\Form;

use App\Entity\Market;
use App\Entity\Ticker;
use App\Repository\MarketRepository;
use App\Repository\TickerRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TradingType extends AbstractType
{


    private MarketRepository $marketRepository;

    public function __construct(MarketRepository $marketRepository)
    {
        $this->marketRepository = $marketRepository;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $buyMarket = $options['data']['buyMarket'] ?? $this->marketRepository->findFirst();
        $sellMarket = $options['data']['sellMarket'] ?? $this->marketRepository->findFirst();

        $builder
            ->add('buyMarket', EntityType::class, [
                'label' => 'Exchange',
                'class' => Market::class,
                'attr' => ['data-action' => 'trading#change'],
                'row_attr' => ['class' => 'input-group mb-3'],
                'label_attr' => ['class' => 'input-group-text bg-primary border-primary text-white'],
                'choice_label' => 'upperName'
            ])
            ->add('sellMarket', EntityType::class, [
                'label' => 'Exchange',
                'class' => Market::class,
                'attr' => ['data-action' => 'trading#change'],
                'row_attr' => ['class' => 'input-group mb-3'],
                'label_attr' => ['class' => 'input-group-text bg-primary border-primary text-white'],
                'choice_label' => 'upperName'
            ])
            ->add('buyTicker', EntityType::class, [
                'label' => 'Ticker',
                'class' => Ticker::class,
                'attr' => ['data-action' => 'trading#change'],
                'row_attr' => ['class' => 'input-group'],
                'label_attr' => ['class' => 'input-group-text bg-primary border-primary text-white'],
                'query_builder' => fn (TickerRepository $tr) => $tr->findAllByMarketQB($buyMarket)
            ])
            ->add('sellTicker', EntityType::class, [
                'label' => 'Ticker',
                'class' => Ticker::class,
                'attr' => ['data-action' => 'trading#change'],
                'row_attr' => ['class' => 'input-group'],
                'label_attr' => ['class' => 'input-group-text bg-primary border-primary text-white'],
                'query_builder' => fn (TickerRepository $tr) => $tr->findAllByMarketQB($sellMarket)
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data' => [],
        ]);
    }
}
