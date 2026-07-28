# 🚀 AbasPOS - Despliegue Docker Completado

## ✅ Estado

Tu aplicación **Sistema de Venta (AbasPOS)** está desplegada y funcionando en Docker.

## 📍 Cómo acceder desde tu red local

### Desde tu computadora principal:
- **URL**: `http://192.168.1.5:8080/Sistema de venta/`
- **Usuario**: `admin`
- **Contraseña**: `password`

### Desde otros dispositivos en tu red (teléfono, tablet, otra PC):
- Asegúrate de estar conectado a la misma red WiFi/LAN
- Abre tu navegador y ve a: `http://192.168.1.5:8080/Sistema de venta/`

## 🗄️ Información de Base de Datos

| Parámetro | Valor |
|-----------|-------|
| **Host** | mysql (interno en Docker) / localhost:3307 (acceso externo) |
| **Base de Datos** | gastropos |
| **Usuario** | gastropos |
| **Contraseña** | gastropos123 |

## 🐳 Contenedores Docker

Dos contenedores están corriendo:

1. **gastropos-app** (PHP 8.1 + Apache)
   - Puerto: 8080
   - Imagen: sistemadeventa-app

2. **gastropos-mysql** (MySQL 8.0)
   - Puerto: 3307
   - Volumen persistente: mysql_data

## 📝 Comandos útiles

```bash
# Ver estado de los contenedores
docker ps

# Ver logs de la aplicación
docker logs gastropos-app -f

# Ver logs de la base de datos
docker logs gastropos-mysql -f

# Detener los contenedores
docker-compose -f "C:/laragon/www/Sistema de venta/docker-compose.yml" down

# Reiniciar los contenedores
docker-compose -f "C:/laragon/www/Sistema de venta/docker-compose.yml" restart

# Volver a iniciar
docker-compose -f "C:/laragon/www/Sistema de venta/docker-compose.yml" up -d
```

## 🔧 Archivos creados/modificados

- ✅ `Dockerfile` - Configuración de la imagen PHP/Apache
- ✅ `docker-compose.yml` - Orquestación de servicios
- ✅ `.dockerignore` - Archivos excluidos del build
- ✅ `config/config.php` - Configuración adaptada para Docker
- ✅ `.env` - Variables de entorno para Docker
- ✅ `.htaccess` - Reescritura de URLs corregida

## ⚠️ Notas importantes

1. **Laragon seguirá funcionando** - Tus servicios en Laragon (puertos 80, 3306, etc.) no se ven afectados
2. **Puerto 8080** - Se utiliza para evitar conflictos con Laragon
3. **Datos persistentes** - La base de datos se guarda en `mysql_data` (volumen Docker)
4. **Desarrollo** - Los cambios en los archivos de la carpeta se reflejan inmediatamente en el contenedor

## 🎯 Próximos pasos

1. Verifica que la aplicación sea accesible desde otros dispositivos en tu red
2. Configura un script de respaldo para la base de datos (opcional)
3. Considera usar un reverse proxy (Nginx) si necesitas múltiples aplicaciones en producción

¡Tu aplicación está lista para usarse en tu red local! 🎉
