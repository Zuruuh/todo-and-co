<?php

namespace App\Form;

use App\Entity\User,
    Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\CallbackTransformer,
    Symfony\Component\Form\Extension\Core\Type\ChoiceType,
    Symfony\Component\Form\FormBuilderInterface,
    Symfony\Component\Form\Extension\Core\Type\EmailType,
    Symfony\Component\Form\Extension\Core\Type\TextType,
    Symfony\Component\Form\Extension\Core\Type\PasswordType,
    Symfony\Component\OptionsResolver\OptionsResolver,
    Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface,
    Symfony\Component\Validator\Constraints\Length,
    Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @codeCoverageIgnore
 */
class UserType extends AbstractType
{
    private bool $displayPasswordField;

    public function __construct(
        private AuthorizationCheckerInterface $permissionsManager
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->displayPasswordField = $options['displayPasswordField'];

        $builder
            ->add('username', TextType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'Admin'
                ]
            ])
            ->add('email', EmailType::class, [
                'invalid_message' => 'Entrez une addresse mail valide.',
                'attr' => [
                    'placeholder' => 'email@address.com'
                ]
            ]);
        if ($this->displayPasswordField) {
            $builder
                ->add('password', PasswordType::class, [
                    'attr' => [
                        'class' => 'password-field',
                        'placeholder' => '********'
                    ],
                    'required' => true,
                    'attr' => ['autocomplete' => 'new-password'],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Entrez un mot de passe.',
                        ]),
                        new Length([
                            'min' => 6,
                            'minMessage' => 'Votre mot de passe doit faire au minimum {{ limit }} charact??res.',
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                            'maxMessage' => 'Votre mot de passe ne doit pas exc??der {{ limit }} charact??res.'
                        ]),
                    ],
                ]);
        }
        if ($this->permissionsManager->isGranted(User::ADMIN_ROLE)) {
            $builder->add('roles', ChoiceType::class, [
                'label' => 'R??le',
                'choices' => [
                    'Utilisateur' => User::USER_ROLE,
                    'Administrateur' => User::ADMIN_ROLE
                ],
                'multiple' => false,
            ]);

            // see https://stackoverflow.com/questions/51744484/symfony-form-choicetype-error-array-to-string-covnersion
            $builder->get('roles')->addModelTransformer(new CallbackTransformer(
                function ($rolesArray) {
                    return count($rolesArray)
                        ? (array_key_exists(0, $rolesArray)
                            ? $rolesArray[0]
                            : null
                        ) : null;
                },
                function ($rolesString) {
                    return [$rolesString];
                }
            ));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'displayPasswordField' => true
        ]);
        $resolver->setAllowedTypes('displayPasswordField', 'boolean');
    }
}
