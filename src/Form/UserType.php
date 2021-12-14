<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

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
                ->add('plainPassword', PasswordType::class, [
                    'attr' => [
                        'class' => 'password-field',
                        'placeholder' => '********'
                    ],
                    'required' => true,
                    'mapped' => false,
                    'attr' => ['autocomplete' => 'new-password'],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Entrez un mot de passe.',
                        ]),
                        new Length([
                            'min' => 6,
                            'minMessage' => 'Votre mot de passe doit faire au minimum {{ limit }} charactères.',
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                            'maxMessage' => 'Votre mot de passe ne doit pas excéder {{ limit }} charactères.'
                        ]),
                    ],
                ]);
        }
        if ($this->permissionsManager->isGranted(User::ADMIN_ROLE)) {
            $builder->add('roles', ChoiceType::class, [
                'label' => 'Rôle',
                'choices' => [
                    'Utilisateur' => User::USER_ROLE,
                    'Administrateur' => User::ADMIN_ROLE
                ],
                'multiple' => false,
            ]);

            // see https://stackoverflow.com/questions/51744484/symfony-form-choicetype-error-array-to-string-covnersion
            $builder->get('roles')->addModelTransformer(new CallbackTransformer(
                function ($rolesArray) {
                    return count($rolesArray) ? $rolesArray[0] : null;
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
