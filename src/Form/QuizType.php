<?php
// src/Form/QuizType.php

namespace App\Form;

use App\Entity\Quiz;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class QuizType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre du Quiz'
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Catégorie',
                'choices' => $this->getCategories($options['entity_manager']),
                'placeholder' => 'Choisir une catégorie'
            ])
            ->add('difficultylevel', ChoiceType::class, [
                'label' => 'Niveau de difficulté',
                'choices' => $this->getDifficultyLevels($options['entity_manager']),
                'placeholder' => 'Choisir un niveau'
            ])
            ->add('minimumSuccessPercentage', ChoiceType::class, [
                'label' => 'Pourcentage de réussite minimum',
                'choices' => array_combine(range(50, 100, 10), range(50, 100, 10))
            ])
            ->add('quizTime', IntegerType::class, [
                'label' => 'Temps du quiz (minutes)'
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            $form->add('questionCount', IntegerType::class, [
                'label' => 'Nombre de questions disponibles',
                'mapped' => false,

                'data' => 0
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Quiz::class,
            'entity_manager' => null,
        ]);
    }

    private function getCategories($em)
    {
        $categories = $em->getRepository('App\Entity\Question')
            ->createQueryBuilder('q')
            ->select('DISTINCT q.category')
            ->getQuery()
            ->getResult();

        $choices = [];
        foreach ($categories as $category) {
            $choices[$category['category']] = $category['category'];
        }

        return $choices;
    }

    private function getDifficultyLevels($em)
    {
        $levels = $em->getRepository('App\Entity\Question')
            ->createQueryBuilder('q')
            ->select('DISTINCT q.difficultylevel')
            ->getQuery()
            ->getResult();

        $choices = [];
        foreach ($levels as $level) {
            $choices[$level['difficultylevel']] = $level['difficultylevel'];
        }

        return $choices;
    }
}