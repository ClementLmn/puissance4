<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Player;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class GameController extends Controller
{
    /**
     * @Template()
     * @Route("/create", name="create")
     */
    public function index()
    {

        $players = $this->getDoctrine()
            ->getRepository(Player::class)
            ->findOtherPlayer($this->getUser());

        return [
            "players" => $players
        ];
    }

    /**
     * @Route("/createGame", name="create_game")
     */
    public function create(Request $request)
    {


        $entityManager = $this->getDoctrine()->getManager();

        $owner = $this->getUser();
        $player2id = $request->request->get('player2');
        $player2 = $this->getDoctrine()
            ->getRepository(Player::class)
            ->findOneById($player2id);

        $game = new Game();
        $game->setIsOver(false);
        $game->setPlayer1($owner);
        $game->setPlayer2($player2);
        $game->setGrid('{}');
        $game->setWhosTurn(true);
        $entityManager->persist($game);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'New game started !'
        );

        return $this->redirectToRoute('home');
    }
}
