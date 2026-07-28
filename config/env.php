<?php
/**
 * Variables de entorno para Docker
 * Se cargan antes que config.php
 */

// Si se usa Docker, las variables deben pasarse a través de docker-compose.yml
// No forzaremos putenv() aquí para no romper el entorno de Laragon.
?>
