<?php
/*
 * Created by PhpStorm.
 * User: Martin
 * Date: 8/4/2016
 *
 */
header('Content-Type: application/json;charset=utf-8');

// Control which errors show
error_reporting(E_ERROR);

// File where current game is to be stored
$filename = "tictac.txt";

// POST data
$token = $_POST['token'];
$command = $_POST['command'];
$text = $_POST['text'];
$user_id = $_POST['user_id'];
$user = $_POST['user_name'];

// Variables to create json
$msg = '';
$data = array();

/*
 * Representation of a tic-tac-toe board.
 */
class Board
{
    // N stands as empty, A as player 1, B as player 2
    public $cell = array(
        array('N', 'N', 'N'),
        array('N', 'N', 'N'),
        array('N', 'N', 'N'));

    // Used to check if board still has some spaces left
    public $spaces_left = 9;

    // Check whose turn it is
    public $playerA_turn = true;

    // Stores the players names
    public $playerA;
    public $playerB;

    /*
     * Returns true if game finishes, else returns false
     */
    function mark($player, $r, $c, &$msg)
    {
        // Convert row letters to row number.
        if ($r == 'A' or $r == 'a')
            $r = 0;
        elseif ($r == 'B' or $r == 'b')
            $r = 1;
        elseif ($r == 'C' or $r == 'c')
            $r = 2;

        // Convert column string to number
        $c = ((int)$c) - 1;

        // Check space and place mark.
        if ($this->cell[$r][$c] == 'N') {
            if ($player == $this->playerA and $this->playerA_turn) {
                $this->cell[$r][$c] = 'A';
                $this->spaces_left--;
            } elseif ($player == $this->playerB and !$this->playerA_turn) {
                $this->cell[$r][$c] = 'B';
                $this->spaces_left--;
            } else {
                $msg .= 'Please, wait for the current players to finish this game.'. "\n";
                $data['text'] = $msg;
                echo json_encode($data);
                exit();
            }
            $this->playerA_turn = !$this->playerA_turn;
            $this->display_board($msg);
            $result = $this->check_win();
            if ($result == 'A') {
                $msg .= $this->playerA . ' wins!'. "\n";
                return true;
            } elseif ($result == 'B') {
                $msg .= $this->playerB . ' wins!'. "\n";
                return true;
            }
            if ($this->spaces_left == 0) {
                $msg .= 'Game ends in a draw.' . "\n";
                return true;
            }
        } else {
            $msg .= "Please, choose an unmarked space to place your mark.\n";
            $data['text'] = $msg;
            echo json_encode($data);
            exit();
        }
        return false;
    }

    function display_board(&$msg)
    {
        $msg .= '--  1   2   3' . "\n";
        for ($r = 0; $r < 3; $r++) {
            // Assing a row letter
            $l = '';
            if ($r == 0) $l = 'a   ';
            if ($r == 1) $l = 'b   ';
            if ($r == 2) $l = 'c   ';

            $msg .= $l;
            for ($c = 0; $c < 3; $c++) {
                if ($this->cell[$r][$c] == 'N')
                    $msg .= '    ';
                elseif ($this->cell[$r]{$c} == 'A')
                    $msg .= 'X';
                else
                    $msg .= 'O';
                if ($c != 2)
                    $msg .= '|';
                else
                    $msg .= "\n";
            }
            if ($r != 2)
                $msg .= '    *----------*' . "\n";
        }
        $msg .= "\n";
        if ($this->playerA_turn)
            $msg .= "It's $this->playerA's turn.\n";
        else
            $msg .= "It's $this->playerB's turn.\n";
        $msg .= 'Type /tictac (row) (col) to place your marker!' . "\n";
    }

