<?php
/**
 * 
 * Activity: Validates input, fetches data from a source, normalizes it to JSON, handles errors gracefully.
 * 
 */

header('Content-Type: application/json');

// Validating Path to ensure no bottlenecks or missmatch
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (preg_match('#^/api/v1/content/([a-z0-9-]+)$#', $path, $matches)) {
    handleContentRequest($matches[1]);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint not found']);
    exit;
}
function handleContentRequest($type) {
    // Validate input
    if (!preg_match('/^[a-z0-9-]+$/', $type)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid content type']);
        exit;
    }
    $perPage = filter_input(INPUT_GET, 'per_page', FILTER_VALIDATE_INT) ?: 10;
    if ($perPage < 1 || $perPage > 50) {
        http_response_code(400);
        echo json_encode(['error' => 'Per page must be 1-50']);
        exit;
    }
    // Fetch from data source
    $data = getContentData($type, $perPage);

    if (empty($data)) {
        http_response_code(404);
        echo json_encode(['error' => 'No content found']);
        exit;
    }
    // Normalising using custom func and logging
    $normalized = normalizeContent($data);
    error_log("API: {$type}, returned " . count($normalized) . " items");
    echo json_encode($normalized);
}
function getContentData($type, $perPage) {
    // Sample data
    $sampleData = [
        ['id' => 1, 'title' => 'Sample Post 1', 'body' => 'Full content here...', 'date' => '2026-03-01T10:00:00Z'],
        ['id' => 2, 'title' => 'Sample Post 2', 'body' => 'Another entry...', 'date' => '2026-03-02T12:00:00Z'],
    ];
    return array_slice($sampleData, 0, $perPage);
}

function normalizeContent($items) {
    return array_map(function($item) {
        return [
            'id' => $item['id'],
            'title' => htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'),
            'excerpt' => substr(strip_tags($item['body']), 0, 100) . '...',
            'date' => $item['date']
        ];
    }, $items);
}
?>
