<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\FortuneCookieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FortuneController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function index(Request $request, EntityManagerInterface $entityManager, CategoryRepository $categoryRepository): Response
    {
//        $entityManager->getFilters()
//            ->enable('fortune_cookie_discontinued')
//            ->setParameter('discontinued', false);

        $search = $request->query->get('q');
        if ($search) {
            $categories = $categoryRepository->search($search);
        } else {
            $categories = $categoryRepository->findAllOrdered();
        }

        return $this->render('fortune/homepage.html.twig',[
            'categories' => $categories,
        ]);
    }

    #[Route('/category/{id}', name: 'app_category_show')]
//    public function showCategory(Category $category): Response
    public function showCategory(int $id, CategoryRepository $categoryRepository, FortuneCookieRepository $fortuneCookieRepository): Response
    {
        $category = $categoryRepository->findWithFortunesJoin($id);
        if (!$category) {
            throw $this->createNotFoundException('Category not found!');
        }

        $result = $fortuneCookieRepository->countNumberPrintedForCategory($category);

        return $this->render('fortune/showCategory.html.twig',[
            'category' => $category,
            'fortunesPrinted' => $result['fortunesPrinted'],
            'fortunesAverage' => $result['fortunesAverage'],
            'categoryName' => $result['name'],
        ]);
    }
}
