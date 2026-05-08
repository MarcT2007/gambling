<?php 
session_start(); 
// Ko pridemo na začetno stran, ponastavimo igro
if (isset($_SESSION['trenutna_runda'])) {
    unset($_SESSION['trenutna_runda']);
}
if (isset($_SESSION['skupni_rezultati'])) {
    unset($_SESSION['skupni_rezultati']);
}
if (isset($_SESSION['zgodovina_metov'])) {
    unset($_SESSION['zgodovina_metov']);
}

$path = $_SERVER['DOCUMENT_ROOT'] . "/common/header.php"; 
if (file_exists($path)) { include_once($path); }
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Piratska ladja</title>
    <style>
        body { background-image: url("background.png"); background-repeat:no-repeat; background-size:cover; color: #FFFFFF; font-family: 'Georgia', serif; text-align: center; }
        .form-container { border: 5px double #5d4037; padding: 20px; display: inline-block; margin-top: 200px; background: #3e2723; }
        input { margin: 5px; padding: 8px; background: #d4b483; border: 1px solid #5d4037; }
        button { background: #8d6e63; color: white; padding: 12px 25px; cursor: pointer; border: 1px solid #5d4037; }
    </style>
</head>
<body>
    <h1>⚓ Vpis piratov v bitko ⚓</h1>
    <div class="form-container">
        <form action="igra.php" method="POST">
            <?php for($i = 1; $i <= 3; $i++): ?>
                <h3>Pirat št. <?php echo $i; ?></h3>
                <input type="text" name="pirati[<?php echo $i; ?>][ime]" placeholder="Ime" required>
                <input type="text" name="pirati[<?php echo $i; ?>][priimek]" placeholder="Priimek" required>
                <input type="hidden" name="pirati[<?php echo $i; ?>][naslov]" value="Posadka <?php echo $i; ?>">
            <?php endfor; ?>
			<br/><br/>
            <button type="submit" name="zacni">Začni bitko!</button>
        </form>
    </div>
</body>
</html>