<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Redirect if already logged in
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ): Response {
        // Redirect if already logged in
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');

            // Validation
            if (empty($name) || empty($email) || empty($password)) {
                $this->addFlash('error', 'Alle velden zijn verplicht.');
                return $this->render('security/register.html.twig');
            }

            if ($password !== $confirmPassword) {
                $this->addFlash('error', 'Wachtwoorden komen niet overeen.');
                return $this->render('security/register.html.twig');
            }

            if (strlen($password) < 8) {
                $this->addFlash('error', 'Wachtwoord moet minimaal 8 tekens zijn.');
                return $this->render('security/register.html.twig');
            }

            // Check if email already exists
            if ($userRepository->findByEmail($email)) {
                $this->addFlash('error', 'Dit e-mailadres is al in gebruik.');
                return $this->render('security/register.html.twig');
            }

            // Create new user
            $user = new User();
            $user->setName($name);
            $user->setEmail($email);
            $user->setPassword($passwordHasher->hashPassword($user, $password));

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Account aangemaakt! U kunt nu inloggen.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.html.twig');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // This method will be intercepted by the security system
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
