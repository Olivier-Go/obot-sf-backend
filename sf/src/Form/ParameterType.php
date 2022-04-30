<?php

namespace App\Form;

use App\Entity\Parameter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParameterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('workerOrderDiff', NumberType::class, [
                'label' => 'Différence entre les ordres',
                'help' => '(stablecoin)',
                'html5' => true,
                'scale' => 4,
                'attr' => [
                    'min' => '0',
                    'step' => '0.0001'
                ]
            ])
            ->add('workerOrderSize', IntegerType::class, [
                'label' => 'Taille des ordres',
                'help' => '(crypto)',
                'attr' => [
                    'min' => '1',
                    'step' => '1'
                ]
            ])
            ->add('workerNotSendOrder', CheckboxType::class, [
                'required' => false,
                'label' => 'Bloquer envoi des ordres aux exchanges',
                'row_attr' => [
                    'class' => 'form-switch ps-3 pt-2'
                ]
            ])
            ->add('workerStopAfterTransaction', CheckboxType::class, [
                'required' => false,
                'label' => 'Arrêt Node Server après première transaction',
                'row_attr' => [
                    'class' => 'form-switch ps-3 pt-2'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Parameter::class,
        ]);
    }
}
