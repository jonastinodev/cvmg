// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', () => {
  // Selectors
  const fileInput = document.getElementById('fileInput');
  const previewUpload = document.getElementById('previewUpload');
  const uploadButtons = document.getElementById('uploadButtons');
  const fileInfo = document.getElementById('fileInfo');
  const errorMessage = document.getElementById('errorMessage');

  const stepUpload = document.getElementById('step-upload');
  const stepConfirm = document.getElementById('step-confirm');
  const previewConfirm = document.getElementById('previewConfirm');
  const confirmButtons = document.getElementById('confirmButtons');

  const indicator1 = document.getElementById('indicator1');
  const indicator2 = document.getElementById('indicator2');

  const rectoCard = document.getElementById("rectoCard");
  const versoCard = document.getElementById("versoCard");

  let selectedFiles = [];
  let rectoFile = null;
  let versoFile = null;

  const OCR_URL = "ocr.php"; // chemin relatif : ocr.php doit être déposé dans le même dossier que cette page

  // Helpers
  function showPreview(files, container) {
    container.innerHTML = '';
    [...files].forEach(file => {
      if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = e => {
          const img = document.createElement('img');
          img.src = e.target.result;
          img.className = 'preview';
          container.appendChild(img);
        };
        reader.readAsDataURL(file);
      } else if (file.type === 'application/pdf') {
        const p = document.createElement('p');
        p.textContent = 'PDF importé : ' + file.name;
        container.appendChild(p);
      }
    });
  }

  function updateFileInfo(files) {
    if (!files || files.length === 0) {
      fileInfo.style.display = 'none';
      return;
    }

    fileInfo.style.display = 'block';
    fileInfo.innerHTML = '<strong>Fichiers sélectionnés:</strong><br>';
    [...files].forEach(file => {
      fileInfo.innerHTML += `• ${file.name} (${(file.size / 1024).toFixed(1)} KB)<br>`;
    });
  }

  function showError(message) {
    errorMessage.textContent = message;
    errorMessage.style.display = 'block';
    setTimeout(() => errorMessage.style.display = 'none', 5000);
  }

  function enableNextButton() {
    uploadButtons.innerHTML = '<button class="next">Suivant</button>';
    uploadButtons.querySelector('.next').addEventListener('click', () => {
      stepUpload.classList.remove('active');
      stepConfirm.classList.add('active');
      indicator1.classList.remove('active');
      indicator1.classList.add('done');
      indicator2.classList.add('active');
      showPreview(selectedFiles, previewConfirm);
    });
  }

  function disableNextButton() {
    uploadButtons.innerHTML = '';
  }

  // File upload
  if (fileInput) {
    fileInput.addEventListener('change', () => {
      const files = fileInput.files;
      previewUpload.innerHTML = '';
      errorMessage.style.display = 'none';

      if (!files.length) {
        disableNextButton();
        updateFileInfo([]);
        return;
      }

      if (files.length === 1 && files[0].type === 'application/pdf') {
        selectedFiles = files;
        showPreview(files, previewUpload);
        updateFileInfo(files);
        enableNextButton();
        return;
      }

      if (files.length === 2 && [...files].every(f => f.type.startsWith('image/'))) {
        selectedFiles = files;
        showPreview(files, previewUpload);
        updateFileInfo(files);
        enableNextButton();
        return;
      }

      showError('Veuillez importer soit 1 PDF, soit exactement 2 images (recto+verso).');
      fileInput.value = '';
      selectedFiles = [];
      updateFileInfo([]);
      disableNextButton();
    });
  }

  // Camera (recto/verso)
  const cameraRecto = document.getElementById("cameraRecto");
  const cameraVerso = document.getElementById("cameraVerso");

  function checkBothPhotos() {
    if (rectoFile && versoFile) {
      selectedFiles = [rectoFile, versoFile];
      enableNextButton();
    } else {
      disableNextButton();
    }
  }

  if (cameraRecto) {
    cameraRecto.addEventListener("change", () => {
      rectoFile = cameraRecto.files[0];
      if (rectoFile) {
        showPreview([rectoFile], previewUpload);
        rectoCard.classList.add("done");
        updateFileInfo([rectoFile, versoFile].filter(Boolean));
      }
      checkBothPhotos();
    });
  }

  if (cameraVerso) {
    cameraVerso.addEventListener("change", () => {
      versoFile = cameraVerso.files[0];
      if (versoFile) {
        showPreview([versoFile], previewUpload);
        versoCard.classList.add("done");
        updateFileInfo([rectoFile, versoFile].filter(Boolean));
      }
      checkBothPhotos();
    });
  }

  // Confirmation step
  if (confirmButtons) {
    confirmButtons.innerHTML = '<button class="prev">Précédent</button><button class="next">Lancer OCR</button>';

    confirmButtons.querySelector('.prev').addEventListener('click', () => {
      stepConfirm.classList.remove('active');
      stepUpload.classList.add('active');
      indicator2.classList.remove('active');
      indicator1.classList.remove('done');
      indicator1.classList.add('active');
    });

    confirmButtons.querySelector('.next').addEventListener('click', async () => {
      const button = confirmButtons.querySelector('.next');
      const originalText = button.textContent;
      button.textContent = 'Traitement OCR en cours...';
      button.disabled = true;

      try {
        const formData = new FormData();
        Array.from(selectedFiles).forEach((file, index) => {
          formData.append(`file${index + 1}`, file);
        });

        const res = await fetch(OCR_URL, { method: "POST", body: formData });
        const data = await res.json().catch(() => null);
        if (!res.ok) throw new Error((data && data.erreur) ? data.erreur : `Erreur réseau vers le serveur OCR (HTTP ${res.status})`);
        if (data.erreur) throw new Error(data.erreur);
        console.log("✅ Réponse OCR :", data);

        // Sauvegarde locale
        localStorage.setItem("ocrResult", JSON.stringify(data));

        // Redirection vers la page résultat
        button.textContent = "✅ OCR terminé, ouverture du résultat...";
        setTimeout(() => {
          window.location.href = "ocr.html";
        }, 1000);

      } catch (err) {
        console.error("Erreur upload :", err);
        alert("Erreur lors de l'envoi ou du traitement OCR : " + err.message);
        button.textContent = originalText;
        button.disabled = false;
      }
    });
  }
});
