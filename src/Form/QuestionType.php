<?php

namespace App\Form;

use App\Entity\Question;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class QuestionType extends AbstractType
{

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('score', TextType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Score']
            ])
            ->add('category', TextType::class, [
                'label' => 'Catégorie',
                'attr' => [
                    'class' => 'form-control category-autocomplete',
                    'list' => 'categorySuggestions',
                    'autocomplete' => 'off',
                    'style' => 'text-transform: uppercase;'
                ]
            ])

            ->add('difficultylevel', ChoiceType::class, [
                'choices' => [
                    'Facile' => 'Facile',
                    'Moyen' => 'Moyen',
                    'Difficile' => 'Difficile',
                ],
                'expanded' => false,
                'multiple' => false,
                'attr' => ['class' => 'form-control']
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

        $builder->get('category')->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            if ($data) {
                $event->setData(strtoupper($data));
            }
        });

    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Question::class,
        ]);
    }
}
