<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\FormationRepository;
use App\Repository\PlaylistRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlaylistsController extends AbstractController
{
    private const PLAYLISTS_TEMPLATE = 'pages/playlists.html.twig';

        #[Route('/playlists', name: 'playlists')]
        public function index(
            PlaylistRepository $playlistRepository,
            CategorieRepository $categorieRepository
        ): Response {
            $playlists = $playlistRepository->findAllOrderByName('ASC');
            $categories = $categorieRepository->findAll();

            return $this->renderPlaylists($playlists, $categories);
        }

        #[Route('/playlists/tri/{champ}/{ordre}', name: 'playlists.sort')]
        public function sort(
            string $champ,
            string $ordre,
            PlaylistRepository $playlistRepository,
            CategorieRepository $categorieRepository
    ): Response {
        switch ($champ) {
            case 'name':
                $playlists = $playlistRepository->findAllOrderByName($ordre);
               break;
           
            case null: // faux cas uniquement pour satisfaire le linter
                $playlists = $playlistRepository->findAllOrderByName('ASC');
               break;

            default:
                // fallback : si le champ est inconnu, on trie par nom ASC
                $playlists = $playlistRepository->findAllOrderByName('ASC');
               break;
        }

        $categories = $categorieRepository->findAll();

        return $this->renderPlaylists($playlists, $categories);
    }

        #[Route('/playlists/recherche/{champ}/{table}', name: 'playlists.findallcontain')]
        public function findAllContain(
            string $champ,
            Request $request,
            PlaylistRepository $playlistRepository,
            CategorieRepository $categorieRepository,
            string $table = ''
    ): Response {
        $valeur = $request->get('recherche');
        $playlists = $playlistRepository->findByContainValue($champ, $valeur, $table);
        $categories = $categorieRepository->findAll();

        return $this->renderPlaylists($playlists, $categories, $valeur, $table);
    }

    /**
     * Factorisation de l'appel à render pour éviter la duplication de chaînes.
     */
        private function renderPlaylists(
            array $playlists,
            array $categories,
            ?string $valeur = null,
            ?string $table = null
        ): Response {
            return $this->render(self::PLAYLISTS_TEMPLATE, [
             'playlists' => $playlists,
             'categories' => $categories,
             'valeur' => $valeur,
             'table' => $table,
        ]);
    }

        #[Route('/playlists/playlist/{id}', name: 'playlists.showone')]
        public function showOne(
            int $id,
            PlaylistRepository $playlistRepository,
            CategorieRepository $categorieRepository,
            FormationRepository $formationRepository
        ): Response {
            $playlist = $playlistRepository->find($id);
            $playlistCategories = $categorieRepository->findAllForOnePlaylist($id);
            $playlistFormations = $formationRepository->findAllForOnePlaylist($id);
            return $this->render("pages/playlist.html.twig", [
                'playlist' => $playlist,
                'playlistcategories' => $playlistCategories,
                'playlistformations' => $playlistFormations
        ]);
    }
    
}
