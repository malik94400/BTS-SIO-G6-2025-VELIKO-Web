<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\PasswordStrength;

class MdpOublieFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('newPassword', RepeatedType::class, [
            'type' => PasswordType::class,
            'first_options' => [
                'label' => 'Nouveau mot de passe',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez entrer un nouveau mot de passe',
                    ]),
                    new Assert\Length([
                        'min' => 8,
                        'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères',
                        'max' => 4096,
                    ]),
                    new PasswordStrength([
                        'minScore' => PasswordStrength::STRENGTH_WEAK, // Niveau de sécurité fort
                        'message' => 'Votre mot de passe doit être forts: veuillez inclure des majuscules, des minuscules, des chiffres et des caractères spéciaux',
                    ]),
                ],
            ],
            'second_options' => [
                'label' => 'Confirmer votre nouveau mot de passe',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez confirmer votre nouveau mot de passe',
                    ]),
                ],
            ],
            'invalid_message' => 'Les mots de passe ne correspondent pas.',
        ])
            ->add('submit', SubmitType::class, [
                'label' => 'Modifier le mdp'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
