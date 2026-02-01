<?php
include 'db_config.php';
include 'header.php';

// Recuperiamo la categoria se presente nell'URL
// Se l'URL è ricerca.php?cat=Primo, allora $cat_selezionata sarà 'Primo'
// Altrimenti sarà una stringa vuota.
$cat_selezionata = isset($_GET['cat']) ? $_GET['cat'] : '';

// Recuperiamo la ricerca se presente
$search_term = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';

// Ordinamento (default: alfabetico)
$ordine = isset($_GET['order']) ? $_GET['order'] : 'az';


// Definiamo la base della query
// Selezioniamo i dati della ricetta E calcoliamo la media dei voti
$query = "SELECT ricette.*, AVG(voti.voto) as media_voti 
          FROM ricette 
          LEFT JOIN voti ON ricette.id = voti.id_ricetta 
          WHERE 1=1";
$params = [];
$types = "";

// Se è stata scelta una categoria, aggiungiamo il filtro
if ($cat_selezionata != '') {
    $query .= " AND categoria = ?";
    $params[] = $cat_selezionata;
    $types .= "s"; 
}

// Se è stata scritta una parola nella barra di ricerca, aggiungiamo il filtro
if ($search_term != '') {
    $query .= " AND titolo LIKE ?";
    $params[] = "%" . $search_term . "%";
    $types .= "s";
}

// Filtro Difficoltà (esatto)
if (isset($_GET['diff']) && $_GET['diff'] != '') {
    $query .= " AND difficolta = ?";
    $params[] = $_GET['diff'];
    $types .= "s"; // "s" perché è una stringa 
}

// Filtro Tempo di preparazione (minore o uguale)
if (isset($_GET['tempo']) && $_GET['tempo'] != '') {
    $tempo_max = intval($_GET['tempo']);
    if ($tempo_max <= 90) {
        $query .= " AND tempo_preparazione <= ?";
        $params[] = $tempo_max;
        $types .= "i"; // "i" sta per integer
    } else {
        // Se l'utente ha scelto "Oltre 90 min" (valore 91 nel tuo HTML)
        $query .= " AND tempo_preparazione > 90";
    }
}


//Filtro ingredienti esclusivo (se seleziono 2 ingredienti mostra solo ricette che li contengono entrambi)
// Logica "AND": La ricetta deve avere TUTTI gli ingredienti selezionati
if (isset($_GET['ingrediente']) && !empty($_GET['ingrediente'])) {
    foreach ($_GET['ingrediente'] as $id_ingrediente) {
        // Per OGNI ingrediente selezionato, aggiungiamo una condizione "EXISTS"
        // Traduzione: "E deve esistere una riga nella tabella collegamenti
        // che unisce questa ricetta a QUESTO specifico ingrediente"
        $query .= " AND EXISTS (
            SELECT 1 FROM ricette_ingredienti 
            WHERE ricette_ingredienti.id_ricetta = ricette.id 
            AND ricette_ingredienti.id_ingrediente = ?
        )";
        
        $params[] = $id_ingrediente;
        $types .= "i"; // 'i' perché l'ID è un numero intero
    }
}

// Raggruppamento
$query .= " GROUP BY ricette.id";

//Filtro Voto minimo
if (isset($_GET['voto']) && $_GET['voto'] != '') {
    // Usiamo HAVING perché stiamo filtrando su un valore calcolato (la media)
    $query .= " HAVING media_voti >= ?";
    $params[] = $_GET['voto'];
    $types .= "i";
}

// 6. ORDINAMENTO
switch ($ordine) {
    case 'voto':
        $query .= " ORDER BY media_voti DESC";
        break;
    case 'tempo_asc':
        $query .= " ORDER BY tempo_preparazione ASC";
        break;
    case 'az':
        $query .= " ORDER BY titolo ASC";
        break;
    case 'recenti':
    default:
        $query .= " ORDER BY data_creazione DESC"; // Assumendo tu abbia questa colonna, altrimenti usa ID DESC
        break;
}

// --- ESECUZIONE DELLA QUERY ---
$stmt = $conn->prepare($query);

// Se ci sono parametri, li leghiamo dinamicamente
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result(); // Qui otteniamo finalmente i dati delle ricette filtrate

// --- Recuperiamo la lista degli ingredienti dal DB per il filtro ---
$sql_ingredienti = "SELECT * FROM ingredienti ORDER BY nome ASC";
$result_ingredienti = $conn->query($sql_ingredienti);
// -----------------------------------------------------------------------
?>

<link rel="stylesheet" href="css/ricerca.css">

