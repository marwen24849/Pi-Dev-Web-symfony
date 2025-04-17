<?php

namespace App\Form;

use App\Entity\Equipe;
use App\Entity\Projet;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomProjet', TextType::class, [
                'label' => 'Nom du Projet:',
                'required' => true,
                'attr' => ['placeholder' => 'Nom du Projet']
            ])
            ->add('equipe', EntityType::class, [
                'class'         => Equipe::class,
                'query_builder' => function ($er) {
                    return $er->createQueryBuilder('e')
                        ->leftJoin('App\Entity\Projet', 'p', 'WITH', 'p.equipe = e')
                        ->where('p.id IS NULL');
                },
                'choice_label'  => 'name',
                'label'         => 'Équipe:',
                'placeholder'   => '-- Choisir une équipe --',
                'required'      => false,
                'attr'          => ['class' => 'form-select'],
            ])
            ->add('responsable', TextType::class, [
                'label' => 'Responsable:',
                'required' => false,
                'attr' => ['placeholder' => 'Nom du responsable']
            ])
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de Début:',
                'required' => false,
                'html5' => true,
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de Fin:',
                'required' => false,
                'html5' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Projet::class,
        ]);
    }
}