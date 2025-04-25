<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isAdmin = $options['is_admin'];
        $builder
            ->add('username', TextType::class, ['label' => "Nom d'utilisateur"])
            ->add('email', EmailType::class, ['label' => 'Adresse email'])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                ],
                'label' => !$isAdmin ? ' ' : 'Rôles',
                'multiple' => true,
                'expanded' => true,
                'attr' => [
                    'style' => !$isAdmin ? 'display: none;' : '',
                ],
                'disabled' => !$isAdmin,
                'constraints' => [
                    new NotBlank(['message' => 'Vous devez sélectionner un rôle.']),
                ],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les deux mots de passe doivent correspondre.',
                'mapped' => false,
                'first_options' => [
                    'label' => !$isAdmin
                        ? 'Mot de passe'
                        : 'Mot de passe (laissez vide pour conserver le mot de passe actuel)',
                ],
                'second_options' => ['label' => 'Tapez le mot de passe à nouveau'],
                'required' => !$isAdmin,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'is_admin' => false,
        ]);
    }
}
