/**
 * recap.js — Gestion AJAX du récapitulatif/statistiques
 * Fonctions: actualiserStatsAJAX(), mettreAJourIndicateurs(), afficherLoader(), gererTimeout()
 */

(function() {
    'use strict';

    // Configuration
    const CONFIG = {
        timeout: 30000, // 30 secondes
        baseUrl: window.baseUrl || '',
        refreshInterval: null
    };

    // État du loader
    let loaderVisible = false;
    let timeoutId = null;

    /**
     * Actualiser les statistiques via AJAX
     * @returns {Promise}
     */
    function actualiserStatsAJAX() {
        return new Promise((resolve, reject) => {
            const btnActualiser = document.getElementById('btnActualiser');
            const spinnerActualiser = document.getElementById('spinnerActualiser');

            // Afficher le loader
            afficherLoader(true);
            if (spinnerActualiser) spinnerActualiser.classList.remove('d-none');
            if (btnActualiser) btnActualiser.disabled = true;

            // Gérer le timeout
            const controller = new AbortController();
            timeoutId = setTimeout(() => {
                controller.abort();
                gererTimeout();
            }, CONFIG.timeout);

            fetch(CONFIG.baseUrl + '/api/stats', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                signal: controller.signal
            })
            .then(response => {
                clearTimeout(timeoutId);
                if (!response.ok) {
                    throw new Error(`Erreur HTTP: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                // Masquer le loader
                afficherLoader(false);
                if (spinnerActualiser) spinnerActualiser.classList.add('d-none');
                if (btnActualiser) btnActualiser.disabled = false;

                if (data.success && data.data) {
                    mettreAJourIndicateurs(data.data);
                    afficherSucces('Données actualisées avec succès');
                    resolve(data.data);
                } else {
                    afficherErreur(data.message || 'Erreur lors de la récupération des données');
                    reject(new Error(data.message));
                }
            })
            .catch(error => {
                clearTimeout(timeoutId);
                afficherLoader(false);
                if (spinnerActualiser) spinnerActualiser.classList.add('d-none');
                if (btnActualiser) btnActualiser.disabled = false;

                if (error.name === 'AbortError') {
                    gererTimeout();
                } else {
                    afficherErreur(error.message);
                }
                reject(error);
            });
        });
    }

    /**
     * Mettre à jour tous les indicateurs de la page
     * @param {Object} stats - Données statistiques
     */
    function mettreAJourIndicateurs(stats) {
        // === Indicateurs Besoins (en MONTANT - conformément au sujet) ===
        const besoins = stats.besoins || {};
        // Montants en Ariary
        mettreAJourElement('besoinsMontantTotal', formatMontant(besoins.montant_total || 0));
        mettreAJourElement('besoinsMontantSatisfaits', formatMontant(besoins.montant_satisfaits || 0));
        mettreAJourElement('besoinsMontantRestants', formatMontant(besoins.montant_restants || 0));

        // Barre progression besoins (basée sur le pourcentage montant)
        const pctBesoins = besoins.pourcentage_montant || besoins.pourcentage_satisfaits || 0;
        mettreAJourElement('pourcentageBesoins', pctBesoins + '%');
        mettreAJourBarre('barreBesoins', pctBesoins);

        // === Indicateurs Dons ===
        const dons = stats.dons || {};
        mettreAJourElement('donsTotal', dons.total || 0);
        mettreAJourElement('donsNature', dons.nature || 0);
        mettreAJourElement('donsArgent', dons.argent || 0);
        mettreAJourElement('donsMontant', formatMontant(dons.valeur_totale || 0));

        // Barre progression dons
        const pctDons = dons.pourcentage_distribues || 0;
        mettreAJourElement('pourcentageDons', pctDons + '%');
        mettreAJourBarre('barreDons', pctDons);

        // === Indicateurs Distributions ===
        const distributions = stats.distributions || {};
        mettreAJourElement('distTotal', distributions.total || 0);
        mettreAJourElement('distConfirmees', distributions.confirmees || 0);
        mettreAJourElement('distEnAttente', distributions.en_attente || 0);
        mettreAJourElement('distQuantite', formatMontant(distributions.quantite_totale || 0));

        // Barre progression distributions
        const pctDist = distributions.pourcentage_confirmees || 0;
        mettreAJourElement('pourcentageDistributions', pctDist + '%');
        mettreAJourBarre('barreDistributions', pctDist);

        // === Indicateurs Achats ===
        const achats = stats.achats || {};
        mettreAJourElement('achatsTotaux', achats.total || 0);
        mettreAJourElement('achatsMontant', formatMontant(achats.montant_total || 0) + ' Ar');
        mettreAJourElement('achatsFrais', formatMontant(achats.frais_total || 0) + ' Ar');
        mettreAJourElement('achatsNet', formatMontant(achats.montant_net || 0) + ' Ar');

        // === Timestamp ===
        const now = new Date();
        const dateStr = now.toLocaleDateString('fr-FR') + ' ' + now.toLocaleTimeString('fr-FR');
        mettreAJourElement('derniereActualisation', dateStr);
    }

    /**
     * Mettre à jour un élément par son ID
     * @param {string} id - ID de l'élément
     * @param {*} valeur - Nouvelle valeur
     */
    function mettreAJourElement(id, valeur) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = valeur;
            // Animation de mise à jour
            element.classList.add('text-primary');
            setTimeout(() => element.classList.remove('text-primary'), 500);
        }
    }

    /**
     * Mettre à jour une barre de progression
     * @param {string} id - ID de la barre
     * @param {number} pourcentage - Pourcentage (0-100)
     */
    function mettreAJourBarre(id, pourcentage) {
        const barre = document.getElementById(id);
        if (barre) {
            barre.style.width = pourcentage + '%';
            barre.textContent = pourcentage + '%';
            barre.setAttribute('aria-valuenow', pourcentage);
        }
    }

    /**
     * Afficher/masquer le loader overlay
     * @param {boolean} visible - true pour afficher, false pour masquer
     */
    function afficherLoader(visible) {
        loaderVisible = visible;
        const loaderOverlay = document.getElementById('loaderOverlay');
        
        if (loaderOverlay) {
            if (visible) {
                loaderOverlay.classList.add('active');
            } else {
                loaderOverlay.classList.remove('active');
            }
        }
    }

    /**
     * Gérer le timeout de la requête
     */
    function gererTimeout() {
        console.warn('Timeout: La requête a pris trop de temps');
        afficherLoader(false);
        
        // Réactiver le bouton
        const btnActualiser = document.getElementById('btnActualiser');
        const spinnerActualiser = document.getElementById('spinnerActualiser');
        if (btnActualiser) btnActualiser.disabled = false;
        if (spinnerActualiser) spinnerActualiser.classList.add('d-none');

        // Afficher message d'erreur
        afficherErreur('La requête a pris trop de temps. Veuillez réessayer.');
    }

    /**
     * Afficher un message d'erreur
     * @param {string} message
     */
    function afficherErreur(message) {
        const alertContainer = document.querySelector('main .container-fluid') || document.querySelector('main');
        if (!alertContainer) return;

        const alertHtml = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert" id="alertErreurRecap">
                <strong>Erreur!</strong> ${escapeHtml(message)}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        const existing = document.getElementById('alertErreurRecap');
        if (existing) existing.remove();

        alertContainer.insertAdjacentHTML('afterbegin', alertHtml);

        setTimeout(() => {
            const alert = document.getElementById('alertErreurRecap');
            if (alert) alert.remove();
        }, 5000);
    }

    /**
     * Afficher un message de succès
     * @param {string} message
     */
    function afficherSucces(message) {
        const alertContainer = document.querySelector('main .container-fluid') || document.querySelector('main');
        if (!alertContainer) return;

        const alertHtml = `
            <div class="alert alert-success alert-dismissible fade show" role="alert" id="alertSuccesRecap">
                <strong>Succès!</strong> ${escapeHtml(message)}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        const existing = document.getElementById('alertSuccesRecap');
        if (existing) existing.remove();

        alertContainer.insertAdjacentHTML('afterbegin', alertHtml);

        setTimeout(() => {
            const alert = document.getElementById('alertSuccesRecap');
            if (alert) alert.remove();
        }, 3000);
    }

    /**
     * Échapper le HTML
     * @param {string} str
     * @returns {string}
     */
    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    /**
     * Formater un montant
     * @param {number} montant
     * @returns {string}
     */
    function formatMontant(montant) {
        return new Intl.NumberFormat('fr-FR').format(montant || 0);
    }

    /**
     * Activer le rafraîchissement automatique
     * @param {number} intervalMs - Intervalle en millisecondes
     */
    function activerAutoRefresh(intervalMs = 60000) {
        if (CONFIG.refreshInterval) {
            clearInterval(CONFIG.refreshInterval);
        }
        CONFIG.refreshInterval = setInterval(actualiserStatsAJAX, intervalMs);
    }

    /**
     * Désactiver le rafraîchissement automatique
     */
    function desactiverAutoRefresh() {
        if (CONFIG.refreshInterval) {
            clearInterval(CONFIG.refreshInterval);
            CONFIG.refreshInterval = null;
        }
    }

    // Initialisation au chargement du DOM
    document.addEventListener('DOMContentLoaded', function() {
        // Récupérer le baseUrl depuis PHP
        const baseElement = document.querySelector('meta[name="base-url"]');
        if (baseElement) {
            CONFIG.baseUrl = baseElement.getAttribute('content') || '';
        }

        // Attacher l'événement au bouton Actualiser
        const btnActualiser = document.getElementById('btnActualiser');
        if (btnActualiser) {
            btnActualiser.addEventListener('click', function(e) {
                e.preventDefault();
                actualiserStatsAJAX();
            });
        }
    });

    // Exposer les fonctions globalement
    window.RecapJS = {
        actualiserStatsAJAX,
        mettreAJourIndicateurs,
        afficherLoader,
        gererTimeout,
        activerAutoRefresh,
        desactiverAutoRefresh
    };

})();
