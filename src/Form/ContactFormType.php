<?php

namespace App\Form;

use App\Entity\ContactMessage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'example@email.com',
                    'id' => 'contact_email',
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Сообщение',
                'attr' => [
                    'placeholder' => 'Введите ваше сообщение (минимум 10 символов)',
                    'id' => 'contact_message',
                    'rows' => 5,
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Отправить',
                'attr' => [
                    'class' => 'btn-submit',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactMessage::class,
            // Включаем CSRF-защиту для формы
            'csrf_protection' => true,
        ]);
    }
}