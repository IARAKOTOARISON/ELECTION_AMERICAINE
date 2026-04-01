<?php
// Compatibility wrapper: include the `besoinRestant.php` view created earlier.
// This file exists because some controllers/render calls expect the name
// `besoinsRestantsPourAchat`. We delegate to the canonical `besoinRestant`.
require __DIR__ . '/besoinRestant.php';
