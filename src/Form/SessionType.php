<?php
namespace App\Form;

use App\Entity\Session;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class SessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['min' => (new \DateTime())->format('Y-m-d')]
            ])
            // The 'salle' field for in-person sessions
            ->add('salle', TextType::class, [
                'label' => 'Room Number',
                'required' => false,
                'attr' => [
                    'placeholder' => 'For in-person sessions',
                    'class' => 'session-type-fields'
                ]
            ]);

        // Handling the 'is_online' checkbox
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $session = $event->getData();
            $form = $event->getForm();
            
            // Add the 'is_online' checkbox, which will toggle the fields visibility
            $form->add('is_online', CheckboxType::class, [
                'label' => 'Online Session?',
                'required' => false,
                'mapped' => false,
                'data' => $session ? $session->getIs_online() : false,
                'attr' => [
                    'class' => 'session-type-toggle',
                    'data-bs-toggle' => 'collapse',
                    'data-bs-target' => '.session-type-fields'
                ]
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Session::class,
            'allow_extra_fields' => true,
            'empty_data' => function () {
                $session = new Session();
                $session->setIs_online(false); // Initialize with default value
                return $session;
            }
        ]);
    }
}
