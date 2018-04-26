<?php

namespace App\Controller;

use App\Entity\Game;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller
{
    /**
     * @Template()
     * @Route("/", name="home")
     */
    public function index()
    {

        $games = $this->getDoctrine()
            ->getRepository(Game::class)
            ->findAll();

        return [
            'games' => $games,
        ];
    }
}
