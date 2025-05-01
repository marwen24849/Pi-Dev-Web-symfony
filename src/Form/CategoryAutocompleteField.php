<?php
// src/Form/CategorySelectType.php
namespace App\Form;

use App\Repository\QuestionRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;


class CategoryAutocompleteField extends AbstractType
{
    public function __construct(
        private RouterInterface $router,
        private QuestionRepository $questionRepository
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'autocomplete_url' => $this->router->generate('categories_autocomplete'),
            'tom_select_options' => [
                'create' => true,
                'maxOptions' => 10,
            ],
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['autocomplete_url'] = $options['autocomplete_url'];
        $view->vars['tom_select_options'] = $options['tom_select_options'];
    }

    public function getParent(): string
    {
        return TextType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'category_select';
    }
}