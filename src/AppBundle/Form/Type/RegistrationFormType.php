<?php
namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class RegistrationFormType
 * @package AppBundle\Form\Type
 */
class RegistrationFormType extends AbstractType
{
    private $class;

    /**
     * @param string $class The User class name
     */
    public function __construct($class)
    {
        $this->class = $class;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->remove('username');
        $builder
            ->add('name', null, [ 'attr' => ['placeholder' => 'Name']])
            ->add('email', 'email', [
                'label' => 'form.email',
                'translation_domain' => 'FOSUserBundle',
                'attr' => [
                    'placeholder' => 'Email'
                ]
            ])
            ->add('plainPassword', null, [
                'label' => 'form.password',
                'translation_domain' => 'FOSUserBundle',
                'attr' => [
                    'placeholder' => 'Password'
                ]
            ]);
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'happystats_user_registration';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->class,
            'intention'  => 'registration',
        ));
    }

    // BC for SF < 2.7
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }
}