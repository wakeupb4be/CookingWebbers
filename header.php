<?php
// Esempio di logica per verificare se l'utente è loggato
session_start();
$is_logged_in = isset($_SESSION['user_id']); 
?>

<link rel="icon" type="image/png" href="img/logo.png">

<link rel="stylesheet" href="css/header.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<header class="main-header">
    <div class="header-top">
        <a href="home.php" class="logo">
            <img src="img/logo.png" alt="Logo Ricette">
        </a>
    </div>

    <nav class="header-bottom">
        <div class="dropdown">
            <button class="dropbtn">Ricette <i class="fa fa-caret-down"></i></button>
            <div class="dropdown-content">
                <a href="ricerca.php?cat=Antipasto">Antipasti</a>
                <a href="ricerca.php?cat=Primo">Primi</a>
                <a href="ricerca.php?cat=Secondo">Secondi</a>
                <a href="ricerca.php?cat=Dolce">Dolci</a>
            </div>
        </div>

        <form action="ricerca.php" method="GET" class="search-container">
            <div class="search-wrapper">
                <i class="fa fa-search search-icon"></i>
                <input type="text" name="search" placeholder="Cerca una ricetta..." class="search-input">
            </div>
        </form>

        <div class="user-menu dropdown">
            <button class="dropbtn">
                <i class="fa fa-user-circle"></i>
            </button>
            <div class="dropdown-content right">
                <?php if (!$is_logged_in): ?>
                    <a href="login.php">Accedi</a>
                    <a href="register.php">Registrati</a>
                <?php else: ?>
                    <a href="profilo.php">Mio Profilo</a>
                    <a href="preferiti.php">Ricette Preferite</a>
                    <a href="crea-ricetta.php">Crea Ricetta</a>
                    <a href="mie-ricette.php">Le mie Ricette</a>
                    <hr>
                    <a href="logout.php">Logout</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>