<?php

namespace App\Form;

use App\Entity\Question;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('score', TextType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Score']
            ])
            ->add('category', TextType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Catégorie']
            ])
            ->add('difficultylevel', TextType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Niveau de difficulté']
            ])
            ->add('option1', TextType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Option 1']
            ])
            ->add('option2', TextType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Option 2']
            ])
            ->add('option3', TextType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Option 3']
            ])
            ->add('option4')
            ->add('question_title' )
            ->add('right_answer');

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Question::class,
        ]);
    }
}