    function check_win()
    {
        // If any 0 reaches 3 or -3, someone has won the game.
        $row[] = array(0, 0, 0);
        $col[] = array(0, 0, 0);
        $d1 = $d2 = 0;

        // Go every each cell and add or subtract accordingly.
        for ($r = 0; $r < 3; $r++) {
            for ($c = 0; $c < 3; $c++) {
                if ($this->cell[$r][$c] == 'A') {
                    $row[$r]++;
                    $col[$c]++;
                    // If part of upper-left to lower-right diagonal
                    if ($r == $c) {
                        $d1++;
                        // If right in the middle, increase other diagonal
                        if ($r == 1 and $c == 1)
                            $d2++;
                    } elseif (($r == 0 and $c == 2) or ($r == 2 and $c == 0)) {
                        $d2++;
                    }
                } elseif ($this->cell[$r][$c] == 'B') {
                    $row[$r]--;
                    $col[$c]--;
                    // If part of upper-left to lower-right diagonal
                    if ($r == $c) {
                        $d1--;
                        // If right in the middle, increase other diagonal
                        if ($r == 1 and $c == 2)
                            $d2--;
                    } elseif (($r == 0 and $c == 2) or ($r == 2 and $c == 0)) {
                        $d2--;
                    }
                }
            }
        } // End of for
        // If any condition meets 3, return the letter corresponding to the winner.
        for ($r = 0; $r < 3; $r++) {
            if (abs($row[$r]) == 3)
                return $this->cell[$r][0];
        }
        for ($c = 0; $c < 3; $c++) {
            if (abs($col[$c]) == 3)
                return $this->cell[0][$c];
        }
        if (abs($d1) == 3 or abs($d2) == 3)
            return $this->cell[1][1];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($token == 'QIdZLSlajZgUOL5ixnwlxOj1') {
        $commands = explode(" ", $text);
        if (sizeof($commands) == 1) {
            $playerA;
            $playerB;
            $board;
            if ($file = fopen($filename, "r+")) {
                if (($playerA = trim(fgets($file))) == false) { // If nothing is written
                    fwrite($file, ($user . "\n"));
                    $msg .= $user . ' has joined a tic-tac-toe game.' . "\n" .
                        'Type /tictac to challenge ' . $user . "\n";
                    fclose($file);
                    $data['response_type'] = 'in_channel';
                    $data['text'] = $msg;
                    echo json_encode($data);
                    exit();
                }
                if (($playerB = trim(fgets($file))) == false) {
                    if ($user == $playerA) {
                        $msg .= 'You cannot play against yourself!'. "\n";
                        fclose($file);
                        $data['text'] = $msg;
                        echo json_encode($data);
                        exit();
                    }
                    $playerB = $user;
                    fwrite($file, ($user . "\n"));
                    $msg .= $playerB . ' has joined a tic-tac-toe game against '
                        . $playerA . "\n";
                }
                if (($board = fgets($file)) == false) {
                    $board = new Board();
                    $msg .= 'Board initialized'. "\n";
                    $board->playerA = $playerA;
                    $board->playerB = $playerB;
                    $board->display_board($msg);
                    fwrite($file, serialize($board));
                    fclose($file);
                }
                elseif ($commands[0] == 'score') {
                    $board = unserialize($board);
                    $board->display_board($msg);
                    fclose($file);
                }
                $data['response_type'] = 'in_channel';
                $data['text'] = $msg;
                echo json_encode($data);
                exit();
            }
        } elseif (sizeof($commands) == 2) {
            $playerA;
            $playerB;
            $board;
            if ($file = fopen($filename, "r")) {
                if (($playerA = trim(fgets($file))) == false) { // If nothing is written
                    $msg .= 'Please, join a game first.'. "\n";
                    fclose($file);
                    $data['text'] = $msg;
                    echo json_encode($data);
                    exit();
                }
                if (($playerB = trim(fgets($file))) == false) {
                    $msg .= 'Please, join the current game first.'. "\n";
                    fclose($file);
                    $data['text'] = $msg;
                    echo json_encode($data);
                    exit();
                }
                if (($board = fgets($file)) == false) {
                    $msg .= 'Error: Board should be initialized'. "\n";
                    fclose($file);
                }
                $board = unserialize($board);
                fclose($file);
            }
            $r = $board->mark($user, $commands[0], $commands[1], $msg);
            if ($r) { // If games ends.
                $file = fopen($filename, "w");
                file_put_contents($file, "");
                fclose($file);
            } else {
                $file = fopen($filename, "w");
                file_put_contents($file, "");
                fwrite($file, ($playerA . "\n"));
                fwrite($file, ($playerB . "\n"));
                fwrite($file, serialize($board));
                fclose($file);
            }
            $data['response_type'] = 'in_channel';
            $data['text'] = $msg;
            echo json_encode($data);
            exit();
        } else {
            $msg .= 'Invalid command. Please use:' .
                '/tictac' .
                '/tictac row(letter) column(number)'. "\n";
        }

    } else {
        $msg .= 'Token does not match'. "\n";
    }
    $data['text'] = $msg;
    echo json_encode($data);
    exit();
}