<?php

namespace App\Controller;

use App\Entity\Subscription;
use App\Repository\SubscriptionRepository;
use App\Repository\CategoryRepository;
use App\Service\EncryptionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/subscriptions')]
class SubscriptionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SubscriptionRepository $subscriptionRepository,
        private CategoryRepository $categoryRepository,
        private EncryptionService $encryptionService
    ) {
    }

    #[Route('', name: 'subscription_index')]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $type = $request->query->get('type');
        $category = $request->query->get('category');
        $search = $request->query->get('search');

        if ($search) {
            $subscriptions = $this->subscriptionRepository->search($user, $search);
        } else {
            $subscriptions = $this->subscriptionRepository->findByUser($user, $type, $category);
        }

        $categories = $this->categoryRepository->findByUser($user);

        return $this->render('subscription/index.html.twig', [
            'subscriptions' => $subscriptions,
            'categories' => $categories,
            'current_type' => $type,
            'current_category' => $category,
            'search' => $search,
        ]);
    }

    #[Route('/create', name: 'subscription_create')]
    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $type = $request->request->get('type');
            $cost = $request->request->get('cost');
            $frequency = $request->request->get('frequency');

            if (empty($name) || empty($type) || empty($cost)) {
                $this->addFlash('error', 'Naam, type en kosten zijn verplicht.');
                return $this->redirectToRoute('subscription_create');
            }

            $subscription = new Subscription();
            $subscription->setUser($user);
            $subscription->setName($name);
            $subscription->setType($type);
            $subscription->setCost($cost);
            $subscription->setFrequency($frequency);
            $subscription->setCategory($request->request->get('category'));
            $subscription->setBillingDate($request->request->get('billing_date') ? (int)$request->request->get('billing_date') : null);
            $subscription->setStartDate($request->request->get('start_date') ? new \DateTime($request->request->get('start_date')) : null);
            $subscription->setEndDate($request->request->get('end_date') ? new \DateTime($request->request->get('end_date')) : null);
            $subscription->setIsMonthlyCancelable($request->request->get('is_monthly_cancelable') === '1');
            $subscription->setUsername($request->request->get('username'));
            $subscription->setWebsiteUrl($request->request->get('website_url'));
            $subscription->setNotes($request->request->get('notes'));
            $subscription->setRenewalReminder($request->request->get('renewal_reminder') ? (int)$request->request->get('renewal_reminder') : 7);

            // Encrypt password if provided
            if ($passwordPlain = $request->request->get('password')) {
                $subscription->setPasswordEncrypted($this->encryptionService->encrypt($passwordPlain));
            }

            $this->entityManager->persist($subscription);
            $this->entityManager->flush();

            $this->addFlash('success', 'Abonnement aangemaakt.');
            return $this->redirectToRoute('subscription_show', ['id' => $subscription->getId()]);
        }

        $categories = $this->categoryRepository->findByUser($user);

        return $this->render('subscription/create.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/{id}', name: 'subscription_show', requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $subscription = $this->subscriptionRepository->find($id);

        if (!$subscription || $subscription->getUser() !== $user) {
            $this->addFlash('error', 'Abonnement niet gevonden.');
            return $this->redirectToRoute('subscription_index');
        }

        // Decrypt password if exists
        $decryptedPassword = null;
        if ($subscription->getPasswordEncrypted()) {
            $decryptedPassword = $this->encryptionService->decrypt($subscription->getPasswordEncrypted());
        }

        return $this->render('subscription/show.html.twig', [
            'subscription' => $subscription,
            'decrypted_password' => $decryptedPassword,
        ]);
    }

    #[Route('/{id}/edit', name: 'subscription_edit', requirements: ['id' => '\d+'])]
    public function edit(int $id, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $subscription = $this->subscriptionRepository->find($id);

        if (!$subscription || $subscription->getUser() !== $user) {
            $this->addFlash('error', 'Abonnement niet gevonden.');
            return $this->redirectToRoute('subscription_index');
        }

        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $type = $request->request->get('type');
            $cost = $request->request->get('cost');
            $frequency = $request->request->get('frequency');

            if (empty($name) || empty($type) || empty($cost)) {
                $this->addFlash('error', 'Naam, type en kosten zijn verplicht.');
                return $this->redirectToRoute('subscription_edit', ['id' => $id]);
            }

            $subscription->setName($name);
            $subscription->setType($type);
            $subscription->setCost($cost);
            $subscription->setFrequency($frequency);
            $subscription->setCategory($request->request->get('category'));
            $subscription->setBillingDate($request->request->get('billing_date') ? (int)$request->request->get('billing_date') : null);
            $subscription->setStartDate($request->request->get('start_date') ? new \DateTime($request->request->get('start_date')) : null);
            $subscription->setEndDate($request->request->get('end_date') ? new \DateTime($request->request->get('end_date')) : null);
            $subscription->setIsMonthlyCancelable($request->request->get('is_monthly_cancelable') === '1');
            $subscription->setUsername($request->request->get('username'));
            $subscription->setWebsiteUrl($request->request->get('website_url'));
            $subscription->setNotes($request->request->get('notes'));
            $subscription->setRenewalReminder($request->request->get('renewal_reminder') ? (int)$request->request->get('renewal_reminder') : 7);
            $subscription->setIsActive($request->request->get('is_active') === '1');

            // Update password if new one provided
            if ($passwordPlain = $request->request->get('password')) {
                $subscription->setPasswordEncrypted($this->encryptionService->encrypt($passwordPlain));
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Abonnement bijgewerkt.');
            return $this->redirectToRoute('subscription_show', ['id' => $id]);
        }

        $categories = $this->categoryRepository->findByUser($user);

        // Decrypt password if exists
        $decryptedPassword = null;
        if ($subscription->getPasswordEncrypted()) {
            $decryptedPassword = $this->encryptionService->decrypt($subscription->getPasswordEncrypted());
        }

        return $this->render('subscription/edit.html.twig', [
            'subscription' => $subscription,
            'decrypted_password' => $decryptedPassword,
            'categories' => $categories,
        ]);
    }

    #[Route('/{id}/delete', name: 'subscription_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $subscription = $this->subscriptionRepository->find($id);

        if (!$subscription || $subscription->getUser() !== $user) {
            $this->addFlash('error', 'Abonnement niet gevonden.');
            return $this->redirectToRoute('subscription_index');
        }

        $this->entityManager->remove($subscription);
        $this->entityManager->flush();

        $this->addFlash('success', 'Abonnement verwijderd.');
        return $this->redirectToRoute('subscription_index');
    }
}
