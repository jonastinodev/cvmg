// OCR Results Page Script - Fixed Location Button
document.addEventListener('DOMContentLoaded', () => {
    // Form submission
    const identityCardForm = document.getElementById('identityCardForm');
    const locationRequest = document.getElementById('locationRequest');
    const locationInfo = document.getElementById('locationInfo');
    const addressDisplay = document.getElementById('addressDisplay');
    const addressInput = document.getElementById('adresse');
    const enableLocationBtn = document.getElementById('enableLocation');

    // Check if we're on a mobile device
    function isMobileDevice() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }
    // Charger automatiquement le résultat OCR reçu depuis la page d’upload
    const saved = localStorage.getItem("ocrResult");
    if (saved) {
        try {
            const data = JSON.parse(saved);
            console.log("📥 Données OCR reçues :", data);

            // Remplir les champs si disponibles
            if (data.nom) document.getElementById("nom").value = data.nom;
            if (data.prenom) document.getElementById("prenom").value = data.prenom;
            if (data.dateNaissance) document.getElementById("dateNaissance").value = data.dateNaissance;
            if (data.lieuNaissance) document.getElementById("lieuNaissance").value = data.lieuNaissance;
            if (data.adresse) document.getElementById("adresse").value = data.adresse;
            if (data.numero) document.getElementById("numero").value = data.numero;
        } catch (e) {
        console.error("⚠️ Erreur parsing OCR result", e);
        }
   }

    // GPS Location Detection - Triggered by user click
    function getLocation() {
        console.log("Bouton de localisation cliqué");
        
        if (navigator.geolocation) {
            console.log("Géolocalisation supportée");
            
            // Show loading state
            enableLocationBtn.disabled = true;
            enableLocationBtn.innerHTML = '<span class="material-symbols-outlined">location_searching</span> Détection en cours...';
            
            addressDisplay.innerHTML = `
                <div class="location-loading">
                    <span class="material-symbols-outlined">location_searching</span>
                    Détection de votre adresse en cours...
                </div>
            `;
            
            // Hide request, show info
            locationRequest.style.display = 'none';
            locationInfo.style.display = 'block';
            
            // For Android devices, use different options
            const options = {
                enableHighAccuracy: true,
                timeout: 20000, // Increased timeout for mobile
                maximumAge: 60000
            };

            // Add specific options for Android
            if (isMobileDevice()) {
                options.timeout = 30000; // Even longer timeout for mobile
                console.log("Appareil mobile détecté, timeout étendu à 30s");
            }
            
            console.log("Demande de géolocalisation envoyée avec options:", options);
            
            navigator.geolocation.getCurrentPosition(
                showPosition, 
                showError,
                options
            );
        } else {
            console.log("Géolocalisation non supportée");
            addressDisplay.innerHTML = '<span class="error-message">❌ La géolocalisation n\'est pas supportée par ce navigateur.</span>';
            locationRequest.style.display = 'none';
            locationInfo.style.display = 'block';
        }
    }

    function showPosition(position) {
        console.log("Position obtenue avec succès:", position);
        const latitude = position.coords.latitude;
        const longitude = position.coords.longitude;
        
        // In a real application, you would reverse geocode these coordinates
        // For this demo, we'll simulate an address lookup
        simulateReverseGeocoding(latitude, longitude);
    }

    function simulateReverseGeocoding(lat, lng) {
        console.log("Simulation de géocodage inverse pour:", lat, lng);
        
        // Simulate API call delay
        setTimeout(() => {
            const addresses = [
                "67 Ha, Rue Dr Raseta, Andraharo, Antananarivo 101",
                "Lot IVK 87 Bis, Ambohidratrimo, Antananarivo",
                "Immeuble Aro, Ampefiloha, Antananarivo 101",
                "Rue des 77 Parlementaires Français, Faravohitra, Antananarivo",
                "Analakely, Antananarivo 101",
                "Isoraka, Antananarivo 101",
                "Ankadifotsy, Antananarivo",
                "Ivandry, Antananarivo"
            ];
            
            const randomAddress = addresses[Math.floor(Math.random() * addresses.length)];
            
            console.log("Adresse simulée:", randomAddress);
            
            addressDisplay.innerHTML = `<span class="success-message">✅ ${randomAddress}</span>`;
            addressInput.value = randomAddress;
            
            // Re-enable the button
            enableLocationBtn.disabled = false;
            enableLocationBtn.innerHTML = '<span class="material-symbols-outlined">location_on</span> Localisation réussie';
        }, 2000);
    }

    function showError(error) {
        console.error("Erreur de géolocalisation:", error);
        
        let message = "";
        
        switch(error.code) {
            case error.PERMISSION_DENIED:
                message = "❌ Vous avez refusé l'accès à votre localisation. Veuillez :<br>• Accepter la permission dans votre navigateur<br>• Ou saisir manuellement votre adresse ci-dessous";
                break;
            case error.POSITION_UNAVAILABLE:
                message = "📡 Les informations de localisation ne sont pas disponibles. Vérifiez votre connexion GPS et internet.";
                break;
            case error.TIMEOUT:
                message = "⏰ La détection de localisation a pris trop de temps. Vérifiez votre connexion et réessayez.";
                break;
            case error.UNKNOWN_ERROR:
                message = "❌ Une erreur inconnue s'est produite. Veuillez saisir manuellement votre adresse.";
                break;
        }
        
        addressDisplay.innerHTML = `<span class="error-message">${message}</span>`;
        
        // Re-enable the button with retry option
        enableLocationBtn.disabled = false;
        enableLocationBtn.innerHTML = '<span class="material-symbols-outlined">location_on</span> Réessayer la localisation';
        
        // Show the request button again
        locationRequest.style.display = 'block';
    }

    // Form Submission
    identityCardForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate required fields
        const requiredFields = ['nom', 'prenom', 'dateNaissance', 'lieuNaissance', 'numero', 'adresse', 'profession'];
        let isValid = true;
        let errorMessage = "";
        
        requiredFields.forEach(field => {
            const input = document.getElementById(field);
            if (!input.value.trim()) {
                isValid = false;
                input.style.borderColor = 'var(--error)';
                
                // Build error message
                if (field === 'profession') {
                    errorMessage = "Veuillez sélectionner une profession.";
                } else {
                    errorMessage = "Veuillez remplir tous les champs obligatoires.";
                }
            } else {
                input.style.borderColor = 'var(--outline)';
            }
        });
        
        if (isValid) {
            // Show loading state
            const submitBtn = this.querySelector('.submit-btn');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Envoi en cours...';
            submitBtn.disabled = true;
            
            // Données validées par l'utilisateur, prêtes pour l'enregistrement en base
            const formData = {
                nom: document.getElementById('nom').value,
                prenom: document.getElementById('prenom').value,
                dateNaissance: document.getElementById('dateNaissance').value,
                lieuNaissance: document.getElementById('lieuNaissance').value,
                adresse: document.getElementById('adresse').value,
                numero: document.getElementById('numero').value,
                profession: document.getElementById('profession').value
            };
            
            console.log('Données à envoyer:', formData);
            
            fetch('enregistrer.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            })
                .then(res => res.json())
                .then(data => {
                    if (data.erreur) throw new Error(data.erreur);
                    localStorage.removeItem('ocrResult');
                    alert('Données enregistrées avec succès !');
                })
                .catch(err => {
                    console.error('Erreur enregistrement :', err);
                    alert('Erreur lors de l\'enregistrement : ' + err.message);
                })
                .finally(() => {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                });
        } else {
            alert(errorMessage || "Veuillez remplir tous les champs obligatoires.");
        }
    });

    // Event listener for location button - FIXED
    enableLocationBtn.addEventListener('click', getLocation);
    
    // Also add touch event for mobile
    enableLocationBtn.addEventListener('touchstart', function(e) {
        e.preventDefault();
        getLocation();
    }, { passive: false });

    // Initialize the page
    console.log("Page OCR initialisée - Bouton de localisation prêt");
});