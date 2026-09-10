<?php
// =====================================================
// config/fine-config.php
// Central location for the fine rule.
// Reads from .env file.
// =====================================================

require_once __DIR__ . '/env.php';

$finePerDay = (int)env('FINE_PER_DAY', '10');
