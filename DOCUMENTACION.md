# AbasPOS — Documentación Completa

> Sistema de Punto de Venta para Abastos y Tiendas de Abarrotes venezolanas 🇻🇪  
> Versión 1.0.0 · PHP 8+ · MySQL · Bootstrap 5 · Laragon

---

## Tabla de Contenidos

1. [Descripción del Sistema](#1-descripción-del-sistema)
2. [Instalación](#2-instalación)
3. [Estructura del Proyecto](#3-estructura-del-proyecto)
4. [Base de Datos](#4-base-de-datos)
5. [Módulos y Funcionalidades](#5-módulos-y-funcionalidades)
6. [Sistema de Moneda Dual USD/Bs](#6-sistema-de-moneda-dual-usdbs)
7. [Calculadora de Precios](#7-calculadora-de-precios)
8. [APIs Disponibles](#8-apis-disponibles)
9. [Configuración del Sistema](#9-configuración-del-sistema)
10. [Seguridad](#10-seguridad)
11. [Solución de Problemas](#11-solución-de-problemas)
12. [Credenciales por Defecto](#12-credenciales-por-defecto)

---

## 1. Descripción del Sistema

**AbasPOS** es un sistema de punto de venta diseñado específicamente para abastos y tiendas de abarrotes venezolanas. Maneja precios en dólares (USD) con conversión automática a bolívares (Bs) usando la tasa oficial del BCV.

### Características principales

| Característica | Detalle |
|---|---|
| **Idioma** | Español venezolano |
| **Moneda base** | USD (dólar) |
| **Conversión** | Automática USD → Bs (BCV) |
| **Inventario** | 150+ productos, 15 categorías |
| **Pagos** | Efectivo, tarjeta, fiado (crédito) |
| **Roles** | Admin, Cajero |
| **Diseño** | Bootstrap 5, responsive, móvil |
| **Backend** | PHP 8, PDO, MVC |
| **Base de Datos** | MySQL (`abastospos`) |

---

## 2. Instalación

### Requisitos

- **Laragon** (o XAMPP/WAMP) con PHP 8.0+ y MySQL 8.0+
- Módulo `curl` habilitado en PHP (para la tasa BCV)
- Acceso a internet (para obtener la tasa automática)

### Pasos

#### Paso 1 — Instalación automática
```
http://localhost/Sistema de venta/install-complete.php
```
Este script:
- Crea la base de datos `abastospos`
- Crea las 9 tablas necesarias
- Inserta 2 usuarios (admin + cajero)
- Inserta 15 categorías de productos
- Inserta 150+ productos venezolanos
- Configura contraseñas a `password`

#### Paso 2 — Verificar instalación
```
http://localhost/Sistema de venta/utils/verificar-datos.php
```
Debe mostrar: 2 usuarios, 15 categorías, 150+ productos, 3 clientes de ejemplo.

#### Paso 3 — Ingresar al sistema
```
http://localhost/Sistema de venta/
```

**Credenciales:**
- Admin: `admin` / `password`
- Cajero: `cajero1` / `password`

> **⚠️ Importante:** Cambiar las contraseñas antes de usar en producción.

---

## 3. Estructura del Proyecto

```
Sistema de venta/
├── config/
│   ├── config.php              ← Configuración global (DB, APP_NAME, APP_URL)
│   └── Database.php            ← Conexión PDO Singleton
│
├── controllers/
│   ├── AuthController.php      ← Login, logout, register, permisos
│   ├── ProductosController.php ← CRUD productos + categorías
│   ├── ClientesController.php  ← CRUD clientes
│   ├── VentasController.php    ← Crear venta atómica, cancelar, reportes
│   └── CreditosController.php  ← Fiados, abonos, cartera
│
├── models/
│   ├── BaseModel.php           ← PDO base (findAll, insert, update, delete)
│   ├── Usuario.php             ← Autenticación, hash bcrypt
│   ├── Producto.php            ← Inventario, calculadora precio
│   ├── Cliente.php             ← Límites de crédito, saldos
│   ├── Venta.php               ← Transacción atómica, ganancias, inventario
│   ├── Credito.php             ← Sistema de fiados y abonos
│   ├── TasaCambio.php          ← API BCV con cache 1 hora
│   └── Utils.php               ← formatCurrency, helpers
│
├── views/
│   ├── layouts/main.php        ← Layout principal Bootstrap 5
│   ├── includes/
│   │   ├── sidebar.php         ← Menú lateral con scroll
│   │   └── topbar.php          ← Barra superior con perfil
│   ├── auth/login.php          ← Pantalla de inicio de sesión
│   ├── dashboard/index.php     ← Métricas, tasa BCV, stock bajo
│   ├── pos/index.php           ← Punto de Venta con carrito
│   ├── productos/index.php     ← CRUD + calculadora de precios
│   ├── clientes/index.php      ← CRUD + historial + cobrar deuda
│   ├── ventas/index.php        ← Historial con filtros + cancelar
│   ├── creditos/index.php      ← Fiados + abonos (todos los estados)
│   ├── reportes/index.php      ← Ventas, ganancias, inventario, CSV
│   ├── configuracion/index.php ← Usuarios, categorías, limpieza BD
│   └── manual/index.php        ← Manual de uso integrado
│
├── public/
│   ├── css/styles.css          ← Estilos personalizados
│   └── js/
│       ├── api-client.js       ← Cliente HTTP fetch con aplanamiento
│       ├── utils.js            ← Helpers JS globales
│       ├── tasa-cambio.js      ← Conversión USD↔Bs en tiempo real
│       ├── app.js              ← Inicialización global (AbasPOS class)
│       ├── productos.js        ← ProductosAPI
│       ├── clientes.js         ← ClientesAPI
│       ├── ventas.js           ← VentasAPI
│       ├── creditos.js         ← CreditosAPI
│       └── pos.js              ← Lógica completa del POS
│
├── database/
│   └── schema.sql              ← SQL completo con 150+ productos
│
├── cache/
│   └── tasa_cambio.json        ← Cache de tasa BCV (auto-generado)
│
├── utils/
│   ├── verificar-datos.php     ← Diagnóstico de la BD
│   ├── limpiar-datos.php       ← API de limpieza (requiere admin)
│   ├── actualizar-tasa.php     ← Actualización manual de tasa BCV
│   ├── setup-database.php      ← Setup alternativo
│   └── diagnostico.php         ← Diagnóstico general
│
├── install-complete.php        ← Instalador con UI
├── reset-password.php          ← Reset de contraseñas
├── api-login.php               ← Endpoint de autenticación
├── api-tasa-cambio.php         ← Endpoint de tasa BCV
├── api-calcular-precio.php     ← Endpoint calculadora precios
├── index.php                   ← Router principal
└── .htaccess                   ← Rewrite rules Apache
```

---

## 4. Base de Datos

**Nombre:** `abastospos`

### Tablas

| Tabla | Descripción | Registros iniciales |
|---|---|---|
| `usuarios` | Administradores y cajeros | 2 |
| `clientes` | Clientes con límites de crédito | 3 (ejemplo) |
| `categorias` | 15 categorías de productos | 15 |
| `productos` | Inventario con precio USD | 120+ |
| `ventas` | Encabezado de cada venta | — |
| `detalle_ventas` | Ítems de cada venta | — |
| `creditos` | Registro de ventas a fiado | — |
| `abonos` | Pagos parciales a créditos | — |
| `auditoria` | Log de operaciones | — |

### Vistas SQL incluidas

- `v_resumen_ventas_diarias` — ventas agrupadas por día
- `v_creditos_vencidos` — créditos fuera de plazo
- `v_clientes_con_deuda` — clientes con saldo > 0

### Campos importantes de `productos`

| Campo | Tipo | Descripción |
|---|---|---|
| `precio_costo` | DECIMAL(10,2) | Precio de compra/costo unitario |
| `porcentaje_ganancia` | DECIMAL(5,2) | % de ganancia aplicado |
| `precio_venta` | DECIMAL(10,2) | Precio de venta al público (USD) |
| `precio_mayoreo` | DECIMAL(10,2) | Precio del bulto/paquete |
| `unidades_por_bulto` | INT | Cantidad de unidades por bulto |
| `stock_actual` | INT | Unidades disponibles |
| `stock_minimo` | INT | Umbral para alerta de stock bajo |

---

## 5. Módulos y Funcionalidades

### 5.1 Dashboard

- 4 métricas en tiempo real: ventas hoy, total vendido (USD+Bs), créditos vencidos, clientes con deuda
- Comparativa vs ayer (`+N vs ayer`)
- Alerta de stock bajo con badges de productos afectados
- Tasa BCV prominente con botón actualizar
- Tabla de últimas ventas del día en USD y Bs

### 5.2 Punto de Venta (POS)

- Grid de productos con iconos, precio USD y Bs
- Filtros por categoría (15 categorías)
- Buscador en tiempo real (debounce 280ms)
- Carrito con +/− por ítem, descuento en porcentaje
- 3 métodos de pago: Efectivo, Tarjeta, Fiado
- **Transacción atómica:** stock validado y descontado en una sola operación SQL con `BEGIN/COMMIT/ROLLBACK`
- Anti-doble-click: botones deshabilitados durante procesamiento
- Ticket de venta en modal con opción imprimir
- Carrito con total en USD y Bs actualizado en tiempo real

### 5.3 Productos (solo Admin)

- CRUD completo con calculadora de precio integrada
- **Calculadora Modo Bulto:** precio bulto ÷ unidades + % ganancia = precio unitario
- **Calculadora Modo Costo:** precio costo + % ganancia = precio venta
- Preview en tiempo real de USD y Bs
- Ajuste de stock (agregar/restar) con modal
- Tab de "Stock Bajo" con lista de productos críticos
- Búsqueda instantánea y filtro por categoría

### 5.4 Clientes

- CRUD con límites de fiado por cliente
- Historial de compras por cliente (modal con todas sus ventas)
- Botón "Cobrar Deuda" directo desde la lista
- Saldo en USD y Bs con barra de progreso del límite
- Búsqueda en tiempo real

### 5.5 Créditos / Fiados

- Tab "Todos los créditos": activos, parciales y vencidos — todos con botón "Abonar"
- Tab "Vencidos": lista de clientes que no pagaron a tiempo
- Tab "Resumen": estadísticas de cartera
- Modal de abono: monto editable, equivalente Bs en tiempo real, 3 métodos de pago
- **Sin restricción de plazo:** se puede abonar aunque el crédito no esté vencido

### 5.6 Historial de Ventas

- Filtros: rango de fechas, tipo de pago, cajero (solo admin)
- Cards de resumen: total ventas, USD, Bs, promedio
- Detalle de venta en modal con ganancia bruta
- **Cancelar venta** (solo admin): restaura stock automáticamente
- Exportar a CSV

### 5.7 Reportes

| Pestaña | Contenido | Exportable |
|---|---|---|
| Ventas | Lista con totales por tipo de pago | ✅ CSV |
| Ganancias | Ingresos, costos y margen por día | — |
| Productos | Top 30 más vendidos por transacciones y unidades | — |
| Inventario | Valor del stock a costo y a precio venta | ✅ CSV |
| Deudas | Clientes con deuda y % de límite usado | — |

### 5.8 Configuración (solo Admin)

- Crear nuevos usuarios (admin/cajero)
- Crear categorías de productos
- Información del sistema y base de datos
- Herramientas: verificar BD, actualizar tasa, resetear contraseñas
- **Limpiar datos de prueba:** elimina ventas, clientes, productos — conserva usuarios y categorías
- **Nombre del sistema configurable** (ver sección 9)

### 5.9 Manual de Uso

Accesible desde el menú de perfil (arriba a la derecha). Cubre:
1. Login y tipos de usuario
2. Dashboard
3. Punto de Venta paso a paso
4. Gestión de productos
5. Clientes
6. Créditos y fiados
7. Historial de ventas
8. Reportes
9. Configuración
10. Tasa de cambio
11. Consejos y buenas prácticas

---

## 6. Sistema de Moneda Dual USD/Bs

### Cómo funciona

1. Todos los precios se **almacenan en USD** en la base de datos
2. Al mostrar en pantalla, se multiplica por la tasa BCV actual
3. La tasa se obtiene de `https://pydolarvenezuela-api.vercel.app/api/v1/dollar?page=bcv`
4. Se almacena en caché por **1 hora** en `/cache/tasa_cambio.json`
5. Si la API falla, usa la última tasa en caché o el valor por defecto

### Flujo de la tasa

```
API BCV → cache JSON (1h) → JavaScript (tasa-cambio.js) → UI
                         ↘ Fallback: última tasa guardada
                                    ↘ Fallback final: 563.29
```

### Actualizar manualmente

```
http://localhost/Sistema de venta/utils/actualizar-tasa.php
```

O desde **Configuración → Herramientas → Actualizar Tasa de Cambio**

---

## 7. Calculadora de Precios

### Modo 1: Desde Bulto

```
Precio del bulto: $17.00
Unidades: 20
Porcentaje de ganancia: 30%

Costo unitario = 17.00 / 20 = $0.85
Ganancia = $0.85 × 30% = $0.255
Precio de venta = $0.85 + $0.255 = $1.105 → $1.11
```

### Modo 2: Desde Costo Directo

```
Precio de costo: $1.20
Porcentaje de ganancia: 25%

Ganancia = $1.20 × 25% = $0.30
Precio de venta = $1.20 + $0.30 = $1.50
```

### Endpoint API

```
POST /api-calcular-precio.php
{
  "modo": "bulto",
  "precio_mayoreo": 17.00,
  "unidades_por_bulto": 20,
  "porcentaje_ganancia": 30
}
```

---

## 8. APIs Disponibles

Todas las APIs requieren sesión autenticada excepto donde se indica.

### Autenticación
```
POST   /api-login.php              → Login (público)
GET    /api/auth/profile           → Perfil del usuario actual
POST   /api/auth/logout            → Cerrar sesión
POST   /api/auth/register          → Crear usuario (solo admin)
```

### Productos
```
GET    /api/productos?action=list               → Todos los activos
GET    /api/productos?action=get&id=N           → Uno con estadísticas
GET    /api/productos?action=search&q=texto     → Buscar por nombre
GET    /api/productos?action=categoria&id=N     → Por categoría
GET    /api/productos?action=categorias         → Listar categorías
GET    /api/productos?action=grid               → Para el POS (paginado)
GET    /api/productos?action=stock-bajo         → Con stock crítico
GET    /api/productos?action=top-vendidos       → Más vendidos
POST   /api/productos?action=create             → Crear (admin)
PUT    /api/productos?action=update&id=N        → Editar (admin)
PUT    /api/productos?action=stock&id=N         → Ajustar stock (admin)
DELETE /api/productos?action=delete&id=N        → Desactivar (admin)
```

### Clientes
```
GET    /api/clientes?action=list                → Todos los activos
GET    /api/clientes?action=get&id=N            → Uno con info básica
GET    /api/clientes?action=search&q=texto      → Buscar
GET    /api/clientes?action=detalles&id=N       → Detalles + historial
GET    /api/clientes?action=con-deuda           → Con saldo > 0
POST   /api/clientes?action=create              → Crear
PUT    /api/clientes?action=update&id=N         → Editar
```

### Ventas
```
GET    /api/ventas?action=hoy                   → Ventas del día
GET    /api/ventas?action=rango&inicio=&fin=    → Por rango + filtros
GET    /api/ventas?action=get&id=N              → Detalle con ítems
GET    /api/ventas?action=cliente&id=N          → Por cliente
GET    /api/ventas?action=ganancias&inicio=&fin= → Reporte de ganancias
GET    /api/ventas?action=inventario            → Inventario valorado
POST   /api/ventas?action=create                → Crear venta (atómica con items)
PUT    /api/ventas?action=cancel&id=N           → Cancelar + restaurar stock
```

### Créditos
```
GET    /api/creditos?action=vencidos            → Créditos vencidos
GET    /api/creditos?action=cliente&id=N        → Créditos de un cliente
GET    /api/creditos?action=estadisticas        → Resumen general
GET    /api/creditos?action=cartera             → Por categoría (activo/parcial/vencido)
POST   /api/creditos?action=create              → Crear crédito
POST   /api/creditos?action=abono               → Registrar abono
```

### Tasa y Precios
```
GET    /api-tasa-cambio.php                     → Tasa BCV actual
GET    /api-tasa-cambio.php?force=1             → Forzar actualización
POST   /api-calcular-precio.php                 → Calcular precio con ganancia
```

### Formato de respuesta

Todos los endpoints retornan:
```json
{
  "success": true,
  "message": "Descripción",
  "data": { ... }
}
```

El `api-client.js` aplana automáticamente la respuesta, por lo que en JavaScript `r.data` apunta directamente al payload (no a `r.data.data`).

---

## 9. Configuración del Sistema

### Cambiar el nombre del sistema

Edita el archivo `config/config.php`:

```php
define('APP_NAME', 'AbasPOS');        // ← Cambia esto
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/Sistema de venta');
define('APP_DESCRIPTION', 'Sistema de Punto de Venta para Abastos');
```

El nombre aparece en:
- El título del navegador (`APP_NAME · Página`)
- El logo del sidebar (se puede cambiar en `views/includes/sidebar.php`)
- El ticket de venta
- El login

### Cambiar el logo del sidebar

En `views/includes/sidebar.php`, línea del brand:
```html
<div class="brand-icon"><i class="fas fa-store"></i></div>
<div class="brand-name">AbasPOS</div>
```

Cambia `fa-store` por cualquier icono de [Font Awesome](https://fontawesome.com/icons) y `AbasPOS` por el nombre deseado.

### Cambiar la URL base

En `config/config.php`:
```php
define('APP_URL', 'http://localhost/Sistema de venta');
// o para producción:
define('APP_URL', 'https://tudominio.com');
```

Y en `.htaccess`:
```apache
RewriteBase /Sistema%20de%20venta/
# cambia a:
RewriteBase /tuCarpeta/
```

### Cambiar la zona horaria

En `config/config.php`:
```php
date_default_timezone_set('America/Caracas');
```

### Configurar tasa BCV por defecto

En `models/TasaCambio.php` y `public/js/tasa-cambio.js`:
```php
return 563.29; // ← Actualizar periódicamente
```
```javascript
this.tasa = 563.29; // ← Mismo valor
```

---

## 10. Seguridad

| Medida | Implementación |
|---|---|
| Contraseñas | bcrypt con `password_hash()` / `password_verify()` |
| SQL Injection | PDO Prepared Statements en todos los modelos |
| Sesiones | `session_set_cookie_params` con `httponly=true`, `samesite=Lax` |
| Roles | Verificación en controladores y vistas |
| CSRF | Headers validados en APIs |
| XSS | `htmlspecialchars()` en salidas PHP |
| Archivos sensibles | `.htaccess` bloquea `.env`, `.sql`, `.log`, `.json` |

### Recomendaciones para producción

1. Cambiar todas las contraseñas por defecto
2. Activar HTTPS
3. Cambiar `DEBUG_MODE` a `false` en `config.php`
4. Configurar backups automáticos de la BD
5. Revisar permisos de la carpeta `cache/` (escritura necesaria)
6. Crear un usuario cajero específico para cada empleado

---

## 11. Solución de Problemas

### El sistema no carga / Error 500
```
# 1. Verificar que Laragon está corriendo (Apache + MySQL)
# 2. Ejecutar el instalador:
http://localhost/Sistema de venta/install-complete.php

# 3. Si persiste, verificar PHP errors:
C:\laragon\tmp\php_errors.log
```

### "Table doesn't exist"
```
# La base de datos no está instalada. Ejecutar:
http://localhost/Sistema de venta/install-complete.php
```

### "Usuario o contraseña incorrectos"
```
# Resetear contraseñas:
http://localhost/Sistema de venta/reset-password.php
# Credenciales por defecto: admin / password
```

### Los productos no se muestran
```
# 1. Verificar datos en BD:
http://localhost/Sistema de venta/utils/verificar-datos.php

# 2. Si hay 0 productos, ejecutar instalador
# 3. Abrir consola del navegador (F12) y buscar errores de red
```

### La tasa BCV muestra valor antiguo
```
# Limpiar caché del navegador: Ctrl + Shift + R
# O actualizar manualmente:
http://localhost/Sistema de venta/utils/actualizar-tasa.php
```

### Error "Cannot declare class AuthController"
```
# Causa: archivos se cargan dos veces
# Solución: verificar que index.php usa require_once para todos los archivos
```

### El menú no aparece en móvil
```
# Asegurarse de que el botón hamburguesa tiene la clase .sb-toggle-btn
# Verificar que el JS de toggleSidebar() está en el layout
```

### Las ventas no se registran
```
# Abrir consola (F12) → pestaña Network
# Buscar la petición POST a /api/ventas?action=create
# Revisar la respuesta: error de stock, o error de conexión
```

---

## 12. Credenciales por Defecto

| Usuario | Contraseña | Rol | Acceso |
|---|---|---|---|
| `admin` | `password` | Administrador | Todo el sistema |
| `cajero1` | `password` | Cajero | Ventas, clientes, créditos, reportes |

> **⚠️ Cambiar antes de usar en producción** desde Configuración → Usuarios o desde `reset-password.php`

---

## URLs Útiles

| URL | Función |
|---|---|
| `/` | Login |
| `/dashboard` | Panel principal |
| `/pos` | Punto de Venta |
| `/productos` | Gestión de productos (admin) |
| `/clientes` | Gestión de clientes |
| `/ventas` | Historial de ventas |
| `/creditos` | Fiados y abonos |
| `/reportes` | Reportes y análisis |
| `/configuracion` | Configuración (admin) |
| `/manual` | Manual de uso |
| `/install-complete.php` | Instalador |
| `/reset-password.php` | Reset de contraseñas |
| `/utils/verificar-datos.php` | Diagnóstico BD |
| `/utils/actualizar-tasa.php` | Actualizar tasa BCV |
| `/api-tasa-cambio.php` | API tasa BCV |

---

*AbasPOS v1.0.0 · Desarrollado para Venezuela 🇻🇪*
