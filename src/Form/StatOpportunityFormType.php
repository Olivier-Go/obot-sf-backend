<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StatOpportunityFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('display', ChoiceType::class, [
                'label' => false,
                'choices'  => [
                    'Jours' => 'day',
                    'Mois' => 'month',
                    'Années' => 'year',
                ],
                'data' => 'day',
                'attr' => [
                    'class' => 'form-select-sm',
                    'data-action' => 'stat-search#change'
                ],
            ])
            ->add('dateStart', DateType::class, [
                'label' => 'Du',
                'required' => false,
                'widget' => 'single_text',
                'format' => 'dd-MM-yyyy',
                'html5' => false,
                'row_attr' => ['class' => 'input-group input-group-sm'],
                'attr' => ['class' => 'litepicker-start']
            ])
            ->add('dateEnd', DateType::class, [
                'label' => 'Au',
                'required' => false,
                'widget' => 'single_text',
                'format' => 'dd-MM-yyyy',
                'html5' => false,
                'row_attr' => ['class' => 'input-group input-group-sm'],
                'attr' => ['class' => 'litepicker-end']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
