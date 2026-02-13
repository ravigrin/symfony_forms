<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Имя',
                'attr' => [
                    'placeholder' => 'Введите ваше имя',
                    'id' => 'registration_name',
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'example@email.com',
                    'id' => 'registration_email',
                ],
            ])
            ->add('phone', TextType::class, [
                'label' => 'Телефон',
                'attr' => [
                    'placeholder' => '+79001234567',
                    'id' => 'registration_phone',
                ],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Пароль',
                    'attr' => [
                        'placeholder' => 'Минимум 6 символов',
                        'id' => 'registration_password',
                    ],
                ],
                'second_options' => [
                    'label' => 'Повторите пароль',
                    'attr' => [
                        'placeholder' => 'Повторите пароль',
                        'id' => 'registration_confirm_password',
                    ],
                ],
                'invalid_message' => 'Пароли должны совпадать',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Зарегистрироваться',
                'attr' => [
                    'class' => 'btn-submit',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            // Включаем CSRF-защиту для формы
            'csrf_protection' => true,
        ]);
    }
}