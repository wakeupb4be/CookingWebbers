<?php
include 'db_config.php';
include 'header.php';

// 1. Recuperiamo l'ID
$id_ricetta = isset($_GET['id']) ? $_GET['id'] : 0;

// 2. PREPARED STATEMENT (Nuovo Metodo Sicuro)
// Prepariamo lo scheletro della query con il segnaposto ?
$stmt = $conn->prepare("SELECT * FROM ricette WHERE id = ?");

// Leghiamo il parametro: "i" sta per integer (intero). 
// Il database ora sa che $id_ricetta è SOLO un dato, non codice da eseguire.
$stmt->bind_param("i", $id_ricetta);

// Eseguiamo
$stmt->execute();

// Recuperiamo il risultato
$result = $stmt->get_result();
$ricetta = $result->fetch_assoc();
?>

<main style="padding: 100px 20px; max-width: 800px; margin: 0 auto;">
    
    <?php if ($ricetta): ?>
        <h1><?php echo $ricetta['titolo']; ?></h1>
        
        <div style="background: #eee; height: 300px; margin: 20px 0;">
             <img src="img/ricette/<?php echo htmlspecialchars($ricetta['img_profilo']); ?>" style="width:100%; height:100%; object-fit:cover;">
        </div>

        <p><strong>Difficoltà:</strong> <?php echo htmlspecialchars($ricetta['difficolta']); ?></p>
        <p><strong>Tempo:</strong> <?php echo htmlspecialchars($ricetta['tempo_preparazione']); ?> min</p>
        <p><strong>Categoria:</strong> <?php echo htmlspecialchars($ricetta['categoria']); ?></p>
        <hr>
        <h3>Procedimento</h3>
        <p><?php echo nl2br(htmlspecialchars($ricetta['descrizione_svolgimento'])); ?></p>

    <?php else: ?>
        <h1>Ricetta non trovata</h1>
        <a href="home.php">Torna alla Home</a>
    <?php endif; ?>

</main>

<?php include 'footer.php'; ?>