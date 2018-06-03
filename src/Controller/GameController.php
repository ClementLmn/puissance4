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
    public function turn(Request $request, \Swift_Mailer $mailer)
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

            $win = $this->isWin($grid);

            if($win){
                $winnerPlayer = $this->getDoctrine()
                    ->getRepository(Player::class)->findOneBy(['id' => $win->getId()]);
                $game->setWinner($winnerPlayer);
                $game->setIsOver(true);
                $looser = $win == $game->getPlayer1() ? $game->getPlayer2() : $game->getPlayer1();

                $messageWin = (new \Swift_Message('You won '.$winnerPlayer->getPseudo(). ' !'))
                    ->setFrom('send@example.com')
                    ->setTo($winnerPlayer->getMail())
                    ->setBody(
                        $this->renderView(
                            'emails/win.html.twig',
                            array(
                                'winner' => $winnerPlayer,
                                'looser' => $looser,
                            )
                        ),
                        'text/html'
                    )
                ;

                $messageLooser = (new \Swift_Message('You loose '.$looser->getPseudo(). ' ...'))
                    ->setFrom('send@example.com')
                    ->setTo($looser->getMail())
                    ->setBody(
                        $this->renderView(
                            'emails/loose.html.twig',
                            array(
                                'winner' => $winnerPlayer,
                                'looser' => $looser,
                            )
                        ),
                        'text/html'
                    )
                ;

                $mailer->send($messageWin);

                $mailer->send($messageLooser);

            }


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

    public function isWin($grid)
    {
        $win = null;
        for ($i = 0; $i < count($grid); $i++){

            for ($w = 0; $w < 3; $w++){
                if($win === null && $grid[$i][$w] !== "empty" && $grid[$i][$w+1] !== "empty" && $grid[$i][$w]->getId() === $grid[$i][$w+1]->getId() && $grid[$i][$w+2] !== "empty" && $grid[$i][$w]->getId() === $grid[$i][$w+2]->getId() && $grid[$i][$w+3] !== "empty" && $grid[$i][$w]->getId() === $grid[$i][$w+3]->getId()){
                    //$win = "Horizontal ! Column " . $i .", Row ". $w. ", player " . $grid[$i][$w]->getPseudo();
                    $win = $grid[$i][$w];
                }
            }
        }

        for ($k = 0; $k < 3; $k++){
            for ($l = 0; $l < 2; $l++){
                if($win === null && $grid[$k][$l] !== "empty" && $grid[$k+1][$l+1] !== "empty" && $grid[$k+2][$l+2] !== "empty" && $grid[$k+3][$l+3]!== "empty" && $grid[$k][$l]->getId() === $grid[$k+1][$l+1]->getId() && $grid[$k][$l]->getId() === $grid[$k+2][$l+2]->getId() && $grid[$k][$l]->getId() === $grid[$k+3][$l+3]->getId()){
                    $win = $grid[$k][$l];
                }
                if($win === null && $grid[$k][5 - $l] !== "empty" && $grid[$k+1][5 - $l-1] !== "empty" && $grid[$k+2][5 - $l-1] !== "empty" && $grid[$k+3][5 - $l-3]!== "empty" && $grid[$k][5 - $l]->getId() === $grid[$k+1][5 - $l-1]->getId() && $grid[$k][5 - $l]->getId() === $grid[$k+2][5 - $l-2]->getId() && $grid[$k][5 - $l]->getId() === $grid[$k+3][5 - $l-3]->getId()){
                    $win = $grid[$k][5 - $l];
                }
            }
        }



        for($j = 0; $j < count($grid[0]); $j++){

            for ($y = 0; $y < 4; $y++){
                if($win === null && $grid[$y][$j] !== "empty" && $grid[$y+1][$j] !== "empty" && $grid[$y+2][$j] !== "empty" && $grid[$y+3][$j]!== "empty" && $grid[$y][$j]->getId() === $grid[$y+1][$j]->getId() && $grid[$y][$j]->getId() === $grid[$y+2][$j]->getId() && $grid[$y][$j]->getId() === $grid[$y+3][$j]->getId()){
                    $win = $grid[$y][$j];
                    //$win = "Vertical ! Column " . $y .", Row ". $j. ", player " . $grid[$y][$j]->getPseudo();
                }
            }
        }

        return $win;
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
