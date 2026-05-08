<?php
session_start();

// 1. Shranjevanje ob prvem obisku
if (isset($_POST['zacni']) && isset($_POST['pirati'])) {
    $_SESSION['posadka'] = $_POST['pirati'];
    $_SESSION['trenutna_runda'] = 1;
    $_SESSION['skupni_rezultati'] = array_fill(1, count($_POST['pirati']), 0);
    $_SESSION['zgodovina_metov'] = [];
    $_SESSION['kdo_je_ze_metal'] = []; 
    header("Location: igra.php");
    exit();
}

if (!isset($_SESSION['posadka'])) {
    header("Location: index.php");
    exit();
}

// 2. Logika za posamezen met kocke
if (isset($_POST['vrzi_za_pirata_hidden'])) {
    $pirat_id = $_POST['pirat_id'];
    
    if (!in_array($pirat_id, $_SESSION['kdo_je_ze_metal'])) {
        $met = rand(1, 6);
        $_SESSION['zgodovina_metov'][$pirat_id][] = $met;
        $_SESSION['skupni_rezultati'][$pirat_id] += $met;
        $_SESSION['kdo_je_ze_metal'][] = $pirat_id;
    }

    if (count($_SESSION['kdo_je_ze_metal']) >= count($_SESSION['posadka'])) {
        $_SESSION['trenutna_runda']++;
        $_SESSION['kdo_je_ze_metal'] = []; 
    }

    header("Location: igra.php");
    exit();
}

$pirati = $_SESSION['posadka'];
$runda = $_SESSION['trenutna_runda'];
$rezultati = $_SESSION['skupni_rezultati'];
$metali_v_rundi = $_SESSION['kdo_je_ze_metal'];

