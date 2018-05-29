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
     * @Template()
     * @Route("/game/{gameId}", name="game_detail")
     */
    public function single($gameId)
    {
        $game = $this->getDoctrine()
            ->getRepository(Game::class)
            ->findOneBy(['id' => $gameId]);

        return [
            "game" => $game
        ];

    }

    /**
     * @Route("/newTurn", name="game_turn")
     */
    public function turn(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $user = $this->getUser();

        $col_choosed = $request->request->get('col');
        $game_id = $request->request->get('id');

        $game = $entityManager->getRepository(Game::class)->find($game_id);
        $grid = $game->getGrid();

        $grid_col = $grid[$col_choosed - 1];
        $added = false;
        $count = 0;
        for ($i = count($grid_col) - 1; $i >= 0; $i--){
            $count++;
            if($grid_col[$i] === "empty" && !$added){
                $grid[$col_choosed - 1][$i] = $user;
                $added = true;
            }
        }

        if($added){
            $game->setGrid($grid);
            $nextPlayer = $user === $game->getPlayer1() ? $game->getPlayer2() : $game->getPlayer1();
            $game->setWhosTurn($nextPlayer);
            $entityManager->persist($game);
            $entityManager->flush();


            $this->addFlash(
                'success',
                'Nice move !'
            );
        }else{
            $this->addFlash(
                'danger',
                'This column is full !'
            );
        }





        return $this->redirectToRoute('game_detail', array('gameId' => $game_id));

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
        $game->setWhosTurn($owner);
        $game->setPlayer2($player2);

        $grid = Array(
            0 => Array(
                0 => "empty",
                1 => "empty",
                2 => "empty",
                3 => "empty",
                4 => "empty",
                5 => "empty"
            ),
            1 => Array(
                0 => "empty",
                1 => "empty",
                2 => "empty",
                3 => "empty",
                4 => "empty",
                5 => "empty"
            ),
            2 => Array(
                0 => "empty",
                1 => "empty",
                2 => "empty",
                3 => "empty",
                4 => "empty",
                5 => "empty"
            ),
            3 => Array(
                0 => "empty",
                1 => "empty",
                2 => "empty",
                3 => "empty",
                4 => "empty",
                5 => "empty"
            ),
            4 => Array(
                0 => "empty",
                1 => "empty",
                2 => "empty",
                3 => "empty",
                4 => "empty",
                5 => "empty"
            ),
            5 => Array(
                0 => "empty",
                1 => "empty",
                2 => "empty",
                3 => "empty",
                4 => "empty",
                5 => "empty"
            ),
            6 => Array(
                0 => "empty",
                1 => "empty",
                2 => "empty",
                3 => "empty",
                4 => "empty",
                5 => "empty"
            ),
        );

        $game->setGrid($grid);
        $entityManager->persist($game);
        $entityManager->flush();

        $game_id = $game->getId();

        $this->addFlash(
            'success',
            'New game started !'
        );

        return $this->redirectToRoute('game_detail', array('gameId' => $game_id));
    }
}
