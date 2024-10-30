<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HelloController extends AbstractController
{
    #[Route('/', name: 'app_accueil' )]

    public function hello(): Response{

        $hello = "Hello world";

        return $this->render('hello/accueil.html.twig', [
            'titre' => $hello,
            ]);
    }

    #[Route('/coucou')]

    public function coucou(): Response
    {

        $coucou = "Coucou world";

        return new Response(
            '<html><h1>' . $coucou . '</h1></html>'
        );
    }


}