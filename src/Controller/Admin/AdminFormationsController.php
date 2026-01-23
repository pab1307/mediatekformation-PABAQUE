<?php

namespace App\Controller\Admin;

use App\Entity\Formation;
use App\Form\FormationType;
use App\Repository\FormationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/formations')]
class AdminFormationsController extends AbstractController
{
    public function __construct(
        private FormationRepository $formationRepository,
        private EntityManagerInterface $em
    ) {
    }

    #[Route('/', name: 'admin_formations')]
    public function index(Request $request, FormationRepository $repo): Response {
        $tri = $request->query->get('tri', 'date');
        $ordre = $request->query->get('ordre', 'DESC');
        $filtreTitre = $request->query->get('filtre_title');
        $filtrePlaylist = $request->query->get('filtre_playlist');
        $categorieId = $request->query->get('categorie');

        $formations = $repo->findForBackOffice(
                $tri,
                $ordre,
                $filtreTitre,
                $filtrePlaylist,
                $categorieId ? (int) $categorieId : null
        );

        return $this->render('admin/formations/index.html.twig', [
                    'formations' => $formations,
                    'tri' => $tri,
                    'ordre' => $ordre,
                    'filtre_title' => $filtreTitre,
                    'filtre_playlist' => $filtrePlaylist,
                    'categorie_id' => $categorieId,
        ]);
    }

    #[Route('/add', name: 'admin_formations_add')]
    public function add(Request $request): Response
    {
        $formation = new Formation();

        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($formation);
            $this->em->flush();

            $this->addFlash('success', 'La formation a bien été ajoutée.');
            return $this->redirectToRoute('admin_formations');
        }

        return $this->render('admin/formations/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_formations_edit')]
    public function edit(Formation $formation, Request $request): Response
    {
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'La formation a bien été modifiée.');
            return $this->redirectToRoute('admin_formations');
        }

        return $this->render('admin/formations/form.html.twig', [
            'form'      => $form->createView(),
            'formation' => $formation,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_formations_delete', methods: ['POST'])]
    public function delete(Formation $formation, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete' . $formation->getId(), $request->request->get('_token'))) {

            // La suppression de la formation la retire automatiquement de la playlist
            // et des catégories si les relations Doctrine sont bien configurées.
            $this->em->remove($formation);
            $this->em->flush();

            $this->addFlash('success', 'La formation a bien été supprimée.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide, la formation n\'a pas été supprimée.');
        }

        return $this->redirectToRoute('admin_formations');
    }
}
