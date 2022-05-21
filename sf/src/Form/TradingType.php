<?php

namespace App\Form;

use App\Entity\Market;
use App\Entity\Ticker;
use App\Repository\TickerRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TradingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $buyMarket = $options['data']['buyMarket'] ?? null;
        $sellMarket = $options['data']['sellMarket'] ?? null;

        $builder
            ->add('buyMarket', EntityType::class, [
                'required' => false,
                'label' => 'Exchange',
                'class' => Market::class,
                'attr' => ['data-action' => 'trading#change'],
                'row_attr' => ['class' => 'input-group mb-3'],
                'label_attr' => ['class' => 'input-group-text bg-primary border-primary text-white'],
                'choice_label' => 'upperName'
            ])
            ->add('sellMarket', EntityType::class, [
                'required' => false,
                'label' => 'Exchange',
                'class' => Market::class,
                'attr' => ['data-action' => 'trading#change'],
                'row_attr' => ['class' => 'input-group mb-3'],
                'label_attr' => ['class' => 'input-group-text bg-primary border-primary text-white'],
                'choice_label' => 'upperName'
            ])
            ->add('buyTicker', EntityType::class, [
                'required' => false,
                'label' => 'Ticker',
                'class' => Ticker::class,
                'attr' => ['data-action' => 'trading#change'],
                'row_attr' => ['class' => 'input-group'],
                'label_attr' => ['class' => 'input-group-text bg-primary border-primary text-white'],
                'query_builder' => fn (TickerRepository $tr) => $tr->findAllByMarketQB($buyMarket)
            ])
            ->add('sellTicker', EntityType::class, [
                'required' => false,
                'label' => 'Ticker',
                'class' => Ticker::class,
                'attr' => ['data-action' => 'trading#change'],
                'row_attr' => ['class' => 'input-group'],
                'label_attr' => ['class' => 'input-group-text bg-primary border-primary text-white'],
                'query_builder' => fn (TickerRepository $tr) => $tr->findAllByMarketQB($sellMarket)
            ])
        ;

        $builder
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $eventData = $event->getData();
                $formData = $event->getForm()->getData();

                if (!empty($formData['buyMarket']) && (int)$eventData['buyMarket'] !== $formData['buyMarket']->getId()) {
                    $eventData['buyTicker'] = '';
                }
                if (!empty($formData['sellMarket']) && (int)$eventData['sellMarket'] !== $formData['sellMarket']->getId()) {
                    $eventData['sellTicker'] = '';
                }

                $event->setData($eventData);
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data' => [],
        ]);
    }
}
