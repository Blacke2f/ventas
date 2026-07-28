<?php
/**
 * ===================================
 * Utils - Funciones de utilidad
 * ===================================
 */

class Utils {
    /**
     * Formatear número como moneda
     */
    public static function formatCurrency($amount, $decimals = 2) {
        $symbol = defined('CURRENCY_SYMBOL') ? CURRENCY_SYMBOL : 'S/.';
        return $symbol . ' ' . number_format($amount, $decimals, '.', ',');
    }

    /**
     * Formatear fecha
     */
    public static function formatDate($date, $format = 'd/m/Y') {
        if (empty($date)) {
            return '-';
        }
        
        $timestamp = is_numeric($date) ? $date : strtotime($date);
        return date($format, $timestamp);
    }

    /**
     * Formatear fecha y hora
     */
    public static function formatDateTime($datetime, $format = 'd/m/Y H:i') {
        if (empty($datetime)) {
            return '-';
        }
        
        $timestamp = is_numeric($datetime) ? $datetime : strtotime($datetime);
        return date($format, $timestamp);
    }

    /**
     * Calcular porcentaje
     */
    public static function percentage($value, $total) {
        if ($total == 0) {
            return 0;
        }
        return round(($value / $total) * 100, 2);
    }

    /**
     * Generar número aleatorio único
     */
    public static function generateUniqueNumber($prefix = '', $length = 8) {
        $number = str_pad(mt_rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
        return $prefix . $number;
    }

    /**
     * Validar email
     */
    public static function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Sanitizar string
     */
    public static function sanitize($string) {
        return htmlspecialchars(strip_tags(trim($string)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Formatear número de teléfono
     */
    public static function formatPhone($phone) {
        // Eliminar caracteres no numéricos
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($phone) == 9) {
            return substr($phone, 0, 3) . '-' . substr($phone, 3, 3) . '-' . substr($phone, 6);
        }
        
        return $phone;
    }

    /**
     * Calcular días entre fechas
     */
    public static function daysBetween($date1, $date2 = null) {
        $date2 = $date2 ?? date('Y-m-d');
        
        $timestamp1 = is_numeric($date1) ? $date1 : strtotime($date1);
        $timestamp2 = is_numeric($date2) ? $date2 : strtotime($date2);
        
        $diff = abs($timestamp2 - $timestamp1);
        return floor($diff / (60 * 60 * 24));
    }

    /**
     * Obtener badge de estado
     */
    public static function getStatusBadge($status) {
        $badges = [
            'activo' => '<span class="badge bg-success">Activo</span>',
            'inactivo' => '<span class="badge bg-secondary">Inactivo</span>',
            'pendiente' => '<span class="badge bg-warning">Pendiente</span>',
            'pagado' => '<span class="badge bg-success">Pagado</span>',
            'pagada' => '<span class="badge bg-success">Pagada</span>',
            'cancelado' => '<span class="badge bg-danger">Cancelado</span>',
            'cancelada' => '<span class="badge bg-danger">Cancelada</span>',
            'vencido' => '<span class="badge bg-danger">Vencido</span>',
            'parcial' => '<span class="badge bg-info">Parcial</span>',
        ];
        
        return $badges[$status] ?? '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
    }

    /**
     * Truncar texto
     */
    public static function truncate($text, $length = 100, $suffix = '...') {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        return substr($text, 0, $length) . $suffix;
    }

    /**
     * Generar código de barras aleatorio
     */
    public static function generateBarcode($prefix = 'PRD') {
        return $prefix . str_pad(mt_rand(0, 999999999), 9, '0', STR_PAD_LEFT);
    }

    /**
     * Validar RUC/DNI
     */
    public static function isValidDocument($document, $type = 'DNI') {
        $document = preg_replace('/[^0-9]/', '', $document);
        
        if ($type === 'DNI') {
            return strlen($document) == 8;
        } elseif ($type === 'RUC') {
            return strlen($document) == 11;
        }
        
        return false;
    }

    /**
     * Convertir a slug
     */
    public static function slugify($text) {
        // Reemplazar caracteres especiales
        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
        $text = trim($text, '-');
        $text = strtolower($text);
        
        return $text;
    }

    /**
     * Obtener saludo según hora del día
     */
    public static function getGreeting() {
        $hour = date('H');
        
        if ($hour >= 0 && $hour < 12) {
            return 'Buenos días';
        } elseif ($hour >= 12 && $hour < 18) {
            return 'Buenas tardes';
        } else {
            return 'Buenas noches';
        }
    }

    /**
     * Formatear tamaño de archivo
     */
    public static function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Validar fortaleza de contraseña
     */
    public static function validatePasswordStrength($password) {
        $strength = 0;
        
        if (strlen($password) >= 8) $strength++;
        if (preg_match('/[a-z]/', $password)) $strength++;
        if (preg_match('/[A-Z]/', $password)) $strength++;
        if (preg_match('/[0-9]/', $password)) $strength++;
        if (preg_match('/[^a-zA-Z0-9]/', $password)) $strength++;
        
        return $strength;
    }

    /**
     * Generar color aleatorio
     */
    public static function randomColor() {
        return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
    }

    /**
     * Array de colores predefinidos
     */
    public static function getChartColors() {
        return [
            '#667eea', '#764ba2', '#f093fb', '#4facfe',
            '#43e97b', '#38f9d7', '#fa709a', '#fee140',
            '#30cfd0', '#330867', '#a8edea', '#fed6e3'
        ];
    }
}
?>
