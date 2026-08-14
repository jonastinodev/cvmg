<?php require_once __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Connexion — CVMG</title>
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#1863F2">
<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap');
  :root {
    --bleu: #1863F2; --bleu-marine: #0B1F3D; --bleu-clair: #EAF2FF;
    --orange: #F7941D; --texte: #22262B; --gris-texte: #5B6472; --gris-fond: #F6F7F9;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter', Arial, sans-serif; color: var(--texte); }
  a { color: inherit; }
  .enveloppe { max-width: 1180px; margin: 0 auto; padding: 0 6vw; }
  nav { display: flex; align-items: center; padding: 5mm 0; }
  .nav-logo { font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 15pt; color: var(--bleu-marine); text-decoration: none; }
  .nav-logo span { color: var(--orange); }

  .conteneur-connexion { min-height: 70vh; display: flex; align-items: center; justify-content: center; padding: 8mm; }
  .carte-connexion { background: #fff; border: 1px solid #E4E7EB; border-radius: 4mm; padding: 12mm 10mm;
    text-align: center; max-width: 380px; width: 100%; box-shadow: 0 2px 12px rgba(11,31,61,.06); }
  .carte-connexion h1 { font-family: 'Poppins', sans-serif; font-size: 17pt; color: var(--bleu-marine); margin-bottom: 3mm; }
  .carte-connexion p { color: var(--gris-texte); font-size: 10pt; margin-bottom: 8mm; }
  #zoneBoutonGoogle { display: flex; justify-content: center; }
  #statutConnexion { font-size: 9.5pt; margin-top: 4mm; min-height: 4mm; }
  #statutConnexion.erreur { color: #B00020; }
  #statutConnexion.chargement { color: var(--gris-texte); }
</style>
</head>
<body>

<div class="enveloppe">
  <nav>
    <a href="accueil.html" class="nav-logo">CV<span>MG</span></a>
  </nav>
</div>

<div class="conteneur-connexion">
  <div class="carte-connexion">
    <h1>Connexion</h1>
    <p>Retrouvez vos CV enregistrés, sans mot de passe à retenir.</p>

    <div id="zoneBoutonGoogle"></div>
    <p id="statutConnexion"></p>
  </div>
</div>

<script src="https://accounts.google.com/gsi/client" async defer></script>
<script>
  window.onload = function () {
    google.accounts.id.initialize({
      client_id: '<?= GOOGLE_CLIENT_ID ?>',
      callback: gererConnexionGoogle,
    });
    google.accounts.id.renderButton(
      document.getElementById('zoneBoutonGoogle'),
      { theme: 'outline', size: 'large', text: 'continue_with', shape: 'pill', width: 280 }
    );
  };

  async function gererConnexionGoogle(reponseGoogle) {
    const statut = document.getElementById('statutConnexion');
    statut.textContent = 'Connexion en cours...';
    statut.className = 'chargement';

    try {
      const res = await fetch('verifier-google.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ credential: reponseGoogle.credential }),
      });
      const data = await res.json().catch(() => null);
      if (!res.ok || !data || data.erreur) {
        throw new Error((data && data.erreur) || `Erreur serveur (HTTP ${res.status})`);
      }
      // On efface le brouillon local avant de rediriger : sans ça, un ancien
      // brouillon (rempli avant connexion, ou laissé par une autre personne
      // sur un appareil partagé) réapparaîtrait dans le formulaire au lieu de
      // repartir d'un état propre.
      localStorage.removeItem('cvmg_brouillon');

      statut.textContent = 'Connecté — redirection...';
      statut.className = 'chargement';
      window.location.href = 'creer-cv.php';
    } catch (err) {
      statut.textContent = 'Erreur de connexion : ' + err.message;
      statut.className = 'erreur';
    }
  }
</script>

</body>
</html>
