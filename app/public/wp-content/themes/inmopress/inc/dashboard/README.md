# Panel de Control Frontend - InmoPress CRM

## Descripción

Panel de control minimalista tipo CRM para gestionar inmuebles, clientes, leads y más desde el frontend. El acceso y las funcionalidades disponibles dependen del rol del usuario.

## Acceso

El panel está disponible en: `https://tudominio.com/panel`

## Roles y Permisos

### Administrador
- Acceso completo a todas las secciones
- Puede gestionar: Inmuebles, Clientes, Leads, Visitas, Agencias, Agentes, Propietarios, Promociones

### Agencia
- Puede gestionar: Inmuebles, Clientes, Leads, Visitas, Agentes
- Ve solo sus inmuebles relacionados

### Agente
- Puede gestionar: Inmuebles, Clientes, Leads, Visitas
- Ve solo sus asignaciones (clientes, inmuebles, visitas)

### Trabajador
- Puede gestionar: Inmuebles, Clientes, Leads, Visitas
- Acceso limitado según configuración

### Cliente
- Acceso a: Dashboard, Favoritos, Perfil
- Solo puede ver y gestionar sus propios datos

## Secciones Disponibles

1. **Dashboard** - Resumen con estadísticas
2. **Inmuebles** - Listado y gestión de propiedades
3. **Clientes** - Listado y gestión de clientes
4. **Leads** - Gestión de leads y conversiones
5. **Visitas** - Calendario y gestión de visitas
6. **Agencias** - Gestión de agencias (solo Admin/Agencia)
7. **Agentes** - Gestión de agentes (solo Admin/Agencia)
8. **Propietarios** - Gestión de propietarios (solo Admin)
9. **Promociones** - Gestión de promociones
10. **Favoritos** - Inmuebles favoritos (solo Cliente)
11. **Mi Perfil** - Edición de perfil personal

## Instalación

1. Los archivos ya están creados en el tema
2. Ve a WordPress Admin → Ajustes → Enlaces permanentes
3. Guarda los cambios para activar las rewrite rules
4. Accede a `/panel` desde el frontend

## Personalización

### Cambiar la URL del panel

Edita `inc/dashboard/class-dashboard.php` y cambia la propiedad `$dashboard_slug`:

```php
private $dashboard_slug = 'panel'; // Cambia 'panel' por tu slug deseado
```

### Modificar permisos

Edita el método `user_can_access_section()` en `class-dashboard.php` para ajustar los permisos por rol.

### Personalizar estilos

Los estilos están en `assets/css/dashboard.css`. Puedes modificar colores, espaciados y diseño según tus necesidades.

## Estructura de Archivos

```
inc/dashboard/
  └── class-dashboard.php      # Clase principal del dashboard

templates/dashboard/
  ├── header.php               # Header del panel
  ├── footer.php               # Footer del panel
  ├── dashboard.php            # Página principal
  ├── properties.php           # Listado de inmuebles
  ├── clients.php              # Listado de clientes
  ├── leads.php                # Listado de leads
  ├── visits.php               # Listado de visitas
  ├── agencies.php             # Listado de agencias
  ├── agents.php                # Listado de agentes
  ├── owners.php                # Listado de propietarios
  ├── promotions.php           # Listado de promociones
  ├── favorites.php             # Favoritos del cliente
  └── profile.php               # Perfil del usuario

assets/
  ├── css/
  │   └── dashboard.css        # Estilos del panel
  └── js/
      └── dashboard.js         # JavaScript del panel
```

## Próximas Mejoras

- [ ] Sistema de favoritos funcional
- [ ] Filtros y búsqueda avanzada
- [ ] Paginación en las tablas
- [ ] Exportación de datos
- [ ] Gráficos y estadísticas avanzadas
- [ ] Notificaciones en tiempo real
- [ ] Sistema de permisos más granular

