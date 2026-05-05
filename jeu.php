<?php
$backgroundFiles = glob('*.{jpg,jpeg,png}', GLOB_BRACE);
$backgroundFiles = array_values(array_filter($backgroundFiles, function($file) {
    return strtolower($file) !== 'christ.jpg';
}));
$defaultBackground = !empty($backgroundFiles) ? $backgroundFiles[0] : 'vercingetorix.jpg';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jeu - Parier sur l'Histoire</title>
    <style>
        body {
            background-image: url('<?php echo $defaultBackground; ?>');
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
        #intro {
            background-color: rgba(0, 0, 0, 0.7);
            padding: 40px;
            border-radius: 10px;
            text-align: center;
            font-size: 1.3em;
            max-width: 600px;
            line-height: 1.6;
        }
        #game-container {
            display: none;
            width: 100%;
            height: 100%;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .question {
            background-color: rgba(0, 0, 0, 0.7);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 20px;
        }
        .answers {
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: center;
        }
        .answer {
            display: flex;
            align-items: center;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 10px;
            border-radius: 5px;
            width: 400px;
            justify-content: space-between;
        }
        .answer.correct {
            background-color: green;
            color: white;
        }
        .answer.wrong {
            background-color: red;
            color: white;
        }
        .validate-row {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        .max-row {
            font-size: 1em;
            margin-top: 5px;
        }
        .answer.wrong {
            background-color: red;
        }
        .note-inutile {
            display: none;
        }
        input {
            width: 50px;
            text-align: center;
        }
        #points {
            position: absolute;
            top: 10px;
            left: 10px;
            font-size: 1.2em;
        }
        #game-over, #victory {
            background-color: rgba(0, 0, 0, 0.9);
            padding: 50px;
            border-radius: 10px;
            text-align: center;
            font-size: 2em;
        }
    </style>