<div class="search-page-container">

    <aside class="filters-sidebar">
        <form action="ricerca.php" method="GET">
            <h3>Filtra Ricette</h3>

            <div>
                <label for="order">Ordina per:</label>
                <select name="order">
                    <option value="recenti" <?php if($ordine == 'recenti') echo 'selected'; ?>>
                        Più recenti
                    </option>

                    <option value="voto" <?php if($ordine == 'voto') echo 'selected'; ?>>
                        Voto migliore
                    </option>

                    <option value="tempo_asc" <?php if($ordine == 'tempo_asc') echo 'selected'; ?>>
                        Più veloci
                    </option>

                    <option value="az" <?php if($ordine == 'az') echo 'selected'; ?>>
                        Alfabetico (A-Z)
                    </option>
                </select>
            </div>
            

            <div class="filter-group">
                <label>Categoria</label>
                <select name="cat">
                    <option value="">Tutte</option>
    
                    <option value="Antipasto" <?php if($cat_selezionata == 'Antipasto') echo 'selected'; ?>>
                        Antipasti
                    </option>
    
                    <option value="Primo" <?php if($cat_selezionata == 'Primo') echo 'selected'; ?>>
                        Primi
                    </option>
    
                    <option value="Secondo" <?php if($cat_selezionata == 'Secondo') echo 'selected'; ?>>
                        Secondi
                    </option>
    
                    <option value="Dolce" <?php if($cat_selezionata == 'Dolce') echo 'selected'; ?>>
                        Dolci
                    </option>
                </select>
            </div>

            <div class="filter-group">
                <label>Difficoltà</label>
                <select name="diff">
                    <option value="">Qualsiasi</option>
                    <option value="Facile" <?php if(isset($_GET['diff']) && $_GET['diff'] == 'Facile') echo 'selected'; ?>>Facile</option>
                    <option value="Normale" <?php if(isset($_GET['diff']) && $_GET['diff'] == 'Normale') echo 'selected'; ?>>Normale</option>
                    <option value="Difficile" <?php if(isset($_GET['diff']) && $_GET['diff'] == 'Difficile') echo 'selected'; ?>>Difficile</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Tempo di preparazione</label>
                <select name="tempo">
                    <option value="">Qualsiasi</option>
                    <option value="20" <?php if(isset($_GET['tempo']) && $_GET['tempo'] == '20') echo 'selected'; ?>>Fino a 20 min</option>
                    <option value="40" <?php if(isset($_GET['tempo']) && $_GET['tempo'] == '40') echo 'selected'; ?>>Fino a 40 min</option>
                    <option value="60" <?php if(isset($_GET['tempo']) && $_GET['tempo'] == '60') echo 'selected'; ?>>Fino a 60 min</option>
                    <option value="90" <?php if(isset($_GET['tempo']) && $_GET['tempo'] == '90') echo 'selected'; ?>>Fino a 90 min</option>
                    <option value="91" <?php if(isset($_GET['tempo']) && $_GET['tempo'] == '91') echo 'selected'; ?>>Oltre 90 min</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Voto minimo</label>
                <select name="voto">
                    <option value="">Qualsiasi</option>
                    <option value="1" <?php if(isset($_GET['voto']) && $_GET['voto'] == '1') echo 'selected'; ?>>1+ Stelle</option>
                    <option value="2" <?php if(isset($_GET['voto']) && $_GET['voto'] == '2') echo 'selected'; ?>>2+ Stelle</option>
                    <option value="3" <?php if(isset($_GET['voto']) && $_GET['voto'] == '3') echo 'selected'; ?>>3+ Stelle</option>
                    <option value="4" <?php if(isset($_GET['voto']) && $_GET['voto'] == '4') echo 'selected'; ?>>4+ Stelle</option>
                    <option value="5" <?php if(isset($_GET['voto']) && $_GET['voto'] == '5') echo 'selected'; ?>>5 Stelle</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Ingredienti</label>
                <select name="ingrediente[]" multiple style="height: 100px;">
                    <?php 
                    if ($result_ingredienti) {
                        // Reset del puntatore se il result set è stato già usato sopra
                        $result_ingredienti->data_seek(0); 
                        while($row = $result_ingredienti->fetch_assoc()) {
                            // Recuperiamo gli ingredienti già selezionati per mantenere il "selected"
                            $selezionati = isset($_GET['ingrediente']) ? $_GET['ingrediente'] : [];
                            $is_selected = in_array($row['id'], $selezionati) ? 'selected' : '';
                
                            echo '<option value="'.$row['id'].'" '.$is_selected.'>'.htmlspecialchars($row['nome']).'</option>';
                        }
                    }
                    ?>
                </select>
            </div>

            <button type="submit" class="btn-apply">Applica Filtri</button>
        </form>
    </aside>

    <main class="results-area">
        

        <div class="recipes-grid">
            <?php
            if ($result->num_rows > 0) {
                // INIZIO DEL CICLO: Ripete questo blocco per ogni ricetta trovata
                while($row = $result->fetch_assoc()) {
            
                    // 1. Gestione Immagine: Se manca, usiamo il logo come fallback
                    $img_path = !empty($row['img_profilo']) ? "img/ricette/" . $row['img_profilo'] : "img/logo.png";

                    // 2. Gestione Voto: Se è NULL (nessun voto), mostriamo "N/A" o 0
                    $voto_medio = $row['media_voti'] ? number_format($row['media_voti'], 1) : "N/A";
            
                    // 3. Link: Creiamo l'URL per andare alla pagina di dettaglio
                    $link_ricetta = "ricetta.php?id=" . $row['id'];
            ?>

            <article class="recipe-card">
                <a href="<?php echo $link_ricetta; ?>" class="card-link" style="text-decoration: none; color: inherit;">
                    
                    <div class="card-img">
                        <img src="<?php echo $img_path; ?>" alt="<?php echo htmlspecialchars($row['titolo']); ?>">
                    </div>
                    
                    <div class="card-body">
                        <h3 class="card-title"><?php echo htmlspecialchars($row['titolo']); ?></h3>
                        
                        <div class="card-info">
                            <span><i class="fa fa-utensils"></i> <?php echo htmlspecialchars($row['categoria']); ?></span>
                            <span><i class="fa fa-clock"></i> <?php echo $row['tempo_preparazione']; ?> min</span>
                        </div>
                        
                        <div class="card-info">
                            <span>Diff: <?php echo htmlspecialchars($row['difficolta']); ?></span>
                            
                            <span class="card-vote">
                                <i class="fa fa-star"></i> <?php echo $voto_medio; ?>
                            </span>
                        </div>
                    </div>

                </a>
            </article>
            <?php
                } // <--- Questa chiude il while (che mancava)
            } else {
                echo "<p style='padding: 20px;'>Nessuna ricetta trovata.</p>";
            } // <--- Questa chiude l'if (che mancava)
            ?>

        </div>
    </main>

</div>

<?php include 'footer.php'; ?>