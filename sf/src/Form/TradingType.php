<?php

namespace App\Form;

use App\Entity\Market;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TradingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('buyMarket', EntityType::class, [
                'label' => 'Exchange',
                'class' => Market::class,
                'row_attr' => ['class' => 'input-group'],
                'label_attr' => ['class' => 'input-group-text bg-primary border-primary text-white'],
                'choice_label' => 'upperName'
            ])
            ->add('sellMarket', EntityType::class, [
                'label' => 'Exchange',
                'class' => Market::class,
                'row_attr' => ['class' => 'input-group'],
                'label_attr' => ['class' => 'input-group-text bg-primary border-primary text-white'],
                'choice_label' => 'upperName'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {

    }
}
