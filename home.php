<?php
include 'db_config.php';

// Query aggiornata:
// 1. SELECT: Selezioniamo tutto delle ricette (r.*) E calcoliamo la media dei voti (AVG) chiamandola "media_voti"
// 2. LEFT JOIN: "Attacchiamo" la tabella voti (v) alla tabella ricette (r) dove gli ID corrispondono
// 3. GROUP BY: Raggruppiamo i risultati per ricetta (altrimenti avremmo una riga per ogni singolo voto)
// 4. ORDER BY: Ordiniamo in base alla media calcolata, dal più alto al più basso (DESC)
$sql = "SELECT ricette.*, AVG(voti.voto) as media_voti 
        FROM ricette 
        LEFT JOIN voti ON ricette.id = voti.id_ricetta 
        GROUP BY ricette.id 
        ORDER BY media_voti DESC 
        LIMIT 4";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="it">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COOKING WEBBERS</title>

    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>

    <?php include 'header.php'; ?>
    
    <video autoplay muted loop id="bg-video">
        <source src="video/background2.mp4" type="video/mp4">
        Il tuo browser non supporta il video tag.
    </video>

    <main>
        <div class="video-spacer"></div>

        <div class="content-wrapper">
            
            <section class="recipes-grid section-band">
                <h2>Le più votate</h2>
                <div class="grid-container">
                    <div class="grid-container">
    <?php
    // Controlliamo se ci sono risultati dalla query fatta a riga 15
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            
            // Arrotondiamo la media voti (es. 4.2 diventa 4) per le stelline
            $voto_intero = round($row['media_voti']); 
            
            // Gestione immagine: se nel db c'è scritto "carbonara.jpg", il percorso sarà "img/ricette/carbonara.jpg"
            // Se manca, mettiamo un placeholder
            $img = !empty($row['img_profilo']) ? "img/ricette/" . $row['img_profilo'] : "img/logo.png";
            ?>

            <a href="ricetta.php?id=<?php echo $row['id']; ?>" class="recipe-card">
                
                <div class="card-image">
                    <img src="<?php echo $img; ?>" alt="<?php echo $row['titolo']; ?>">
                </div>

                <div class="card-content">
                    <h3><?php echo $row['titolo']; ?></h3>
                    
                    <div class="stars">
                        <?php
                        for ($i = 1; $i <= 5; $i++) {
                            // Se l'indice è minore o uguale alla media, stella piena (fas), altrimenti vuota (far)
                            echo ($i <= $voto_intero) ? '<i class="fas fa-star filled"></i>' : '<i class="far fa-star"></i>';
                        }
                        ?>
                        <span class="rating-num">(<?php echo number_format($row['media_voti'], 1); ?>)</span>
                    </div>
                </div>
            </a>

            <?php
        }
    } else {
        echo "<p style='text-align:center; width:100%;'>Nessuna ricetta trovata nel database.</p>";
    }
    ?>
</div>
                </div>
                <div class="cta-container">
                <a href="ricerca.php" class="btn-all-recipes">Tutte le ricette</a>
            </div>
            </section>

            

        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>