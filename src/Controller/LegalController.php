<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LegalController extends AbstractController
{
    #[Route('/cgu', name: 'cgu', methods: ['GET'])]
    public function cgu(): Response 
    {
        return $this->render('legal/cgu.html.twig');
    }
}

