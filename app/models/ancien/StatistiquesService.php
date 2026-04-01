<?php
namespace app\models;

/**
 * StatistiquesService — Service de calcul des statistiques et récapitulatifs
 * Fournit toutes les données chiffrées pour le tableau de bord et les rapports
 */
class StatistiquesService {
    private \PDO $db;

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    /**
     * Récap global pour le tableau de bord principal
     * @return array
     */
    public function getRecapGlobal(): array {
        return [
            'besoins' => $this->getStatistiquesBesoins(),
            'dons' => $this->getStatistiquesDons(),
            'distributions' => $this->getStatistiquesDistributions(),
            'achats' => $this->getStatistiquesAchats(),
            'villes' => $this->getStatistiquesParVille(),
        ];
    }

    /**
     * Statistiques sur les besoins
     * Retourne les MONTANTS en Ariary (conformément au sujet)
     * @return array
     */
    public function getStatistiquesBesoins(): array {
        try {
            // Total des besoins (nombre)
            $stmtTotal = $this->db->query("SELECT COUNT(*) as total FROM besoin");
            $total = (int)$stmtTotal->fetchColumn();

            // Besoins satisfaits (status = 2) - nombre
            $stmtSatisfaits = $this->db->query("SELECT COUNT(*) as total FROM besoin WHERE idStatus = 2");
            $satisfaits = (int)$stmtSatisfaits->fetchColumn();

            // Besoins en attente (status = 1) - nombre
            $stmtEnAttente = $this->db->query("SELECT COUNT(*) as total FROM besoin WHERE idStatus = 1");
            $enAttente = (int)$stmtEnAttente->fetchColumn();

            // MONTANT TOTAL des besoins (quantite * prixUnitaire) en Ariary
            $stmtMontantTotal = $this->db->query("
                SELECT COALESCE(SUM(b.quantite * p.prixUnitaire), 0) as montant
                FROM besoin b
                LEFT JOIN produit p ON b.idProduit = p.id
            ");
            $montantTotal = (float)$stmtMontantTotal->fetchColumn();

            // MONTANT des besoins SATISFAITS (status = 2) en Ariary
            $stmtMontantSatisfaits = $this->db->query("
                SELECT COALESCE(SUM(b.quantite * p.prixUnitaire), 0) as montant
                FROM besoin b
                LEFT JOIN produit p ON b.idProduit = p.id
                WHERE b.idStatus = 2
            ");
            $montantSatisfaits = (float)$stmtMontantSatisfaits->fetchColumn();

            // MONTANT des besoins RESTANTS (status = 1, en attente) en Ariary
            $stmtMontantRestants = $this->db->query("
                SELECT COALESCE(SUM(b.quantite * p.prixUnitaire), 0) as montant
                FROM besoin b
                LEFT JOIN produit p ON b.idProduit = p.id
                WHERE b.idStatus = 1
            ");
            $montantRestants = (float)$stmtMontantRestants->fetchColumn();

            // Quantité totale des besoins
            $stmtQte = $this->db->query("SELECT COALESCE(SUM(quantite), 0) as qte FROM besoin");
            $quantiteTotale = (int)$stmtQte->fetchColumn();

            // Pourcentage basé sur les montants (conformément au sujet)
            $pourcentageMontant = $montantTotal > 0 ? round(($montantSatisfaits / $montantTotal) * 100, 1) : 0;

            return [
                'total' => $total,
                'satisfaits' => $satisfaits,
                'en_attente' => $enAttente,
                'pourcentage_satisfaits' => $total > 0 ? round(($satisfaits / $total) * 100, 1) : 0,
                'montant_total' => $montantTotal,
                'montant_satisfaits' => $montantSatisfaits,
                'montant_restants' => $montantRestants,
                'pourcentage_montant' => $pourcentageMontant,
                'quantite_totale' => $quantiteTotale,
            ];
        } catch (\PDOException $e) {
            return [
                'total' => 0,
                'satisfaits' => 0,
                'en_attente' => 0,
                'pourcentage_satisfaits' => 0,
                'montant_total' => 0,
                'montant_satisfaits' => 0,
                'montant_restants' => 0,
                'pourcentage_montant' => 0,
                'quantite_totale' => 0,
            ];
        }
    }

    /**
     * Statistiques sur les dons
     * @return array
     */
    public function getStatistiquesDons(): array {
        try {
            // Total des dons
            $stmtTotal = $this->db->query("SELECT COUNT(*) as total FROM don");
            $total = (int)$stmtTotal->fetchColumn();

            // Dons en nature
            $stmtNature = $this->db->query("SELECT COUNT(*) as total FROM don WHERE idProduit IS NOT NULL");
            $nature = (int)$stmtNature->fetchColumn();

            // Dons argent
            $stmtArgent = $this->db->query("SELECT COUNT(*) as total FROM don WHERE montant IS NOT NULL AND montant > 0");
            $argent = (int)$stmtArgent->fetchColumn();

            // Montant total des dons argent
            $stmtMontantArgent = $this->db->query("SELECT COALESCE(SUM(montant), 0) FROM don WHERE montant IS NOT NULL");
            $montantArgent = (float)$stmtMontantArgent->fetchColumn();

            // Valeur totale des dons en nature
            $stmtValeurNature = $this->db->query("
                SELECT COALESCE(SUM(d.quantite * p.prixUnitaire), 0)
                FROM don d
                JOIN produit p ON d.idProduit = p.id
                WHERE d.idProduit IS NOT NULL
            ");
            $valeurNature = (float)$stmtValeurNature->fetchColumn();

            // Dons distribués (statut 2)
            $stmtDistribues = $this->db->query("SELECT COUNT(*) FROM don WHERE idStatus = 2");
            $distribues = (int)$stmtDistribues->fetchColumn();

            return [
                'total' => $total,
                'nature' => $nature,
                'argent' => $argent,
                'montant_argent' => $montantArgent,
                'valeur_nature' => $valeurNature,
                'valeur_totale' => $montantArgent + $valeurNature,
                'distribues' => $distribues,
                'pourcentage_distribues' => $total > 0 ? round(($distribues / $total) * 100, 1) : 0,
            ];
        } catch (\PDOException $e) {
            return [
                'total' => 0,
                'nature' => 0,
                'argent' => 0,
                'montant_argent' => 0,
                'valeur_nature' => 0,
                'valeur_totale' => 0,
                'distribues' => 0,
                'pourcentage_distribues' => 0,
            ];
        }
    }

    /**
     * Statistiques sur les distributions
     * @return array
     */
    public function getStatistiquesDistributions(): array {
        try {
            // Total distributions
            $stmtTotal = $this->db->query("SELECT COUNT(*) FROM distribution");
            $total = (int)$stmtTotal->fetchColumn();

            // Distributions confirmées (status 2)
            $stmtConfirmees = $this->db->query("SELECT COUNT(*) FROM distribution WHERE idStatusDistribution = 2");
            $confirmees = (int)$stmtConfirmees->fetchColumn();

            // En attente (status 1)
            $stmtEnAttente = $this->db->query("SELECT COUNT(*) FROM distribution WHERE idStatusDistribution = 1");
            $enAttente = (int)$stmtEnAttente->fetchColumn();

            // Quantité totale distribuée
            $stmtQte = $this->db->query("SELECT COALESCE(SUM(quantite), 0) FROM distribution WHERE idStatusDistribution = 2");
            $quantiteTotale = (int)$stmtQte->fetchColumn();

            return [
                'total' => $total,
                'confirmees' => $confirmees,
                'en_attente' => $enAttente,
                'quantite_totale' => $quantiteTotale,
                'pourcentage_confirmees' => $total > 0 ? round(($confirmees / $total) * 100, 1) : 0,
            ];
        } catch (\PDOException $e) {
            return [
                'total' => 0,
                'confirmees' => 0,
                'en_attente' => 0,
                'quantite_totale' => 0,
                'pourcentage_confirmees' => 0,
            ];
        }
    }

    /**
     * Statistiques sur les achats
     * @return array
     */
    public function getStatistiquesAchats(): array {
        try {
            // Total des achats
            $stmtTotal = $this->db->query("SELECT COUNT(*) FROM achat");
            $total = (int)$stmtTotal->fetchColumn();

            // Montant total
            $stmtMontant = $this->db->query("SELECT COALESCE(SUM(montant_total), 0) FROM achat");
            $montantTotal = (float)$stmtMontant->fetchColumn();

            // Total frais
            $stmtFrais = $this->db->query("SELECT COALESCE(SUM(frais_appliques), 0) FROM achat");
            $fraisTotal = (float)$stmtFrais->fetchColumn();

            // Achats ce mois
            $stmtMois = $this->db->query("SELECT COUNT(*) FROM achat WHERE MONTH(date_achat) = MONTH(NOW()) AND YEAR(date_achat) = YEAR(NOW())");
            $ceMois = (int)$stmtMois->fetchColumn();

            return [
                'total' => $total,
                'montant_total' => $montantTotal,
                'frais_total' => $fraisTotal,
                'ce_mois' => $ceMois,
                'montant_net' => $montantTotal - $fraisTotal,
            ];
        } catch (\PDOException $e) {
            return [
                'total' => 0,
                'montant_total' => 0,
                'frais_total' => 0,
                'ce_mois' => 0,
                'montant_net' => 0,
            ];
        }
    }

    /**
     * Statistiques par ville
     * @return array
     */
    public function getStatistiquesParVille(): array {
        try {
            $sql = "
                SELECT 
                    v.id,
                    v.nom,
                    COALESCE(besoins.total, 0) as besoins_total,
                    COALESCE(besoins.satisfaits, 0) as besoins_satisfaits,
                    COALESCE(distributions.total, 0) as distributions_total
                FROM ville v
                LEFT JOIN (
                    SELECT idVille, COUNT(*) as total, 
                           SUM(CASE WHEN idStatus = 2 THEN 1 ELSE 0 END) as satisfaits
                    FROM besoin
                    GROUP BY idVille
                ) besoins ON v.id = besoins.idVille
                LEFT JOIN (
                    SELECT idVille, COUNT(*) as total
                    FROM distribution
                    GROUP BY idVille
                ) distributions ON v.id = distributions.idVille
                ORDER BY v.nom
            ";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return [];
        }
    }

    /**
     * Top produits les plus demandés
     * @param int $limit
     * @return array
     */
    public function getTopProduitsDemandes(int $limit = 10): array {
        try {
            $sql = "
                SELECT 
                    p.id,
                    p.nom,
                    SUM(b.quantite) as quantite_demandee,
                    COUNT(b.id) as nombre_besoins,
                    SUM(b.quantite * p.prixUnitaire) as valeur_totale
                FROM besoin b
                JOIN produit p ON b.idProduit = p.id
                GROUP BY p.id, p.nom
                ORDER BY quantite_demandee DESC
                LIMIT :limit
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return [];
        }
    }

    /**
     * Évolution des distributions sur les derniers mois
     * @param int $mois Nombre de mois à récupérer
     * @return array
     */
    public function getEvolutionDistributions(int $mois = 6): array {
        try {
            $sql = "
                SELECT 
                    DATE_FORMAT(dateDistribution, '%Y-%m') as mois,
                    COUNT(*) as nombre,
                    SUM(quantite) as quantite
                FROM distribution
                WHERE dateDistribution >= DATE_SUB(NOW(), INTERVAL :mois MONTH)
                GROUP BY DATE_FORMAT(dateDistribution, '%Y-%m')
                ORDER BY mois ASC
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':mois', $mois, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return [];
        }
    }

    /**
     * Récap financier (argent disponible, dépensé, etc.)
     * @return array
     */
    public function getRecapFinancier(): array {
        try {
            // Argent total reçu en dons
            $stmtRecu = $this->db->query("SELECT COALESCE(SUM(montant), 0) FROM don WHERE montant IS NOT NULL");
            $argentRecu = (float)$stmtRecu->fetchColumn();

            // Argent dépensé en achats
            $stmtDepense = $this->db->query("SELECT COALESCE(SUM(montant_total), 0) FROM achat");
            $argentDepense = (float)$stmtDepense->fetchColumn();

            // Frais totaux
            $stmtFrais = $this->db->query("SELECT COALESCE(SUM(frais_appliques), 0) FROM achat");
            $fraisTotal = (float)$stmtFrais->fetchColumn();

            // Argent restant disponible
            $argentRestant = $argentRecu - $argentDepense;

            return [
                'argent_recu' => $argentRecu,
                'argent_depense' => $argentDepense,
                'frais_total' => $fraisTotal,
                'argent_restant' => $argentRestant,
                'pourcentage_utilise' => $argentRecu > 0 ? round(($argentDepense / $argentRecu) * 100, 1) : 0,
            ];
        } catch (\PDOException $e) {
            return [
                'argent_recu' => 0,
                'argent_depense' => 0,
                'frais_total' => 0,
                'argent_restant' => 0,
                'pourcentage_utilise' => 0,
            ];
        }
    }

    /**
     * Données pour graphiques
     * @return array
     */
    public function getDonneesGraphiques(): array {
        return [
            'evolution_distributions' => $this->getEvolutionDistributions(),
            'top_produits' => $this->getTopProduitsDemandes(5),
            'repartition_par_ville' => $this->getStatistiquesParVille(),
        ];
    }
}
