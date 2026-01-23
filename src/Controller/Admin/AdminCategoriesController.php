<?php

namespace App\Controller\Admin;

use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/categories')]
class AdminCategoriesController extends AbstractController
{
    public function __construct(
        private CategorieRepository $categorieRepository,
        private EntityManagerInterface $em
    ) {
    }

    #[Route('/', name: 'admin_categories')]
    public function index(): Response
    {
        // si tu as une méthode findAllOrderByName, tu peux l’utiliser ici
        // $categories = $this->categorieRepository->findAllOrderByName('ASC');
        $categories = $this->categorieRepository->findAll();

        return $this->render('admin/categories/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/add', name: 'admin_categories_add')]
    public function add(Request $request): Response
    {
        $categorie = new Categorie();

        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($categorie);
            $this->em->flush();

            $this->addFlash('success', 'La catégorie a bien été ajoutée.');
            return $this->redirectToRoute('admin_categories');
        }

        return $this->render('admin/categories/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_categories_delete', methods: ['POST'])]
    public function delete(Categorie $categorie, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $categorie->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_categories');
        }

        // Option sécurité : on ne supprime pas une catégorie utilisée
        if ($categorie->getFormations()->count() > 0) {
            $this->addFlash('error', 'Impossible de supprimer cette catégorie : des formations y sont encore rattachées.');
            return $this->redirectToRoute('admin_categories');
        }

        $this->em->remove($categorie);
        $this->em->flush();

        $this->addFlash('success', 'La catégorie a bien été supprimée.');
        return $this->redirectToRoute('admin_categories');
    }
}