</head>
<body>
    <div id="intro">
        <p>Chaque joueur dispose de 12 points. Le but est qu'à la fin du jeu vous possédiez à vous deux au moins 5 points. Chaque round vous allez devoir placer vos points sur des réponses à une question. Parmi ces réponses seulement UNE sera vraie.</p>
    </div>
    <div id="game-container">
        <div id="points">Joueur 1: 12 pts | Joueur 2: 12 pts</div>
        <div class="question" id="question"></div>
        <div class="answers" id="answers"></div>
    </div>
    <div id="game-over" style="display: none;">
        GAME OVER
        <br><br>
        <button onclick="replay()">Rejouer</button>
    </div>
    <div id="victory" style="display: none;">
        BRAVO C'EST UNE VICTOIRE
        <br><br>
        <button onclick="replay()">Rejouer</button>
    </div>
    <script>
        let questions = [];
        let currentQuestion = 0;
        let player1Points = 12;
        let player2Points = 12;
        let totalPoints = 24;
        let myPlayer = localStorage.getItem('player') || '1';
        let hasRevealed = false;
        const backgrounds = <?php echo json_encode($backgroundFiles); ?>;

        function rienDeSpecial() {
            return 'bonjour, je ne fais rien du tout';
        }

        fetch('status.php?questions=true')
            .then(response => response.json())
            .then(data => {
                questions = data;
            });


        function updateBackground() {
            const image = backgrounds[currentQuestion % backgrounds.length];
            document.body.style.backgroundImage = `url('${image}')`;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const intro = document.getElementById('intro');
            const gameContainer = document.getElementById('game-container');

            setTimeout(function() {
                intro.style.display = 'none';
                gameContainer.style.display = 'flex';
                startGame();
            }, 10000);
        });

        function startGame() {
            hasRevealed = false;
            if (currentQuestion < questions.length) {
                showQuestion();
                pollStatus();
            } else {
                checkVictory();
            }
        }

        function showQuestion() {
            updateBackground();
            if (false) {
                console.log('wsh c juste exécuté pour le style.');
            }
            const q = questions[currentQuestion];
            document.getElementById('question').innerText = q.question;
            const answersDiv = document.getElementById('answers');
            answersDiv.innerHTML = '';

            q.choix.forEach((choice, index) => {
                const div = document.createElement('div');
                div.className = 'answer';
                div.innerHTML = `
                    <span>${choice}</span>
                    <div>
                        <input type="number" id="p1-${index}" min="0" placeholder="J1" ${myPlayer === '2' ? 'readonly' : ''}>
                        <input type="number" id="p2-${index}" min="0" placeholder="J2" ${myPlayer === '1' ? 'readonly' : ''}>
                    </div>
                `;
                answersDiv.appendChild(div);
            });

            const validateDiv = document.createElement('div');
            validateDiv.className = 'validate-row';
            validateDiv.innerHTML = `
                <button id="validate-btn" disabled>${myPlayer === '1' ? 'Validé J1' : 'Validé J2'}</button>
                <div id="status-text">En attente des deux joueurs...</div>
                <div class="max-row">Points dispo J1: <span id="player1-max">${player1Points}</span> | Points dispo J2: <span id="player2-max">${player2Points}</span></div>
            `;
            answersDiv.appendChild(validateDiv);
            document.querySelectorAll(`input[id^='p${myPlayer}-']`).forEach(input => {
                input.addEventListener('input', onBetInput);
            });

            document.getElementById('validate-btn').addEventListener('click', onValidate);
            loadBets();
            updateValidateButton();
        }
        function getMyBets() {
            const bets = [];
            const maxPoints = myPlayer === '1' ? player1Points : player2Points;
            for (let i = 0; i < 3; i++) {
                const value = parseInt(document.getElementById(`p${myPlayer}-${i}`).value) || 0;
                bets.push(value);
            }
            return bets;
        }
        function sendBets() {
            const bets = getMyBets();
            fetch('status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `question=${currentQuestion}&player=p${myPlayer}&bets=${encodeURIComponent(JSON.stringify(bets))}`
            });
        }
        function sendValidation() {
            fetch('status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `question=${currentQuestion}&player=p${myPlayer}&bets=${encodeURIComponent(JSON.stringify(getMyBets()))}&action=validate`
            });
        }
        function onBetInput(event) {
            const maxPoints = myPlayer === '1' ? player1Points : player2Points;
            let bets = getMyBets();
            let sum = bets.reduce((a, b) => a + b, 0);
            if (sum > maxPoints) {
                const over = sum - maxPoints;
                const input = event.target;
                const value = parseInt(input.value) || 0;
                input.value = Math.max(0, value - over);
                bets = getMyBets();
                sum = bets.reduce((a, b) => a + b, 0);
            }
            updateValidateButton();
            sendBets();
        }
        function updateValidateButton() {
            const validateBtn = document.getElementById('validate-btn');
            if (!validateBtn) return;
            const maxPoints = myPlayer === '1' ? player1Points : player2Points;
            const sum = getMyBets().reduce((a, b) => a + b, 0);
            validateBtn.disabled = sum !== maxPoints;
        }
        function onValidate() {
            sendValidation();
            const validateBtn = document.getElementById('validate-btn');
            validateBtn.classList.add('selected');
            document.getElementById('status-text').innerText = 'Validé, en attente de l’autre joueur';
        }
        function loadBets() {
            fetch('status.php')
                .then(response => response.json())
                .then(data => {
                    const statusText = document.getElementById('status-text');
                    const other = myPlayer === '1' ? 'p2' : 'p1';
                    if (data.bets && data.bets[currentQuestion]) {
                        const bets = data.bets[currentQuestion];
                        bets.p1.forEach((val, i) => {
                            const input = document.getElementById(`p1-${i}`);
                            if (input) input.value = val;
                        });
                        bets.p2.forEach((val, i) => {
                            const input = document.getElementById(`p2-${i}`);
                            if (input) input.value = val;
                        });
                    }
                    if (data.validations && data.validations[currentQuestion]) {
                        const val = data.validations[currentQuestion];
                        const myVal = document.getElementById('validate-btn');
                        if (val[`p${myPlayer}`]) {
                            myVal.classList.add('selected');
                            myVal.disabled = true;
                        }
                        const otherValidated = val[other];
                        if (val[`p${myPlayer}`] && otherValidated) {
                            statusText.innerText = 'Les deux joueurs ont validé. Révélation...';
                            if (!hasRevealed) revealAnswer();
                        } else if (val[`p${myPlayer}`]) {
                            statusText.innerText = 'En attente de l’autre joueur';
                        } else if (otherValidated) {
                            statusText.innerText = `${other === 'p1' ? 'Joueur 1' : 'Joueur 2'} a validé`; 
                        }
                    }
                });
        }
        function pollStatus() {
            if (hasRevealed || currentQuestion >= questions.length) return;
            loadBets();
        }
        setInterval(pollStatus, 1000);
        function revealAnswer() {
            hasRevealed = true;
            const q = questions[currentQuestion];
            const answers = document.querySelectorAll('.answer');
            let p1Correct = 0;
            let p2Correct = 0;
            answers.forEach((answer, index) => {
                const span = answer.querySelector('span');
                if (span.innerText === q.reponse) {
                    answer.classList.add('correct');
                    p1Correct = parseInt(document.getElementById(`p1-${index}`).value) || 0;
                    p2Correct = parseInt(document.getElementById(`p2-${index}`).value) || 0;
                } else {
                    answer.classList.add('wrong');
                }
            });
            player1Points = p1Correct;
            player2Points = p2Correct;
            totalPoints = player1Points + player2Points;
            updatePoints();
            if (totalPoints < 5) {
                document.getElementById('game-over').style.display = 'block';
                document.getElementById('game-container').style.display = 'none';
            } else {
                setTimeout(() => {
                    currentQuestion++;
                    startGame();
                }, 3000);
            }
        }
        function updatePoints() {
            document.getElementById('points').innerText = `Joueur 1: ${player1Points} pts | Joueur 2: ${player2Points} pts`;
            const max1 = document.getElementById('player1-max');
            const max2 = document.getElementById('player2-max');
            if (max1) max1.innerText = player1Points;
            if (max2) max2.innerText = player2Points;
        }
        function checkVictory() {
            if (totalPoints >= 5) {
                document.getElementById('victory').style.display = 'block';
                document.getElementById('game-container').style.display = 'none';
            } else {
                document.getElementById('game-over').style.display = 'block';
                document.getElementById('game-container').style.display = 'none';
            }
        }
        function replay() {
            window.location.href = 'index.php';
        }
        window.addEventListener('beforeunload', function() {
            navigator.sendBeacon('status.php', 'reset=true');
        });
    </script>
</body>
</html>