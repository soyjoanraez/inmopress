# InmoPress Pro - Tema Integrado

## 🎉 Integración Completa

Este tema combina las funcionalidades de **InmoPress CRM** (sistema inmobiliario) con **GP Child Pro** (funcionalidades administrativas avanzadas).

---

## 📋 Estructura del Tema

```
inmopress/
│
├── 📄 style.css                    (Información del tema)
├── 📄 functions.php                (Cargador principal - integra ambos sistemas)
├── 📄 README-INTEGRACION.md         (Esta documentación)
│
├── 📁 assets/
│   ├── css/
│   │   ├── admin.css               (Estilos GPCP + InmoPress)
│   │   ├── dashboard.css           (Dashboard frontend)
│   │   └── property-cards.css       (Cards de propiedades)
│   └── js/
│       ├── admin.js                (JavaScript GPCP)
│       ├── dashboard.js            (Dashboard frontend)
│       └── property-filters.js     (Filtros de propiedades)
│
├── 📁 inc/
│   ├── class-inmopress-core.php    (Core InmoPress CRM)
│   │
│   ├── 📁 acf/                     (Campos ACF)
│   │   ├── class-acf-integration.php
│   │   ├── class-acf-fields-loader.php
│   │   └── groups/                 (Grupos de campos JSON)
│   │
│   ├── 📁 dashboard/               (Dashboard Frontend CRM)
│   │   └── class-dashboard.php
│   │
│   ├── 📁 post-types/             (Custom Post Types)
│   │   ├── class-post-types.php
│   │   └── class-property-hooks.php
│   │
│   ├── 📁 properties/             (Sistema de Propiedades)
│   │   ├── class-property-settings.php
│   │   ├── class-property-query.php
│   │   ├── class-property-shortcode.php
│   │   ├── class-property-filters.php
│   │   └── class-property-ajax.php
│   │
│   ├── 📁 roles/                   (Roles y Permisos)
│   │   └── class-roles.php
│   │
│   ├── 📁 taxonomies/              (Taxonomías)
│   │   └── class-taxonomies.php
│   │
│   └── 📁 gpcp/                    (Módulos GP Child Pro)
│       ├── class-gpcp-loader.php   (Loader GPCP)
│       │
│       ├── 📁 admin/               (Panel Admin GPCP)
│       │   ├── class-gpcp-admin.php
│       │   ├── class-gpcp-seo-manager.php
│       │   └── views/
│       │       └── dashboard.php
│       │
│       └── 📁 modules/              (Módulos GPCP)
│           ├── class-gpcp-security.php
│           ├── class-gpcp-seo.php
│           ├── class-gpcp-optimization.php
│           ├── class-gpcp-images.php
│           ├── class-gpcp-branding.php      (🆕 Branding)
│           ├── class-gpcp-export-import.php (🆕 Export/Import)
│           ├── class-gpcp-dashboard-widgets.php (🆕 Widgets)
│           └── class-gpcp-maintenance.php   (🆕 Mantenimiento)
│
└── 📁 templates/                    (Templates Frontend)
    ├── dashboard/                  (Templates del dashboard)
    └── properties/                  (Templates de propiedades)
```

---

## 🎯 Funcionalidades Incluidas

### 🏠 InmoPress CRM (Sistema Inmobiliario)

#### Custom Post Types:
1. **Inmuebles** (`impress_property`)
2. **Clientes** (`impress_client`)
3. **Leads** (`impress_lead`)
4. **Visitas** (`impress_visit`)
5. **Agencias** (`impress_agency`)
6. **Agentes** (`impress_agent`)
7. **Propietarios** (`impress_owner`)
8. **Promociones** (`impress_promotion`)

#### Características:
- ✅ Dashboard frontend en `/panel`
- ✅ Sistema de roles y permisos
- ✅ Integración con ACF (Advanced Custom Fields)
- ✅ Filtros avanzados de propiedades
- ✅ Shortcodes para mostrar propiedades
- ✅ Sistema de favoritos para clientes
- ✅ Gestión de visitas y leads

### 🛠️ GP Child Pro (Funcionalidades Administrativas)

#### Módulos Incluidos:

1. **🔒 Seguridad**
   - URL de login personalizada
   - Protección de archivos
   - Límite de intentos de login

2. **🎯 SEO**
   - Auto-completado de metadatos SEO
   - Gestor centralizado de SEO

3. **⚡ Optimización**
   - 12 optimizaciones de rendimiento
   - Defer JavaScript
   - Lazy loading

4. **🖼️ Imágenes**
   - Conversión automática a WebP
   - Gestión inteligente de tamaños

5. **🎨 Branding** (🆕)
   - Personalización de login
   - Personalización de admin
   - Logos y colores personalizados

6. **📦 Exportar/Importar** (🆕)
   - Exportación de configuraciones
   - Importación con match de posts
   - Backup de configuraciones

7. **📊 Dashboard Widgets** (🆕)
   - Widget de Resumen SEO
   - Widget de Estado del Sitio
   - Widget de Actividad Reciente
   - Widget de Notas Rápidas

