<?php
// partials/pied.php — Pied de page commun aux pages marketing (accueil, apropos,
// contact, conditions, confidentialite). Un seul endroit à modifier pour changer
// les liens ou le logo sur les 5 pages à la fois.
// Nécessite la classe CSS .footer-grille / .footer-liens / .nav-logo, déjà
// définie dans le <style> de chaque page qui inclut ce partiel.
?>
<footer>
  <div class="enveloppe footer-grille">
    <a href="accueil.php" class="nav-logo" style="font-size:12pt">CV<span>MG</span></a>
    <div class="footer-liens">
      <a href="apropos.php">À propos</a>
      <a href="contact.php">Contact</a>
      <a href="confidentialite.php">Confidentialité</a>
      <a href="conditions.php">Conditions d'utilisation</a>
    </div>
  </div>
</footer>
