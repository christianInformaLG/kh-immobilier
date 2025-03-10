<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ChangePasswordUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', PasswordType::class,[
                'label' => 'Mon ancien mot de passe',
                'error_bubbling' => true,
                'required' => true,
                'attr' => [
                    'class' => 'form-control input has-text-centered',
                    'placeholder' => 'Entrer mon ancien mot de passe',
                    ],
                'mapped' => false,
                ]

            )

            ->add('plainPassword', RepeatedType::class, [
                'label' => 'Mon nouveau mot de passe',
                'attr' => ['class' => 'field'],
//                'row_attr' => ['class' => 'field'],
                'first_options'  => [
                    'label' => 'Mon nouveau mot de passe',
                    'error_bubbling' => true,
                    'required' => true,
                    'attr' => [
                        'class' => 'form-control mb-2',
                        'placeholder' => 'Entrer mon nouveau mot de passe',
                    ]
                ],
                'second_options' => [
                    'label' => false,
                    'error_bubbling' => false,
                    'required' => true,
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => 'Entrer une nouvelle foi mon nouveau mot de passe',
                    ]
                ],
                'mapped' => false,
                'type' => PasswordType::class,
                'invalid_message' => 'Les nouveaux mots de passe ne correspondent pas',
                'required' => true,
                'error_bubbling' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez remplir ce champ.']),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit contenir au moins 6 caractères.'
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
