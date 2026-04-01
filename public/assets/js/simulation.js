/**
 * simulation.js — Gestion AJAX de la simulation de dispatch
 * Fonctions: lancerSimulationAJAX(), validerSimulationAJAX(), afficherResultats(), gererErreurs()
 */

(function() {
    'use strict';

    // Configuration
    const CONFIG = {
        timeout: 30000, // 30 secondes
        baseUrl: window.baseUrl || ''
    };

    // Stockage des propositions simulées
    let propositionsSimulees = [];

    /**
     * Lancer la simulation via AJAX
     * @returns {Promise}
     */
    function lancerSimulationAJAX() {
        return new Promise((resolve, reject) => {
            const btnSimuler = document.getElementById('btnSimuler');
            const spinnerSimuler = document.getElementById('spinnerSimuler');
            
            // Afficher le loader
            if (spinnerSimuler) spinnerSimuler.classList.remove('d-none');
            if (btnSimuler) btnSimuler.disabled = true;

            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), CONFIG.timeout);

            fetch(CONFIG.baseUrl + '/api/simulation/lancer', {
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
                if (spinnerSimuler) spinnerSimuler.classList.add('d-none');
                if (btnSimuler) btnSimuler.disabled = false;

                if (data.success) {
                    propositionsSimulees = data.distributions || [];
                    afficherResultats(data);
                    resolve(data);
                } else {
                    gererErreurs(data.message || 'Erreur lors de la simulation');
                    reject(new Error(data.message));
                }
            })
            .catch(error => {
                clearTimeout(timeoutId);
                if (spinnerSimuler) spinnerSimuler.classList.add('d-none');
                if (btnSimuler) btnSimuler.disabled = false;
                
                if (error.name === 'AbortError') {
                    gererErreurs('Timeout: La simulation a pris trop de temps');
                } else {
                    gererErreurs(error.message);
                }
                reject(error);
            });
        });
    }

    /**
     * Valider et persister la simulation via AJAX
     * @returns {Promise}
     */
    function validerSimulationAJAX() {
        return new Promise((resolve, reject) => {
            if (propositionsSimulees.length === 0) {
                gererErreurs('Aucune proposition à valider. Lancez d\'abord une simulation.');
                reject(new Error('Aucune proposition'));
                return;
            }

            const btnValider = document.getElementById('btnValider');
            if (btnValider) btnValider.disabled = true;

            // Soumission directe sans popup de confirmation

            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), CONFIG.timeout);

            const formData = new FormData();
            formData.append('distributions', JSON.stringify(propositionsSimulees));

            fetch(CONFIG.baseUrl + '/api/simulation/valider', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData,
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
                if (btnValider) btnValider.disabled = false;

                if (data.success) {
                    // Afficher succès
                    afficherSucces(data.message || `${data.count || propositionsSimulees.length} distribution(s) créée(s)`);
                    propositionsSimulees = []; // Vider les propositions
                    
                    // Recharger la page après 2 secondes
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                    
                    resolve(data);
                } else {
                    gererErreurs(data.message || 'Erreur lors de la validation');
                    reject(new Error(data.message));
                }
            })
            .catch(error => {
                clearTimeout(timeoutId);
                if (btnValider) btnValider.disabled = false;
                
                if (error.name === 'AbortError') {
                    gererErreurs('Timeout: La validation a pris trop de temps');
                } else {
                    gererErreurs(error.message);
                }
                reject(error);
            });
        });
    }

    /**
     * Afficher les résultats de la simulation
     * @param {Object} data - Données de la simulation
     */
    function afficherResultats(data) {
        const distributions = data.distributions || [];
        const stats = data.stats || {};

        // Séparer nature et achats
        const nature = distributions.filter(d => d.type === 'nature' || !d.type);
        const achats = distributions.filter(d => d.type === 'achat');

        // Afficher tableau propositions nature
        const cardNature = document.getElementById('cardPropositionsNature');
        if (cardNature && nature.length > 0) {
            cardNature.style.display = 'block';
            const tbody = cardNature.querySelector('tbody');
            if (tbody) {
                tbody.innerHTML = nature.map((d, i) => `
                    <tr>
                        <td>${i + 1}</td>
                        <td><strong>${escapeHtml(d.ville_nom || '')}</strong></td>
                        <td>${escapeHtml(d.produit_nom || '')}</td>
                        <td><span class="badge bg-success">${d.quantite_attribuee || d.quantite || 0}</span></td>
                        <td>${escapeHtml(d.donateur_nom || '')}</td>
                        <td><span class="badge bg-info">Nature</span></td>
                    </tr>
                `).join('');
            }
        }

        // Afficher tableau propositions achats
        const cardAchats = document.getElementById('cardPropositionsAchats');
        if (cardAchats && achats.length > 0) {
            cardAchats.style.display = 'block';
            const tbody = cardAchats.querySelector('tbody');
            if (tbody) {
                tbody.innerHTML = achats.map((d, i) => `
                    <tr>
                        <td>${i + 1}</td>
                        <td><strong>${escapeHtml(d.ville_nom || '')}</strong></td>
                        <td>${escapeHtml(d.produit_nom || '')}</td>
                        <td>${d.quantite || 0}</td>
                        <td>${formatMontant(d.montant || 0)} Ar</td>
                        <td>${formatMontant(d.frais || 0)} Ar</td>
                        <td><strong>${formatMontant(d.total || 0)} Ar</strong></td>
                    </tr>
                `).join('');
            }
        }

        // Afficher résumé
        const cardResume = document.getElementById('cardResume');
        if (cardResume) {
            cardResume.style.display = 'block';
            
            const resumeNature = document.getElementById('resumeNature');
            const resumeAchats = document.getElementById('resumeAchats');
            const resumeTotal = document.getElementById('resumeTotal');
            const resumeTaux = document.getElementById('resumeTaux');
            
            if (resumeNature) resumeNature.textContent = nature.length;
            if (resumeAchats) resumeAchats.textContent = achats.length;
            if (resumeTotal) resumeTotal.textContent = distributions.length;
            if (resumeTaux) resumeTaux.textContent = (stats.taux_satisfaction || 0) + '%';
        }

        // Activer bouton valider
        const btnValider = document.getElementById('btnValider');
        if (btnValider && distributions.length > 0) {
            btnValider.disabled = false;
        }

        // Mettre à jour le champ hidden
        const distributionsData = document.getElementById('distributionsData');
        if (distributionsData) {
            distributionsData.value = JSON.stringify(distributions);
        }

        // Notification
        afficherSucces(`Simulation terminée: ${distributions.length} proposition(s)`);
    }

    /**
     * Gérer les erreurs
     * @param {string} message - Message d'erreur
     */
    function gererErreurs(message) {
        console.error('Erreur simulation:', message);
        
        // Créer une alerte
        const alertContainer = document.querySelector('.container-fluid main') || document.body;
        const alertHtml = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert" id="alertErreur">
                <strong>Erreur!</strong> ${escapeHtml(message)}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        // Supprimer alerte existante
        const existingAlert = document.getElementById('alertErreur');
        if (existingAlert) existingAlert.remove();
        
        // Insérer la nouvelle alerte
        alertContainer.insertAdjacentHTML('afterbegin', alertHtml);
        
        // Auto-dismiss après 5 secondes
        setTimeout(() => {
            const alert = document.getElementById('alertErreur');
            if (alert) alert.remove();
        }, 5000);
    }

    /**
     * Afficher un message de succès
     * @param {string} message
     */
    function afficherSucces(message) {
        const alertContainer = document.querySelector('.container-fluid main') || document.body;
        const alertHtml = `
            <div class="alert alert-success alert-dismissible fade show" role="alert" id="alertSucces">
                <strong>Succès!</strong> ${escapeHtml(message)}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        const existingAlert = document.getElementById('alertSucces');
        if (existingAlert) existingAlert.remove();
        
        alertContainer.insertAdjacentHTML('afterbegin', alertHtml);
        
        setTimeout(() => {
            const alert = document.getElementById('alertSucces');
            if (alert) alert.remove();
        }, 5000);
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

    // Initialisation au chargement du DOM
    document.addEventListener('DOMContentLoaded', function() {
        // Récupérer le baseUrl depuis PHP
        const baseElement = document.querySelector('meta[name="base-url"]');
        if (baseElement) {
            CONFIG.baseUrl = baseElement.getAttribute('content') || '';
        }

        // Attacher les événements aux boutons
        const btnSimuler = document.getElementById('btnSimuler');
        if (btnSimuler) {
            btnSimuler.addEventListener('click', function(e) {
                e.preventDefault();
                lancerSimulationAJAX();
            });
        }

        const formValider = document.getElementById('formValider');
        if (formValider) {
            formValider.addEventListener('submit', function(e) {
                e.preventDefault();
                validerSimulationAJAX();
            });
        }
    });

    // Exposer les fonctions globalement
    window.SimulationJS = {
        lancerSimulationAJAX,
        validerSimulationAJAX,
        afficherResultats,
        gererErreurs
    };

})();