8. **🔧 Modo Mantenimiento** (🆕)
   - Página de mantenimiento personalizable
   - Cuenta regresiva
   - Control de acceso por IP

---

## 🚀 Cómo Funciona la Integración

### Inicialización

El archivo `functions.php` carga ambos sistemas:

```php
// Inicializa InmoPress CRM
$inmopress = Inmopress\CRM\Core::get_instance();
$inmopress->init();

// Inicializa GPCP modules
GPCP_Loader::get_instance();
```

### Menús de Administración

**InmoPress CRM:**
- Los CPTs aparecen en el menú principal de WordPress
- El dashboard frontend está en `/panel`

**GP Child Pro:**
- Menú "InmoPress Pro" en el admin de WordPress
- Submenús: Dashboard, Seguridad, SEO, Optimización, Imágenes, Branding, Exportar/Importar, Mantenimiento

### Sin Conflictos

- ✅ Los nombres de clases están en diferentes namespaces
- ✅ Las constantes están bien definidas
- ✅ Los hooks no se solapan
- ✅ Los assets se cargan solo cuando es necesario

---

## 📖 Uso del Tema

### 1. Configuración Inicial

1. **Activa el tema** en WordPress > Apariencia > Temas
2. **Configura ACF** si aún no lo has hecho
3. **Ve a InmoPress Pro > Dashboard** para configurar las funcionalidades GPCP
4. **Accede a `/panel`** para el dashboard frontend del CRM

### 2. Configurar Branding

1. Ve a **InmoPress Pro > Branding**
2. Personaliza el nombre del tema (por defecto: "InmoPress Pro")
3. Sube logos para login y admin
4. Elige tu color principal
5. Personaliza el footer

### 3. Configurar Dashboard Widgets

Los widgets se activan automáticamente. Ve al Dashboard de WordPress para verlos:
- Resumen SEO
- Estado del Sitio
- Actividad Reciente
- Notas Rápidas

### 4. Exportar/Importar Configuraciones

1. Ve a **InmoPress Pro > Exportar/Importar**
2. Descarga tu configuración actual
3. Importa en otros sitios para replicar la configuración

### 5. Modo Mantenimiento

1. Ve a **InmoPress Pro > Mantenimiento**
2. Personaliza la página
3. Activa cuando necesites trabajar sin interrupciones

---

## 🎨 Personalización

### Cambiar el Nombre del Menú

El nombre del menú se puede cambiar en:
- **InmoPress Pro > Branding > Nombre del Tema**

O directamente en la base de datos:
```sql
UPDATE wp_options SET option_value = 'Tu Nombre' WHERE option_name = 'gpcp_branding_theme_name';
```

### Añadir Funcionalidades

**Para InmoPress CRM:**
- Añade nuevos CPTs en `inc/post-types/`
- Crea nuevos campos ACF en `inc/acf/groups/`
- Añade templates en `templates/`

**Para GPCP:**
- Crea nuevos módulos en `inc/gpcp/modules/`
- Regístralos en `inc/gpcp/class-gpcp-loader.php`
- Añade páginas en `inc/gpcp/admin/class-gpcp-admin.php`

---

## ⚠️ Notas Importantes

### Requisitos

- WordPress 5.8+
- PHP 7.4+
- GeneratePress (tema padre)
- ACF Pro (recomendado para InmoPress CRM)
- Extensión GD de PHP (para WebP)

### Compatibilidad

- ✅ Todos los módulos GPCP funcionan independientemente
- ✅ El CRM funciona sin necesidad de activar módulos GPCP
- ✅ Puedes desactivar módulos GPCP sin afectar el CRM

### Actualizaciones

Al actualizar el tema:
1. Haz backup de tus configuraciones (Exportar/Importar)
2. Actualiza los archivos
3. Verifica que todo funcione correctamente
4. Restaura configuraciones si es necesario

---

## 🆘 Solución de Problemas

### El menú "InmoPress Pro" no aparece

- Verifica que el tema esté activo
- Asegúrate de tener permisos de administrador
- Revisa que no haya errores PHP en el log

### Los widgets del dashboard no aparecen

- Verifica que el módulo esté cargado
- Limpia la caché del navegador
- Revisa la consola de JavaScript

### El modo mantenimiento no funciona

- Verifica que no seas administrador (los admins siempre ven el sitio)
- Revisa que tu IP no esté en las IPs permitidas
- Limpia la caché si usas un plugin de caché

---

## 📚 Documentación Adicional

- **InmoPress CRM**: Ver `inmopress-estructura-completa.md`
- **Dashboard Frontend**: Ver `inc/dashboard/README.md`
- **GPCP Modules**: Ver documentación en cada módulo

---

## 🏆 Resumen

Has combinado exitosamente:

✅ **Sistema CRM Inmobiliario Completo** (InmoPress)
✅ **8 Módulos Administrativos Profesionales** (GP Child Pro)
✅ **4 Nuevas Funcionalidades Avanzadas** (Branding, Export/Import, Widgets, Maintenance)

**Todo en un solo tema hijo de GeneratePress.**

---

¡Disfruta de tu tema integrado! 🎉



