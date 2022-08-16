<?php

namespace App\Form;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;



class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
             ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Le mot de passe doit être identique à sa confirmation.',
                'required' => true,
                
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        //$resolver->setDefaults([
          //  'data_class' => User::class,
        // ]);
        $resolver->setDefaults([]);
    }
}
