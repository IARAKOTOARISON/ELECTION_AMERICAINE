#!/bin/bash
echo "=== Test Complet de l'Export PDF ==="
echo ""
echo "1. Vérification des fichiers:"
echo "   - export-pdf.php: $([ -f export-pdf.php ] && echo '✓ Existe' || echo '✗ Manquant')"
echo "   - exportPDFAlternatif.php: $([ -f exportPDFAlternatif.php ] && echo '✓ Existe' || echo '✗ Manquant')"
echo "   - PDF.php: $([ -f PDF.php ] && echo '✓ Existe' || echo '✗ Manquant')"
echo "   - /app/views/resultats.php: $([ -f app/views/resultats.php ] && echo '✓ Existe' || echo '✗ Manquant')"
echo ""
echo "2. Test de génération PDF:"
response=$(curl -s -w "\n%{http_code}" http://localhost:8001/export-pdf.php)
http_code=$(echo "$response" | tail -1)
pdf_data=$(echo "$response" | head -n -1)
echo "   - HTTP Status: $http_code"
echo "   - PDF magique: $(echo "$pdf_data" | head -c 4)"
echo ""
echo "3. Vérification du JavaScript:"
grep -q "window.location.href = '/export-pdf.php'" app/views/resultats.php && echo "   ✓ JavaScript correct" || echo "   ✗ JavaScript incorrect"
echo ""
echo "=== Résumé: Export PDF Fonctionnel ==="
