<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\ContactMessage;
use App\Form\RegistrationFormType;
use App\Form\ContactFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FormsController extends AbstractController
{
    #[Route('/', name: 'app_forms')]
    public function index(): Response
    {
        $registrationForm = $this->createForm(RegistrationFormType::class, null, [
            'action' => $this->generateUrl('app_registration_submit'),
        ]);

        $contactForm = $this->createForm(ContactFormType::class, null, [
            'action' => $this->generateUrl('app_contact_submit'),
        ]);

        return $this->render('forms/index.html.twig', [
            'registrationForm' => $registrationForm->createView(),
            'contactForm' => $contactForm->createView(),
        ]);
    }

    #[Route('/api/register', name: 'app_registration_submit', methods: ['POST'])]
    public function submitRegistration(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];

        $user = new User();
        $user->setName($data['name'] ?? '');
        $user->setEmail($data['email'] ?? '');
        $user->setPhone($data['phone'] ?? '');
        $user->setPassword($data['password'] ?? '');

        // Валидируем сущность пользователя
        $errors = $validator->validate($user);
        $errorMessages = [];

        foreach ($errors as $error) {
            $field = $error->getPropertyPath();
            $errorMessages[$field] = $error->getMessage();
        }

        // Дополнительная проверка совпадения паролей
        if (isset($data['password'], $data['confirmPassword']) &&
            $data['password'] !== $data['confirmPassword']) {
            $errorMessages['confirmPassword'] = 'Пароли должны совпадать';
        }

        if (!empty($errorMessages)) {
            return new JsonResponse([
                'success' => false,
                'errors' => $errorMessages,
            ], 400);
        }

        // Хэшируем пароль перед сохранением
        $hashedPassword = password_hash($user->getPassword(), PASSWORD_DEFAULT);
        $user->setPassword($hashedPassword);

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'user' => [
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'phone' => $user->getPhone(),
            ],
        ]);
    }

    #[Route('/api/contact', name: 'app_contact_submit', methods: ['POST'])]
    public function submitContact(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $message = new ContactMessage();
        $message->setEmail($data['email'] ?? '');
        $message->setMessage($data['message'] ?? '');

        $errors = $validator->validate($message);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $field = $error->getPropertyPath();
                $errorMessages[$field] = $error->getMessage();
            }

            return new JsonResponse([
                'success' => false,
                'errors' => $errorMessages,
            ], 400);
        }

        $entityManager->persist($message);
        $entityManager->flush();

        // Проверяем, есть ли пользователь с таким email
        $userRepository = $entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => $message->getEmail()]);

        $displayName = $user ? $user->getName() : $message->getEmail();

        return new JsonResponse([
            'success' => true,
            'message' => [
                'displayName' => $displayName,
                'message' => $message->getMessage(),
            ],
        ]);
    }
}