// 3. Razvrščanje za stopničke
$razvrsceni_pirati = [];
if ($runda > 3) {
    foreach ($pirati as $id => $p) {
        $razvrsceni_pirati[] = [
            'id' => $id, 'ime' => $p['ime'], 'priimek' => $p['priimek'], 'tocke' => $rezultati[$id]
        ];
    }
    usort($razvrsceni_pirati, function($a, $b) {
        return $b['tocke'] <=> $a['tocke'];
    });
}
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>⚓ Piratska bitka ⚓</title>
    <style>
        body { background-color: #2c1e14; color: #d4b483; font-family: 'Georgia', serif; text-align: center; padding: 20px; margin: 0; overflow-x: auto; }
        
        /* NOVO: Ovijalec za igralce, da bodo v črti */
        .igralna-povrsina {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            flex-wrap: wrap; /* Če je preveč igralcev, skočijo v novo vrsto */
            gap: 20px;
            margin-top: 30px;
        }

        .pirat-kartica { 
            border: 1px solid #5d4037; 
            width: 300px; /* Malo ožje, da gredo lepše v črto */
            padding: 15px;
			/*margin-top:200px;*/
			margin:200px 15px 0px 15px;
            background: rgba(62, 39, 35, 0.9); 
            border-radius: 10px; 
            box-shadow: 0 4px 8px rgba(0,0,0,0.5); 
        }

        .dice-img { width: 60px; height: 60px; margin: 5px; vertical-align: middle; border-radius: 5px; }
        .mini-dice { width: 24px; vertical-align: middle; margin-left: 5px; margin-bottom: 2px; }
        
        .btn-posamezen-met { 
            background: #ffd700; color: #000; padding: 12px 25px; cursor: pointer; border: none; 
            font-weight: bold; border-radius: 5px; font-size: 1.1em; transition: 0.3s; width: 100%;
        }
        .btn-posamezen-met:hover:not(:disabled) { background: #fff; transform: scale(1.05); }

        /* Stopničke ostanejo enake */
        .stopnicke-container { display: flex; justify-content: center; align-items: flex-end; gap: 15px; margin-top: 200px; min-height: 400px; }
        .podij { width: 280px; padding: 20px; background: rgba(62, 39, 35, 0.9); border-radius: 10px; opacity: 0; animation: dvigPodija 1s ease-out forwards; }
        @keyframes dvigPodija { from { opacity: 0; transform: translateY(100px); } to { opacity: 1; transform: translateY(0); } }
        
        .mesto-1 { order: 2; border: 4px solid #ffd700; height: 380px; animation-delay: 1.2s; box-shadow: 0 0 20px rgba(255, 215, 0, 0.3); }
        .mesto-2 { order: 1; border: 3px solid #c0c0c0; height: 320px; animation-delay: 0.6s; }
        .mesto-3 { order: 3; border: 3px solid #cd7f32; height: 280px; animation-delay: 0.1s; }
        
        .skupaj { font-size: 1.5em; font-weight: bold; margin-top: 15px; }
        a.nova-igra { display: inline-block; margin-top: 40px; color: #ffd700; text-decoration: none; font-size: 1.6em; font-weight: bold; }
    </style>

    <script>
        function animirajMet(form, piratId) {
            const gumb = form.querySelector('button');
            const kontejnerMetov = document.getElementById('meti-' + piratId);
            gumb.disabled = true;
            gumb.innerText = "Vrtenje...";

            const rollImg = document.createElement('img');
            rollImg.src = 'http://193.2.139.22/dice/dice-anim.gif'; 
            rollImg.className = 'dice-img';
            kontejnerMetov.appendChild(rollImg);

            setTimeout(() => { form.submit(); }, 800);
            return false; 
        }

        window.onload = function() {
            const coinSound = document.getElementById('coinSound');
            if (coinSound) { setTimeout(() => { coinSound.play(); }, 200); }
        };
    </script>
</head>
<body>

    <h1>☠ <?php echo ($runda > 3) ? "Konec bitke" : "Runda: $runda / 3"; ?> ☠</h1>

    <?php if ($runda <= 3): ?>
        <!-- IGRALNA POVRŠINA V ČRTI -->
        <div class="igralna-povrsina">
            <?php foreach ($pirati as $id => $p): ?>
                <div class="pirat-kartica">
                    <h3><?php echo htmlspecialchars($p['ime'] . " " . $p['priimek']); ?></h3>
                    <div class="meti" id="meti-<?php echo $id; ?>">
                        <?php if (isset($_SESSION['zgodovina_metov'][$id])): ?>
                            <?php foreach ($_SESSION['zgodovina_metov'][$id] as $m): ?>
                                <img src="http://193.2.139.22/dice/<?php echo 'dice' . $m . '.gif'; ?>" class="dice-img">
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <p>Točk: <strong><?php echo $rezultati[$id]; ?></strong></p>
                    <form method="POST" onsubmit="return animirajMet(this, '<?php echo $id; ?>');">
                        <input type="hidden" name="pirat_id" value="<?php echo $id; ?>">
                        <input type="hidden" name="vrzi_za_pirata_hidden" value="1">
                        <button type="submit" class="btn-posamezen-met" <?php echo in_array($id, $metali_v_rundi) ? 'disabled' : ''; ?>>
                            <?php echo in_array($id, $metali_v_rundi) ? '✓ Čakam' : '🎲 Vrzi'; ?>
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

    <?php else: ?>
        <audio id="coinSound">
            <source src="https://www.soundjay.com/misc/sounds/coins-spilled-1.mp3" type="audio/mpeg">
        </audio>

        <div class="stopnicke-container">
            <?php foreach ($razvrsceni_pirati as $kljuc => $p): 
                $mesto = $kljuc + 1;
                if($mesto > 3) break; 
            ?>
                <div class="podij mesto-<?php echo $mesto; ?>">
                    <h2><?php echo $mesto; ?>. Mesto</h2>
                    <h3><?php echo htmlspecialchars($p['ime'] . " " . $p['priimek']); ?></h3>
                    <div class="zgodovina-izpis">
                        <?php 
                        foreach ($_SESSION['zgodovina_metov'][$p['id']] as $st => $met) {
                            $ime_datoteke = "dice" . $met . ".gif";
                            echo "Met " . ($st + 1) . ": <img src='http://193.2.139.22/dice/$ime_datoteke' class='mini-dice'><br>";
                        }
                        ?>
                    </div>
                    <div class="skupaj">Skupaj: <?php echo $p['tocke']; ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        <a href="index.php" class="nova-igra">⚓ Nova bitka ⚓</a>
    <?php endif; ?>

</body>
</html>