document.addEventListener('DOMContentLoaded', function() {
    const btn1 = document.getElementById('joueur1');
    const btn2 = document.getElementById('joueur2');
    let navigatingToGame = false;

    btn1.addEventListener('click', function() {
        selectJoueur('joueur1');
        btn1.classList.add('selected');
        localStorage.setItem('player', '1');
    });

    btn2.addEventListener('click', function() {
        selectJoueur('joueur2');
        btn2.classList.add('selected');
        localStorage.setItem('player', '2');
    });

    function selectJoueur(joueur) {
        fetch('status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'joueur=' + joueur
        })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            checkStatus();
        });
    }

    function goToGame() {
        if (!navigatingToGame) {
            navigatingToGame = true;
            window.location.href = 'jeu.php';
        }
    }

    function updateButtons(data) {
        if (data.joueur1) {
            btn1.classList.add('selected');
        }
        if (data.joueur2) {
            btn2.classList.add('selected');
        }
        if (data.joueur1 && data.joueur2) {
            goToGame();
        }
    }

    function checkStatus() {
        fetch('status.php', { cache: 'no-store' })
        .then(response => response.json())
        .then(data => {
            updateButtons(data);
        });
    }

    checkStatus();
    setInterval(checkStatus, 1000);

    window.addEventListener('beforeunload', function() {
        if (!navigatingToGame) {
            const data = new FormData();
            data.append('reset', 'true');
            navigator.sendBeacon('status.php', data);
        }
    });
});