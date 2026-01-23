<?php

namespace App\Controller\Admin;

use App\Entity\Playlist;
use App\Form\PlaylistType;
use App\Repository\PlaylistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/playlists')]
class AdminPlaylistsController extends AbstractController
{
    public function __construct(
        private PlaylistRepository $playlistRepository,
        private EntityManagerInterface $em
    ) {
    }

    #[Route('/', name: 'admin_playlists')]
    public function index(Request $request): Response
    {
        // paramètres de tri / filtre (beaucoup plus simples que pour les formations)
        $tri        = $request->query->get('tri', 'name');   // 'name' ou 'nb'
        $ordre      = $request->query->get('ordre', 'ASC');  // 'ASC' ou 'DESC'
        $filtreName = $request->query->get('filtre_name');   // filtre sur le nom

        // on utilise ta méthode findForBackOffice du PlaylistRepository
        $playlists = $this->playlistRepository->findForBackOffice(
            $tri,
            $ordre,
            $filtreName
        );

        return $this->render('admin/playlists/index.html.twig', [
            'playlists'   => $playlists,
            'tri'         => $tri,
            'ordre'       => $ordre,
            'filtre_name' => $filtreName,
        ]);
    }

    #[Route('/add', name: 'admin_playlists_add')]
    public function add(Request $request): Response
    {
        $playlist = new Playlist();

        $form = $this->createForm(PlaylistType::class, $playlist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($playlist);
            $this->em->flush();

            $this->addFlash('success', 'La playlist a bien été ajoutée.');
            return $this->redirectToRoute('admin_playlists');
        }

        return $this->render('admin/playlists/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_playlists_edit')]
    public function edit(Playlist $playlist, Request $request): Response
    {
        $form = $this->createForm(PlaylistType::class, $playlist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'La playlist a bien été modifiée.');
            return $this->redirectToRoute('admin_playlists');
        }

        return $this->render('admin/playlists/form.html.twig', [
            'form'     => $form->createView(),
            'playlist' => $playlist,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_playlists_delete', methods: ['POST'])]
    public function delete(Playlist $playlist, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $playlist->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide, la playlist n\'a pas été supprimée.');
            return $this->redirectToRoute('admin_playlists');
        }

        // règle métier : on ne supprime pas une playlist qui contient des formations
        if ($playlist->getFormations()->count() > 0) {
            $this->addFlash('error', 'Impossible de supprimer cette playlist : des formations y sont rattachées.');
            return $this->redirectToRoute('admin_playlists');
        }

        $this->em->remove($playlist);
        $this->em->flush();

        $this->addFlash('success', 'La playlist a bien été supprimée.');
        return $this->redirectToRoute('admin_playlists');
    }
}
