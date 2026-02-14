<?php

namespace App\Controller;

use App\Entity\Subscription;
use App\Entity\Document;
use App\Repository\SubscriptionRepository;
use App\Repository\CategoryRepository;
use App\Repository\DocumentRepository;
use App\Service\EncryptionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/subscriptions')]
class SubscriptionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SubscriptionRepository $subscriptionRepository,
        private CategoryRepository $categoryRepository,
        private DocumentRepository $documentRepository,
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
            $subscription->setIsActive($request->request->get('is_active') === '1');
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

            // Handle file uploads
            $this->handleFileUploads($request, $subscription, $user);

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

            // Handle file uploads
            $this->handleFileUploads($request, $subscription, $user);

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

    #[Route('/{id}/upload', name: 'subscription_upload_document', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function uploadDocument(int $id, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $subscription = $this->subscriptionRepository->find($id);

        if (!$subscription || $subscription->getUser() !== $user) {
            $this->addFlash('error', 'Abonnement niet gevonden.');
            return $this->redirectToRoute('subscription_index');
        }

        $uploadedFile = $request->files->get('document');

        if (!$uploadedFile) {
            $this->addFlash('error', 'Geen bestand geselecteerd.');
            return $this->redirectToRoute('subscription_show', ['id' => $id]);
        }

        // Get file info BEFORE moving (these calls fail after move)
        $mimeType = $uploadedFile->getMimeType();
        $fileSize = $uploadedFile->getSize();
        $clientOriginalName = $uploadedFile->getClientOriginalName();
        
        // Validate file type
        $allowedMimeTypes = [
            'application/pdf',
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp'
        ];

        if (!in_array($mimeType, $allowedMimeTypes)) {
            $this->addFlash('error', 'Ongeldig bestandstype. Alleen PDF en afbeeldingen zijn toegestaan.');
            return $this->redirectToRoute('subscription_show', ['id' => $id]);
        }

        // Validate file size (max 5MB)
        if ($fileSize > 5242880) {
            $this->addFlash('error', 'Bestand is te groot. Maximaal 5MB toegestaan.');
            return $this->redirectToRoute('subscription_show', ['id' => $id]);
        }

        try {
            $originalFilename = pathinfo($clientOriginalName, PATHINFO_FILENAME);
            $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();

            // Create upload directory if it doesn't exist
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/documents';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $uploadedFile->move($uploadDir, $newFilename);

            // Create document entity
            $document = new Document();
            $document->setSubscription($subscription);
            $document->setUser($user);
            $document->setFilename($newFilename);
            $document->setOriginalFilename($clientOriginalName);
            $document->setFileType($mimeType);
            $document->setFileSize($fileSize);
            $document->setFilePath('/uploads/documents/' . $newFilename);

            $this->entityManager->persist($document);
            $this->entityManager->flush();

            $this->addFlash('success', 'Document succesvol geüpload.');
        } catch (FileException $e) {
            $this->addFlash('error', 'Er is een fout opgetreden bij het uploaden van het bestand.');
        }

        return $this->redirectToRoute('subscription_show', ['id' => $id]);
    }

    #[Route('/document/{id}/delete', name: 'subscription_delete_document', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function deleteDocument(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $document = $this->documentRepository->find($id);

        if (!$document || $document->getUser() !== $user) {
            $this->addFlash('error', 'Document niet gevonden.');
            return $this->redirectToRoute('subscription_index');
        }

        $subscriptionId = $document->getSubscription()->getId();

        // Delete physical file
        $filePath = $this->getParameter('kernel.project_dir') . '/public' . $document->getFilePath();
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $this->entityManager->remove($document);
        $this->entityManager->flush();

        $this->addFlash('success', 'Document verwijderd.');
        return $this->redirectToRoute('subscription_show', ['id' => $subscriptionId]);
    }

    /**
     * Handle multiple file uploads for a subscription
     */
    private function handleFileUploads(Request $request, Subscription $subscription, $user): void
    {
        $uploadedFiles = $request->files->get('documents', []);
        
        if (empty($uploadedFiles)) {
            return;
        }

        // Support both single file and multiple files
        if (!is_array($uploadedFiles)) {
            $uploadedFiles = [$uploadedFiles];
        }

        $allowedMimeTypes = [
            'application/pdf',
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp'
        ];

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/documents';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $successCount = 0;
        foreach ($uploadedFiles as $uploadedFile) {
            if (!$uploadedFile) {
                continue;
            }

            try {
                // Get file info BEFORE moving
                $mimeType = $uploadedFile->getMimeType();
                $fileSize = $uploadedFile->getSize();
                $clientOriginalName = $uploadedFile->getClientOriginalName();

                // Validate file type
                if (!in_array($mimeType, $allowedMimeTypes)) {
                    $this->addFlash('warning', "Bestand '{$clientOriginalName}' overgeslagen: ongeldig type.");
                    continue;
                }

                // Validate file size (max 5MB)
                if ($fileSize > 5242880) {
                    $this->addFlash('warning', "Bestand '{$clientOriginalName}' overgeslagen: te groot (max 5MB).");
                    continue;
                }

                $originalFilename = pathinfo($clientOriginalName, PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();

                $uploadedFile->move($uploadDir, $newFilename);

                // Create document entity
                $document = new Document();
                $document->setSubscription($subscription);
                $document->setUser($user);
                $document->setFilename($newFilename);
                $document->setOriginalFilename($clientOriginalName);
                $document->setFileType($mimeType);
                $document->setFileSize($fileSize);
                $document->setFilePath('/uploads/documents/' . $newFilename);

                $this->entityManager->persist($document);
                $successCount++;
            } catch (FileException $e) {
                $this->addFlash('warning', "Fout bij uploaden van '{$clientOriginalName}'.");
            }
        }

        if ($successCount > 0) {
            $this->entityManager->flush();
            $this->addFlash('success', "{$successCount} document(en) succesvol geüpload.");
        }
    }
}
