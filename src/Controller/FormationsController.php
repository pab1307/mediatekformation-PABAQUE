<?php
namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\FormationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controleur des formations
 *
 * @author emds
 */
class FormationsController extends AbstractController
{
    /**
     *
     * @var FormationRepository
     */
    private FormationRepository $formationRepository;

    /**
     *
     * @var CategorieRepository
     */
    private CategorieRepository $categorieRepository;

    public function __construct(
        FormationRepository $formationRepository,
        CategorieRepository $categorieRepository
    ) {
        $this->formationRepository = $formationRepository;
        $this->categorieRepository = $categorieRepository;
    }

    #[Route('/formations', name: 'formations')]
    public function index(): Response
    {
        $formations = $this->formationRepository->findAll();

        // on délègue le rendu à la méthode privée commune
        return $this->renderFormations($formations);
    }

    #[Route('/formations/tri/{champ}/{ordre}/{table}', name: 'formations.sort')]
    public function sort($champ, $ordre, $table = ""): Response
    {
        $formations = $this->formationRepository->findAllOrderBy($champ, $ordre, $table);

        // même vue, mêmes paramètres supplémentaires (ici pas de filtre, donc valeur = null)
        return $this->renderFormations($formations, null, $table);
    }

    #[Route('/formations/recherche/{champ}/{table}', name: 'formations.findallcontain')]
    public function findAllContain($champ, Request $request, $table = ""): Response
    {
        $valeur = $request->get("recherche");
        $formations = $this->formationRepository->findByContainValue($champ, $valeur, $table);

        // ici on passe aussi la valeur du filtre
        return $this->renderFormations($formations, $valeur, $table);
    }

    #[Route('/formations/formation/{id}', name: 'formations.showone')]
    public function showOne($id): Response
    {
        $formation = $this->formationRepository->find($id);
        return $this->render("pages/formation.html.twig", [
            'formation' => $formation,
        ]);
    }

    private function renderFormations(
        array $formations,
        ?string $valeur = null,
        ?string $table = null
    ): Response {
        $categories = $this->categorieRepository->findAll();

        return $this->render("pages/formations.html.twig", [
            'formations' => $formations,
            'categories' => $categories,
            'valeur'     => $valeur,
            'table'      => $table,
        ]);
    }
}

