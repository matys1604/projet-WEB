//partie a Matis le sang 
<?php
session_start();
$statusFile = 'status.json';
if (!file_exists($statusFile)) {
    $status = [
        'joueur1' => false,
        'joueur2' => false,
        'bets' => [],
        'validations' => []
    ];
    file_put_contents($statusFile, json_encode($status));
} else {
    $status = json_decode(file_get_contents($statusFile), true);
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['reset']) && $_POST['reset'] == 'true') {
        $questions = json_decode(file_get_contents('questions.json'), true);
        shuffle($questions);
        $selectedQuestions = array_slice($questions, 0, 5);
        $status = [
            'joueur1' => false,
            'joueur2' => false,
            'bets' => [],
            'validations' => [],
            'questions' => $selectedQuestions
        ];
        file_put_contents($statusFile, json_encode($status));
        echo json_encode(['status' => 'reset']);
        exit;
    }
    if (isset($_POST['joueur'])) {
        $joueur = $_POST['joueur'];
        if ($joueur == 'joueur1' || $joueur == 'joueur2') {
            $status[$joueur] = true;
            file_put_contents($statusFile, json_encode($status));
            echo json_encode(['status' => 'selected']);
            exit;
        }
    } // parti a Oumar 
    if (isset($_POST['question']) && isset($_POST['player']) && isset($_POST['bets'])) {
        $q = intval($_POST['question']);
        $player = $_POST['player'];
        $bets = json_decode($_POST['bets'], true);
        if (!isset($status['bets'][$q])) {
            $status['bets'][$q] = ['p1' => [0,0,0], 'p2' => [0,0,0]];
        }
        $status['bets'][$q][$player] = $bets;
        if (!isset($status['validations'][$q])) {
            $status['validations'][$q] = ['p1' => false, 'p2' => false];
        }
        if (isset($_POST['action']) && $_POST['action'] === 'validate') {
            $status['validations'][$q][$player] = true;
        }
        file_put_contents($statusFile, json_encode($status));
        echo json_encode(['status' => 'updated']);
        exit;
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['questions'])) {
        echo json_encode($status['questions'] ?? []);
    } else {
        echo json_encode($status);
    }
}
?>