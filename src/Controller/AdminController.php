<?php

namespace App\Controller;

use App\Entity\Admin;
use App\Entity\User;
use App\Repository\AdminRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/login', name: 'admin_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Redirect if already logged in
        if ($this->getUser() && in_array('ROLE_ADMIN', $this->getUser()->getRoles(), true)) {
            return $this->redirectToRoute('admin_dashboard');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('admin/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'admin_logout')]
    public function logout(): void
    {
        // Intercepted by security system
        throw new \LogicException('This method can be blank.');
    }

    #[Route('/', name: 'admin_dashboard')]
    public function dashboard(
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Get server statistics
        $totalUsers = count($userRepository->findAll());
        $usersWithStats = $userRepository->findAllWithStats();

        // Calculate total subscriptions and passwords across all users
        $totalSubscriptions = 0;
        $totalPasswords = 0;
        foreach ($usersWithStats as $result) {
            $totalSubscriptions += $result['subscriptionCount'] ?? 0;
            $totalPasswords += $result['passwordCount'] ?? 0;
        }

        // Get server info
        $serverInfo = [
            'php_version' => PHP_VERSION,
            'symfony_version' => \Symfony\Component\HttpKernel\Kernel::VERSION,
            'database' => $entityManager->getConnection()->getDatabase(),
            'memory_usage' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB',
            'peak_memory' => round(memory_get_peak_usage() / 1024 / 1024, 2) . ' MB',
        ];

        return $this->render('admin/dashboard.html.twig', [
            'total_users' => $totalUsers,
            'total_subscriptions' => $totalSubscriptions,
            'total_passwords' => $totalPasswords,
            'server_info' => $serverInfo,
            'recent_users' => array_slice($userRepository->findBy([], ['createdAt' => 'DESC']), 0, 10),
        ]);
    }

    #[Route('/users', name: 'admin_users')]
    public function users(UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $users = $userRepository->findAll();

        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/users/create', name: 'admin_user_create')]
    public function createUser(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $email = $request->request->get('email');
            $password = $request->request->get('password');

            if (empty($name) || empty($email) || empty($password)) {
                $this->addFlash('error', 'Alle velden zijn verplicht.');
                return $this->render('admin/user_form.html.twig', ['user' => null]);
            }

            if ($userRepository->findByEmail($email)) {
                $this->addFlash('error', 'Dit e-mailadres is al in gebruik.');
                return $this->render('admin/user_form.html.twig', ['user' => null]);
            }

            $user = new User();
            $user->setName($name);
            $user->setEmail($email);
            $user->setPassword($passwordHasher->hashPassword($user, $password));

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Gebruiker aangemaakt.');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/user_form.html.twig', ['user' => null]);
    }

    #[Route('/users/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    public function deleteUser(
        int $id,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $userRepository->find($id);
        if (!$user) {
            $this->addFlash('error', 'Gebruiker niet gevonden.');
            return $this->redirectToRoute('admin_users');
        }

        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'Gebruiker verwijderd.');
        return $this->redirectToRoute('admin_users');
    }

    #[Route('/users/{id}/change-password', name: 'admin_user_change_password')]
    public function changeUserPassword(
        int $id,
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $userRepository->find($id);
        if (!$user) {
            $this->addFlash('error', 'Gebruiker niet gevonden.');
            return $this->redirectToRoute('admin_users');
        }

        if ($request->isMethod('POST')) {
            $newPassword = $request->request->get('new_password');
            $confirmPassword = $request->request->get('confirm_password');

            if (empty($newPassword) || empty($confirmPassword)) {
                $this->addFlash('error', 'Alle velden zijn verplicht.');
                return $this->render('admin/user_change_password.html.twig', ['user' => $user]);
            }

            if ($newPassword !== $confirmPassword) {
                $this->addFlash('error', 'Wachtwoorden komen niet overeen.');
                return $this->render('admin/user_change_password.html.twig', ['user' => $user]);
            }

            if (strlen($newPassword) < 8) {
                $this->addFlash('error', 'Wachtwoord moet minimaal 8 tekens zijn.');
                return $this->render('admin/user_change_password.html.twig', ['user' => $user]);
            }

            $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
            $entityManager->flush();

            $this->addFlash('success', 'Wachtwoord van gebruiker ' . $user->getName() . ' succesvol gewijzigd.');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/user_change_password.html.twig', ['user' => $user]);
    }

    #[Route('/administrators', name: 'admin_administrators')]
    public function administrators(AdminRepository $adminRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $admins = $adminRepository->findAll();

        return $this->render('admin/administrators.html.twig', [
            'admins' => $admins,
        ]);
    }

    #[Route('/administrators/create', name: 'admin_administrator_create')]
    public function createAdministrator(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        AdminRepository $adminRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($request->isMethod('POST')) {
            $username = $request->request->get('username');
            $name = $request->request->get('name');
            $password = $request->request->get('password');

            if (empty($username) || empty($password)) {
                $this->addFlash('error', 'Gebruikersnaam en wachtwoord zijn verplicht.');
                return $this->render('admin/admin_form.html.twig', ['admin' => null]);
            }

            if ($adminRepository->findByUsername($username)) {
                $this->addFlash('error', 'Deze gebruikersnaam is al in gebruik.');
                return $this->render('admin/admin_form.html.twig', ['admin' => null]);
            }

            $admin = new Admin();
            $admin->setUsername($username);
            $admin->setName($name);
            $admin->setPassword($passwordHasher->hashPassword($admin, $password));

            $entityManager->persist($admin);
            $entityManager->flush();

            $this->addFlash('success', 'Administrator aangemaakt.');
            return $this->redirectToRoute('admin_administrators');
        }

        return $this->render('admin/admin_form.html.twig', ['admin' => null]);
    }

    #[Route('/change-password', name: 'admin_change_password')]
    public function changePassword(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $admin = $this->getUser();

        if ($request->isMethod('POST')) {
            $currentPassword = $request->request->get('current_password');
            $newPassword = $request->request->get('new_password');
            $confirmPassword = $request->request->get('confirm_password');

            if (!$passwordHasher->isPasswordValid($admin, $currentPassword)) {
                $this->addFlash('error', 'Huidig wachtwoord is onjuist.');
                return $this->render('admin/change_password.html.twig');
            }

            if ($newPassword !== $confirmPassword) {
                $this->addFlash('error', 'Nieuwe wachtwoorden komen niet overeen.');
                return $this->render('admin/change_password.html.twig');
            }

            if (strlen($newPassword) < 8) {
                $this->addFlash('error', 'Wachtwoord moet minimaal 8 tekens zijn.');
                return $this->render('admin/change_password.html.twig');
            }

            $admin->setPassword($passwordHasher->hashPassword($admin, $newPassword));
            $entityManager->flush();

            $this->addFlash('success', 'Wachtwoord gewijzigd.');
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/change_password.html.twig');
    }
}
