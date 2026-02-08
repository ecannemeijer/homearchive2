<?php

namespace App\Controller;

use App\Entity\Password;
use App\Repository\PasswordRepository;
use App\Service\EncryptionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/password-vault')]
class PasswordVaultController extends AbstractController
{
    public function __construct(
        private EncryptionService $encryptionService,
        private EntityManagerInterface $entityManager,
        private PasswordRepository $passwordRepository
    ) {
    }

    #[Route('', name: 'password_vault_index')]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $search = $request->query->get('search', '');

        if ($search) {
            $passwords = $this->passwordRepository->search($user, $search);
        } else {
            $passwords = $this->passwordRepository->findByUser($user);
        }

        return $this->render('password_vault/index.html.twig', [
            'passwords' => $passwords,
            'search' => $search,
        ]);
    }

    #[Route('/create', name: 'password_vault_create')]
    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($request->isMethod('POST')) {
            $title = $request->request->get('title');
            $username = $request->request->get('username');
            $passwordPlain = $request->request->get('password');
            $websiteUrl = $request->request->get('website_url');
            $notes = $request->request->get('notes');
            $tags = $request->request->get('tags');

            if (empty($title) || empty($passwordPlain)) {
                $this->addFlash('error', 'Titel en wachtwoord zijn verplicht.');
                return $this->redirectToRoute('password_vault_create');
            }

            $password = new Password();
            $password->setUser($this->getUser());
            $password->setTitle($title);
            $password->setUsername($username);
            $password->setPasswordEncrypted($this->encryptionService->encrypt($passwordPlain));
            $password->setWebsiteUrl($websiteUrl);
            $password->setNotes($notes);
            $password->setTags($tags);

            $this->entityManager->persist($password);
            $this->entityManager->flush();

            $this->addFlash('success', 'Wachtwoord opgeslagen.');
            return $this->redirectToRoute('password_vault_index');
        }

        // Generate a secure password suggestion
        $suggestedPassword = $this->encryptionService->generateSecurePassword(16);

        return $this->render('password_vault/create.html.twig', [
            'suggested_password' => $suggestedPassword,
        ]);
    }

    #[Route('/{id}', name: 'password_vault_show', requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $password = $this->passwordRepository->find($id);

        if (!$password || $password->getUser() !== $user) {
            $this->addFlash('error', 'Wachtwoord niet gevonden.');
            return $this->redirectToRoute('password_vault_index');
        }

        // Decrypt password for display
        $decryptedPassword = $this->encryptionService->decrypt($password->getPasswordEncrypted());

        return $this->render('password_vault/show.html.twig', [
            'password' => $password,
            'decrypted_password' => $decryptedPassword,
        ]);
    }

    #[Route('/{id}/edit', name: 'password_vault_edit', requirements: ['id' => '\d+'])]
    public function edit(int $id, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $password = $this->passwordRepository->find($id);

        if (!$password || $password->getUser() !== $user) {
            $this->addFlash('error', 'Wachtwoord niet gevonden.');
            return $this->redirectToRoute('password_vault_index');
        }

        if ($request->isMethod('POST')) {
            $title = $request->request->get('title');
            $username = $request->request->get('username');
            $passwordPlain = $request->request->get('password');
            $websiteUrl = $request->request->get('website_url');
            $notes = $request->request->get('notes');
            $tags = $request->request->get('tags');

            if (empty($title)) {
                $this->addFlash('error', 'Titel is verplicht.');
                return $this->redirectToRoute('password_vault_edit', ['id' => $id]);
            }

            $password->setTitle($title);
            $password->setUsername($username);
            $password->setWebsiteUrl($websiteUrl);
            $password->setNotes($notes);
            $password->setTags($tags);

            // Only update password if a new one is provided
            if (!empty($passwordPlain)) {
                $password->setPasswordEncrypted($this->encryptionService->encrypt($passwordPlain));
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Wachtwoord bijgewerkt.');
            return $this->redirectToRoute('password_vault_show', ['id' => $id]);
        }

        // Decrypt password for editing
        $decryptedPassword = $this->encryptionService->decrypt($password->getPasswordEncrypted());

        return $this->render('password_vault/edit.html.twig', [
            'password' => $password,
            'decrypted_password' => $decryptedPassword,
        ]);
    }

    #[Route('/{id}/delete', name: 'password_vault_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $password = $this->passwordRepository->find($id);

        if (!$password || $password->getUser() !== $user) {
            $this->addFlash('error', 'Wachtwoord niet gevonden.');
            return $this->redirectToRoute('password_vault_index');
        }

        $this->entityManager->remove($password);
        $this->entityManager->flush();

        $this->addFlash('success', 'Wachtwoord verwijderd.');
        return $this->redirectToRoute('password_vault_index');
    }

    #[Route('/api/generate-password', name: 'password_generate_api')]
    public function generatePassword(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $length = $request->query->getInt('length', 16);
        $length = max(8, min($length, 32)); // Between 8 and 32

        $password = $this->encryptionService->generateSecurePassword($length);
        $strength = $this->encryptionService->calculatePasswordStrength($password);

        return new JsonResponse([
            'password' => $password,
            'length' => strlen($password),
            'strength' => $strength,
        ]);
    }

    #[Route('/api/check-strength', name: 'password_check_strength')]
    public function checkStrength(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $password = $request->query->get('password', '');
        $strength = $this->encryptionService->calculatePasswordStrength($password);

        $label = 'Zwak';
        if ($strength >= 80) $label = 'Zeer sterk';
        elseif ($strength >= 60) $label = 'Sterk';
        elseif ($strength >= 40) $label = 'Gemiddeld';

        return new JsonResponse([
            'strength' => $strength,
            'label' => $label,
        ]);
    }
}
