# Inmopress Core Plugin

Plugin principal de Inmopress que registra los Custom Post Types, Taxonomías y Roles del sistema CRM inmobiliario.

## Estructura

- `inmopress-core.php` - Archivo principal del plugin
- `includes/class-cpts.php` - Registro de Custom Post Types
- `includes/class-taxonomies.php` - Registro de Taxonomías
- `includes/class-roles.php` - Creación de Roles personalizados
- `acf-json/` - Carpeta para sincronización de campos ACF

## Custom Post Types

1. **impress_property** - Inmuebles (público)
2. **impress_client** - Clientes (privado)
3. **impress_lead** - Leads (privado)
4. **impress_visit** - Visitas (privado)
5. **impress_agency** - Agencias (privado)
6. **impress_agent** - Agentes (privado)
7. **impress_owner** - Propietarios (privado)
8. **impress_promotion** - Promociones (público)
9. **impress_transaction** - Transacciones (privado)
10. **impress_email_template** - Plantillas de Email (privado)
11. **impress_event** - Eventos/Tareas (privado)

## Roles Personalizados

- **agencia** - Sin permisos de publicación
- **agente** - Con permisos de publicación
- **trabajador** - Solo lectura/edición
