<?php

namespace App\Form;

use App\Entity\DemandeConge;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DemandeCongeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('typeConge', ChoiceType::class, [
                'choices' => [
                    'Vacances' => 'Vacances',
                    'Maladie' => 'Maladie',
                    'Autre' => 'Autre',
                ],
                'placeholder' => '-- Sélectionnez --',
                'label' => 'Type de congé',
            ])
            ->add('autre', TextType::class, [
                'required' => false,
                'label' => 'Précisez le type'
            ])
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de début'
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de fin'
            ])
            ->add('justification', TextareaType::class, [
                'label' => 'Justification',
            ])
            ->add('certificate', FileType::class, [
                'required' => false,
                'label' => 'Certificat médical',
                'mapped' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DemandeConge::class,
        ]);
    }
}
