<?php
// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

/**
 * Standalone PHP REST API - Content Fetcher
 * GET /api.php/content/{type}?per_page=5
 */

// Parse request
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (preg_match('#^/api\.php/content/([a-z0-9-]+)$#', $path, $matches)) {
    $type = $matches[1];
    
    // Validate
    if (!preg_match('/^[a-z0-9-]+$/', $type)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid content type']);
        exit;
    }
    
    $perPage = filter_input(INPUT_GET, 'per_page', FILTER_VALIDATE_INT) ?: 5;
    if ($perPage < 1 || $perPage > 20) {
        http_response_code(400);
        echo json_encode(['error' => 'Per page 1-20']);
        exit;
    }
    
    // Fetch & normalize
    $data = getContentData($type, $perPage);
    if (empty($data)) {
        http_response_code(404);
        echo json_encode(['error' => 'No content']);
        exit;
    }
    
    error_log("API: $type, " . count($data) . " items");
    echo json_encode($data);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Use: /api.php/content/articles']);
}

function getContentData($type, $perPage) {
    // Demo data
    $content = [
        ['id' => 1, 'title' => 'Latest Article', 'excerpt' => 'This is a sample article about web development...', 'date' => '2026-03-13'],
        ['id' => 2, 'title' => 'PHP Tips', 'excerpt' => 'Best practices for modern PHP APIs...', 'date' => '2026-03-12'],
        ['id' => 3, 'title' => 'REST Design', 'excerpt' => 'Building clean, scalable APIs...', 'date' => '2026-03-11']
    ];
    return array_slice($content, 0, $perPage);
}
?>
