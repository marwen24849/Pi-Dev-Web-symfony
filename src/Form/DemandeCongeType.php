<?php

namespace App\Form;

use App\Entity\DemandeConge;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DemandeCongeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $readonly = $options['edit_justification_only'];
        $editMode = $options['is_edit_mode'];

        $builder
            ->add('typeConge', ChoiceType::class, [
                'required' => false,
                'disabled' => $readonly,
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
                'disabled' => $readonly,
                'label' => 'Précisez le type',
            ])
            ->add('dateDebut', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'label' => 'Date de début',
                'disabled' => $readonly,
            ])
            ->add('dateFin', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'label' => 'Date de fin',
                'disabled' => $readonly,
            ])
            ->add('justification', TextareaType::class, [
                'required' => false,
                'label' => 'Justification',
                'disabled' => false,
            ])
            ->add('certificate', FileType::class, [
                'required' => false,
                'label' => 'Certificat médical',
                'mapped' => !$editMode, // 🔥 pas mappé en édition
                'disabled' => $editMode, // 🔥 désactivé en édition
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DemandeConge::class,
            'edit_justification_only' => false,
            'is_edit_mode' => false,
        ]);
    }
}
