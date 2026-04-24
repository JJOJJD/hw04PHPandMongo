<?php
// guardar.php está en public/php/
// vendor/ y .env están en la raíz del proyecto (dos niveles arriba)
$root = __DIR__ . "/../../";

require_once $root . "vendor/autoload.php"; // Composer

// =============================================
//  Cargar .env en entorno local (si existe).
//  En Render las variables ya están en el sistema.
// =============================================
if (file_exists($root . ".env")) {
    $dotenv = Dotenv\Dotenv::createImmutable($root);
    $dotenv->load();
}

// =============================================
//  Leer el URI de MongoDB desde el entorno
// =============================================
$mongoHost = $_ENV["MONGODB_URI"] ?? getenv("MONGODB_URI");

if (!$mongoHost) {
    http_response_code(500);
    die("Error de configuración: la variable MONGODB_URI no está definida.");
}

$mongoDB  = "students";   // Base de datos en Atlas
$mongoCol = "Customer";   // Colección en Atlas

// =============================================
//  Solo procesar si el método es POST
// =============================================
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    die("Método no permitido.");
}

// =============================================
//  Recoger y limpiar los datos del formulario
// =============================================
function limpiar(string $valor): string {
    return htmlspecialchars(trim($valor));
}

$nombres          = limpiar($_POST["nombres"]          ?? "");
$apellidos        = limpiar($_POST["apellidos"]        ?? "");
$cedula           = limpiar($_POST["cedula"]           ?? "");
$correo           = limpiar($_POST["correo"]           ?? "");
$fecha_nacimiento = limpiar($_POST["fecha_nacimiento"] ?? "");
$carrera          = limpiar($_POST["carrera"]          ?? "");
$semestre         = (int) ($_POST["semestre"]          ?? 0);
$modalidad        = limpiar($_POST["modalidad"]        ?? "");
$observaciones    = limpiar($_POST["observaciones"]    ?? "");

// Validación básica de campos requeridos
if (!$nombres || !$apellidos || !$cedula || !$correo || !$fecha_nacimiento || !$carrera || !$semestre || !$modalidad) {
    http_response_code(400);
    die("Por favor completa todos los campos requeridos.");
}

use MongoDB\Client;

try {
    $client     = new Client($mongoHost);
    $collection = $client->students->Customer;

    // Documento a insertar
    $documento = [
        "nombres"          => $nombres,
        "apellidos"        => $apellidos,
        "cedula"           => $cedula,
        "correo"           => $correo,
        "fecha_nacimiento" => $fecha_nacimiento,
        "carrera"          => $carrera,
        "semestre"         => $semestre,
        "modalidad"        => $modalidad,
        "observaciones"    => $observaciones,
        "fecha_registro"   => new MongoDB\BSON\UTCDateTime(), // fecha actual
    ];

    $resultado = $collection->insertOne($documento);

    if ($resultado->getInsertedCount() === 1) {
        // Redirigir de vuelta al formulario con mensaje de éxito
        header("Location: ../index.html?estado=ok");
        exit;
    } else {
        throw new Exception("No se pudo insertar el documento.");
    }

} catch (Exception $e) {
    http_response_code(500);
    echo "<p style='color:red;font-family:Arial'>Error al guardar: " . htmlspecialchars($e->getMessage()) . "</p>";
}
