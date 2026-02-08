<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/categories')]
class CategoryController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CategoryRepository $categoryRepository
    ) {
    }

    #[Route('', name: 'category_index')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $categories = $this->categoryRepository->findByUser($user);

        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/create', name: 'category_create')]
    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $color = $request->request->get('color');

            if (empty($name)) {
                $this->addFlash('error', 'Naam is verplicht.');
                return $this->redirectToRoute('category_create');
            }

            $category = new Category();
            $category->setUser($user);
            $category->setName($name);
            $category->setColor($color ?: '#3B82F6');

            $this->entityManager->persist($category);
            $this->entityManager->flush();

            $this->addFlash('success', 'Categorie aangemaakt.');
            return $this->redirectToRoute('category_index');
        }

        return $this->render('category/create.html.twig');
    }

    #[Route('/{id}/edit', name: 'category_edit', requirements: ['id' => '\d+'])]
    public function edit(int $id, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $category = $this->categoryRepository->find($id);

        if (!$category || $category->getUser() !== $user) {
            $this->addFlash('error', 'Categorie niet gevonden.');
            return $this->redirectToRoute('category_index');
        }

        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $color = $request->request->get('color');

            if (empty($name)) {
                $this->addFlash('error', 'Naam is verplicht.');
                return $this->render('category/edit.html.twig', ['category' => $category]);
            }

            $category->setName($name);
            $category->setColor($color);

            $this->entityManager->flush();

            $this->addFlash('success', 'Categorie bijgewerkt.');
            return $this->redirectToRoute('category_index');
        }

        return $this->render('category/edit.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/{id}/delete', name: 'category_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $category = $this->categoryRepository->find($id);

        if (!$category || $category->getUser() !== $user) {
            $this->addFlash('error', 'Categorie niet gevonden.');
            return $this->redirectToRoute('category_index');
        }

        $this->entityManager->remove($category);
        $this->entityManager->flush();

        $this->addFlash('success', 'Categorie verwijderd.');
        return $this->redirectToRoute('category_index');
    }
}
