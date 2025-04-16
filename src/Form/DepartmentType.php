<?php

namespace App\Form;

use App\Entity\Department;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
// Add NumberType if you want totalEquipe in the form
// use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DepartmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom', // Label for accessibility, can be hidden visually if needed
                'required' => true,
                'attr' => ['placeholder' => 'Nom du département'] // Placeholder visible in input
            ])
            ->add('description', TextareaType::class, [ // Use Textarea for longer descriptions
                'label' => 'Description',
                'required' => false, // Make description optional if desired
                'attr' => ['placeholder' => 'Description', 'rows' => 2] // Smaller textarea
            ])
            // ->add('totalEquipe', NumberType::class, [ // Uncomment if you need this in the form
            //     'label' => 'Total Équipe',
            //     'required' => false, // Usually calculated, so maybe not required here
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Department::class,
        ]);
    }
}