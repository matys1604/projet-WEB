<?php
//partie Matis 1/mai 
$statusFile = 'status.json';
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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil Parier sur l'Histoire</title>
    <style>
        body {
            background-image: url('romains-decadencef.JPG');
            background-size: cover;
            background-position: center;
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.7);
        }
        h1 {
            font-family: 'Trebuchet MS', 'Lucida Sans', Arial, sans-serif;
            font-size: 4em;
            margin-bottom: 40px;
            color: #FFD868;
            text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.7);
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .buttons {
            display: flex;
            gap: 20px;
        }
        button {
            padding: 15px 30px;
            font-size: 1.5em;
            background-color: gray;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button.selected {
            background-color: green;
        }
        button:hover {
            background-color: lightgray;
        }
    </style>
</head>
<body>
    <h1>C'est l'heure de parier sur l'histoire</h1>
    <div class="buttons">
        <button id="joueur1">Joueur 1</button>
        <button id="joueur2">Joueur 2</button>
    </div>
    <script src="js/script.js"></script>
</body>
</html>