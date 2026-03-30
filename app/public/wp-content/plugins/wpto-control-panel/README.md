# WP Total Optimizer - Panel de Control

Plugin de WordPress para gestionar todas las optimizaciones de tu sitio desde un panel centralizado.

## 📋 Características

### 🔒 Módulo de Seguridad (7 funciones)
- Cambio de URL de Login
- Protección contra Fuerza Bruta
- Autenticación de Dos Factores (2FA)
- Hardening Automático
- Monitoreo de Cambios en Archivos
- Gestión de Sesiones
- Firewall de Aplicación Web (WAF)

### ⚡ Módulo de Optimización (8 funciones)
- Optimización de Base de Datos
- Desactivación de Recursos Innecesarios
- Minificación CSS/JS
- Carga Diferida (Lazy Loading)
- DNS Prefetch y Preconnect
- Optimización de Gutenberg
- Object Caching (Redis/Memcached)
- Limpieza Automática de Código

### 🖼️ Módulo de Imágenes (8 funciones)
- Conversión Automática a WebP/AVIF
- Control de Tamaños de Imagen
- Sobrescritura de Imágenes
- Eliminación Completa de Versiones
- Optimización y Compresión
- ALT Automático
- Sugerencias de ALT Contextuales
- Conversión Batch

### 🔍 Módulo SEO (6 funciones)
- Panel de Estructura de Encabezados
- Meta Descripción y Título SEO
- Schema Markup Automático
- Sitemap XML Automático
- Análisis SEO en Tiempo Real
- Vista Previa SERP de Google
- Edición Masiva de SEO

## 🚀 Instalación

### Método 1: Instalación Manual

1. Descarga el archivo `wpto-control-panel.zip`
2. Ve a WordPress → Plugins → Añadir nuevo
3. Haz clic en "Subir plugin"
4. Selecciona el archivo ZIP descargado
5. Haz clic en "Instalar ahora"
6. Activa el plugin

### Método 2: Instalación por FTP

1. Descomprime el archivo `wpto-control-panel.zip`
2. Sube la carpeta `wpto-control-panel` a `/wp-content/plugins/`
3. Activa el plugin desde WordPress → Plugins

## 📖 Uso

### Acceder al Panel de Control

Una vez activado el plugin, encontrarás el menú "Total Optimizer" en la barra lateral de WordPress.

### Panel Principal

El panel principal muestra:
- **Estadísticas generales**: Número de funciones activas por módulo
- **4 pestañas principales**: Seguridad, Optimización, Imágenes, SEO

### Activar/Desactivar Funciones

1. Selecciona la pestaña del módulo que deseas configurar
2. Activa el toggle switch de cada función que quieras habilitar
3. Configura las opciones específicas que aparecen al activar cada función
4. Haz clic en "Guardar Configuración"

### Prioridades de Funciones

Cada función tiene un nivel de prioridad:
- 🔴 **Crítica**: Funciones esenciales (ej: conversión WebP)
- 🔵 **Alta**: Muy recomendadas (ej: lazy loading, hardening)
- 🟡 **Media**: Importantes pero opcionales (ej: 2FA, minificación)
- ⚪ **Baja**: Mejoras adicionales (ej: WAF básico)

## ⌨️ Atajos de Teclado

- `Ctrl/Cmd + S`: Guardar configuración de la pestaña actual
- `Ctrl/Cmd + 1-4`: Cambiar entre pestañas (1=Seguridad, 2=Optimización, 3=Imágenes, 4=SEO)

## 🎯 Recomendaciones de Configuración

### Configuración Básica (Para empezar)
✅ Seguridad:
- Protección contra Fuerza Bruta
- Hardening Automático

✅ Optimización:
- Lazy Loading
- Optimización de Base de Datos

✅ Imágenes:
- Conversión a WebP
- ALT Automático

✅ SEO:
- Meta Descripción y Título
- Sitemap XML

### Configuración Avanzada (Máximo rendimiento)
- Activa todas las funciones de alta prioridad
- Configura 2FA para administradores
- Activa Object Caching si tienes Redis/Memcached
- Habilita minificación CSS/JS
- Activa conversión AVIF además de WebP

## 🔧 Requisitos Técnicos

- WordPress 5.8 o superior
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Extensión GD o Imagick (para procesamiento de imágenes)
- Mínimo 256MB de RAM

### Recomendado
- PHP 8.0+
- Redis o Memcached instalado (para Object Caching)
- 512MB+ de RAM

## 📊 Estructura de Archivos

```
wpto-control-panel/
├── wpto-control-panel.php    # Archivo principal del plugin
├── assets/
│   ├── css/
│   │   └── admin.css          # Estilos del panel
│   └── js/
│       └── admin.js           # JavaScript del panel
└── README.md                  # Este archivo
```

## ⚙️ Almacenamiento de Opciones

El plugin guarda las configuraciones en 4 opciones de WordPress:
- `wpto_security_options`: Configuración de seguridad
- `wpto_optimization_options`: Configuración de optimización
- `wpto_images_options`: Configuración de imágenes
- `wpto_seo_options`: Configuración de SEO

También crea una tabla en la base de datos:
- `wp_wpto_activity_log`: Registro de actividades del sistema

## 🐛 Solución de Problemas

### El panel no se muestra correctamente
- Limpia la caché de tu navegador
- Desactiva otros plugins de optimización temporalmente
- Verifica que estás usando WordPress 5.8+

### Los cambios no se guardan
- Verifica que tienes permisos de administrador
- Comprueba la consola del navegador en busca de errores JavaScript
- Asegúrate de hacer clic en "Guardar Configuración"

### Conflictos con otros plugins
El plugin es compatible con:
- Yoast SEO
- Rank Math
- WooCommerce
- Elementor
- Divi

Si experimentas conflictos, desactiva temporalmente otros plugins de optimización.

## 🔐 Seguridad

- Todas las peticiones AJAX usan nonces para verificación
- Los datos se sanitizan antes de guardarse
- Solo administradores pueden acceder al panel
- Las configuraciones críticas requieren confirmación

## 📈 Rendimiento

El plugin está optimizado para NO afectar el rendimiento:
- CSS y JS solo se cargan en páginas de administración
- Consultas a base de datos están optimizadas
- Sin impacto en el frontend a menos que actives funciones específicas

## 🔄 Actualizaciones

Este plugin está en versión 1.0.0. Las futuras versiones incluirán:
- Sistema de backups automáticos
- Integración con Google PageSpeed Insights
- Health Check automático del sitio
- Logs de actividad centralizados
- Y más...

## 💡 Soporte

Para soporte o sugerencias:
- Email: soporte@fixypet.com
- Web: https://fixypet.com

## 📄 Licencia

GPL v2 or later

---

**Desarrollado por JoanRaez** | [fixypet.com](https://fixypet.com)

**Versión:** 1.0.0  
**Última actualización:** Diciembre 2024
