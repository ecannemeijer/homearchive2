<?php

namespace App\Controller;

use App\Repository\SubscriptionRepository;
use App\Repository\PasswordRepository;
use App\Repository\NotificationRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    #[Route('/dashboard', name: 'dashboard')]
    public function index(
        SubscriptionRepository $subscriptionRepository,
        PasswordRepository $passwordRepository,
        NotificationRepository $notificationRepository,
        CategoryRepository $categoryRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        // Get user statistics
        $subscriptions = $subscriptionRepository->findByUser($user);
        $passwords = $passwordRepository->findByUser($user);
        $notifications = $notificationRepository->findUnreadByUser($user);

        // Calculate monthly cost
        $monthlyCost = $subscriptionRepository->calculateMonthlyCost($user);

        // Get expiring subscriptions
        $expiringSubscriptions = $subscriptionRepository->findExpiring($user, 30);

        // Recent subscriptions
        $recentSubscriptions = array_slice($subscriptions, 0, 5);

        // Count by type
        $subscriptionCount = count(array_filter($subscriptions, fn($s) => $s->getType() === 'subscription'));
        $insuranceCount = count(array_filter($subscriptions, fn($s) => $s->getType() === 'insurance'));

        return $this->render('dashboard/index.html.twig', [
            'total_subscriptions' => count($subscriptions),
            'subscription_count' => $subscriptionCount,
            'insurance_count' => $insuranceCount,
            'total_passwords' => count($passwords),
            'monthly_cost' => $monthlyCost,
            'yearly_cost' => $monthlyCost * 12,
            'expiring_subscriptions' => $expiringSubscriptions,
            'recent_subscriptions' => $recentSubscriptions,
            'notifications' => $notifications,
        ]);
    }
}
