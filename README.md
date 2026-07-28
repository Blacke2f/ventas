# AbasPOS — Sistema de Punto de Venta para Abastos 🇻🇪

Sistema POS para abastos y tiendas de abarrotes venezolanas.  
Maneja precios en USD con conversión automática a Bs usando la tasa BCV.

---

## Inicio Rápido

### Opción A — Laragon / XAMPP (local)

1. Colocar la carpeta en `c:\laragon\www\Sistema de venta\`
2. Abrir: `http://localhost/Sistema de venta/install-complete.php`
3. Acceder: `http://localhost/Sistema de venta/`

### Opción B — Docker (compartir en red local)

```bash
# Construir e iniciar
docker compose up -d --build

# Verificar que MySQL esté listo
# Abrir: http://TU_IP:8080/utils/wait-for-db.php

# Instalar la base de datos
# Abrir: http://TU_IP:8080/install-complete.php

# Acceder al sistema
# Abrir: http://TU_IP:8080/
```

**Tu IP local:** abre CMD y ejecuta `ipconfig` → busca `IPv4`  
**Ejemplo:** `http://192.168.1.5:8080/`

### Credenciales por defecto

| Usuario | Contraseña | Rol |
|---------|-----------|-----|
| `admin` | `password` | Administrador |
| `cajero1` | `password` | Cajero |

> ⚠️ Cambiar contraseñas antes de usar en producción.

---

## Solución de Problemas Docker

### "Error del servidor" al hacer login

1. Verifica que MySQL está listo:  
   `http://TU_IP:8080/utils/wait-for-db.php`

2. Si dice "BD no existe", ejecuta el instalador:  
   `http://TU_IP:8080/install-complete.php`

3. Ver URL detectada:  
   `http://TU_IP:8080/debug-url.php`

### Reiniciar Docker limpio

```bash
docker compose down -v    # elimina volúmenes
docker compose up -d --build
```

---

## Documentación Completa

Ver **DOCUMENTACION.md** para la guía técnica completa.

---

*AbasPOS v1.0.0 · PHP 8 + MySQL + Bootstrap 5*
