<?php
/*
  BACKEND - REGISTRO TÉCNICO (PHP)
  [R]ecibir: Capturar datos del formulario y el archivo.
  [V]alidar: Rechazar archivos > 20MB o formatos no permitidos.
  [G]uardar: Almacenar en /uploads/documents/ usando uniqid().
  [A]signar: Preparar arreglo con estado 'Pendiente' e isVerified = false.
  [B]loquear: Retornar respuesta exitosa para el mensaje de notificación.
*/

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $especialidad = $_POST['especialidad'] ?? '';
    $tarifa = $_POST['tarifa'] ?? '';
    $experiencia = $_POST['experiencia'] ?? '';
    
    if (!isset($_FILES['documento']) || $_FILES['documento']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(["error" => "No se recibió el archivo o hubo un error."]);
        exit;
    }

    $file = $_FILES['documento'];
    
    $maxSize = 20 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        http_response_code(400);
        echo json_encode(["error" => "El archivo excede los 20MB."]);
        exit;
    }

    $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($extension, $allowedExtensions)) {
        http_response_code(400);
        echo json_encode(["error" => "Formato no permitido."]);
        exit;
    }

    $uploadDir = __DIR__ . '/uploads/documents/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $uniqueId = uniqid('doc_', true);
    $fileName = $uniqueId . '.' . $extension;
    $destination = $uploadDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        
        $nuevoTecnico = [
            "especialidad" => $especialidad,
            "tarifa" => $tarifa,
            "experiencia" => $experiencia,
            "documento" => $fileName,
            "estado" => "Pendiente",
            "isVerified" => false
        ];

        echo json_encode([
            "mensaje" => "Registro exitoso", 
            "tecnico" => $nuevoTecnico
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Error al mover el archivo al servidor."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido."]);
}
?>