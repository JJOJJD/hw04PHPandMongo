<?php
$rootPath = __DIR__ . "/../../";
require_once $rootPath . "vendor/autoload.php";
if (file_exists($rootPath . ".env")) {
    $dotenv = Dotenv\Dotenv::createImmutable($rootPath);
    $dotenv->load();
}
$mongoUri = $_ENV["MONGODB_URI"] ?? getenv("MONGODB_URI");
if (!$mongoUri) {
    http_response_code(500);
    die("Configuration error: MONGODB_URI is not defined.");
}
$databaseName   = "students";
$collectionName = "Customer";
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    die("Method not allowed.");
}
function sanitize(string $value): string {
    return htmlspecialchars(trim($value));
}
$fullName           = sanitize($_POST["full_name"]           ?? "");
$idNumber           = sanitize($_POST["id_number"]           ?? "");
$birthDate          = sanitize($_POST["birth_date"]          ?? "");
$educationalLevel   = sanitize($_POST["educational_level"]   ?? "");
$grade              = sanitize($_POST["grade"]               ?? "");
$hasAllergies       = sanitize($_POST["has_allergies"]       ?? "no");
$medicalDescription = sanitize($_POST["medical_description"] ?? "");
if (!$fullName || !$idNumber || !$birthDate || !$educationalLevel || !$grade) {
    http_response_code(400);
    die("Please fill in all required fields.");
}
use MongoDB\Client;
try {
    $client     = new Client($mongoUri);
    $collection = $client->selectCollection($databaseName, $collectionName);
    $document = [
        "full_name"           => $fullName,
        "id_number"           => $idNumber,
        "birth_date"          => $birthDate,
        "educational_level"   => $educationalLevel,
        "grade"               => $grade,
        "has_allergies"       => $hasAllergies,
        "medical_description" => $medicalDescription,
        "registered_at"       => new MongoDB\BSON\UTCDateTime(),
    ];
    $result = $collection->insertOne($document);
    if ($result->getInsertedCount() === 1) {
        header("Location: ../index.html?status=success");
        exit;
    }
    throw new Exception("Document could not be inserted.");
} catch (Exception $error) {
    http_response_code(500);
    echo "<p style='color:red;font-family:Arial'>Error saving record: " . htmlspecialchars($error->getMessage()) . "</p>";
}
