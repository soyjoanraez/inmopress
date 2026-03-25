# Inmopress API

API REST personalizada para Inmopress con autenticación JWT, rate limiting y webhooks.

## Requisitos

- WordPress 6.4+
- PHP 8.1+
- Composer (para dependencias)

## Instalación

1. Instalar dependencias de Composer:
```bash
composer require firebase/php-jwt:^6.0
```

2. Activar el plugin desde el panel de WordPress.

## Autenticación

La API utiliza JWT (JSON Web Tokens) para autenticación.

### Login

```http
POST /wp-json/inmopress/v1/auth/login
Content-Type: application/json

{
  "username": "usuario",
  "password": "contraseña"
}
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
      "id": 1,
      "username": "usuario",
      "email": "usuario@example.com",
      "display_name": "Usuario"
    }
  }
}
```

### Uso del Token

Incluir el token en el header `Authorization`:

```http
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

## Endpoints

### Autenticación

- `POST /wp-json/inmopress/v1/auth/login` - Login
- `POST /wp-json/inmopress/v1/auth/refresh` - Refrescar token
- `POST /wp-json/inmopress/v1/auth/logout` - Logout
- `GET /wp-json/inmopress/v1/auth/me` - Información del usuario

### Propiedades

- `GET /wp-json/inmopress/v1/properties` - Listar propiedades
- `GET /wp-json/inmopress/v1/properties/{id}` - Obtener propiedad
- `POST /wp-json/inmopress/v1/properties` - Crear propiedad
- `PUT /wp-json/inmopress/v1/properties/{id}` - Actualizar propiedad
- `DELETE /wp-json/inmopress/v1/properties/{id}` - Eliminar propiedad
- `GET /wp-json/inmopress/v1/properties/search` - Buscar propiedades

### Clientes

- `GET /wp-json/inmopress/v1/clients` - Listar clientes
- `GET /wp-json/inmopress/v1/clients/{id}` - Obtener cliente
- `POST /wp-json/inmopress/v1/clients` - Crear cliente
- `PUT /wp-json/inmopress/v1/clients/{id}` - Actualizar cliente
- `DELETE /wp-json/inmopress/v1/clients/{id}` - Eliminar cliente

### Leads

- `GET /wp-json/inmopress/v1/leads` - Listar leads
- `GET /wp-json/inmopress/v1/leads/{id}` - Obtener lead
- `POST /wp-json/inmopress/v1/leads` - Crear lead (público)
- `PUT /wp-json/inmopress/v1/leads/{id}` - Actualizar lead
- `POST /wp-json/inmopress/v1/leads/{id}/convert` - Convertir lead a cliente

### Matching

- `GET /wp-json/inmopress/v1/matching/property/{id}` - Matches de una propiedad
- `GET /wp-json/inmopress/v1/matching/client/{id}` - Matches de un cliente
- `POST /wp-json/inmopress/v1/matching/property/{id}/calculate` - Calcular matching
- `POST /wp-json/inmopress/v1/matching/client/{id}/calculate` - Calcular matching

## Rate Limiting

Por defecto, cada usuario puede realizar 100 peticiones por hora. Este límite es configurable desde el panel de administración.

Si se excede el límite, se devuelve un error 429:

```json
{
  "success": false,
  "error": {
    "code": "rate_limit_exceeded",
    "message": "Has excedido el límite de peticiones. Límite: 100 por hora."
  }
}
```

## Webhooks

Los webhooks se disparan automáticamente cuando ocurren ciertos eventos:

- `property.created` - Propiedad creada
- `property.updated` - Propiedad actualizada
- `property.deleted` - Propiedad eliminada
- `client.created` - Cliente creado
- `client.updated` - Cliente actualizado
- `client.deleted` - Cliente eliminado
- `lead.created` - Lead creado
- `lead.updated` - Lead actualizado
- `lead.converted` - Lead convertido a cliente

### Configurar Webhook

Los webhooks se configuran desde el código o mediante la base de datos directamente.

El payload incluye:
- `event`: Nombre del evento
- `data`: Datos relacionados
- `timestamp`: Timestamp Unix

El header `X-Inmopress-Signature` contiene un HMAC SHA256 del payload usando el secret del webhook.

## Ejemplos

### Crear Propiedad

```http
POST /wp-json/inmopress/v1/properties
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "Apartamento en el centro",
  "content": "Descripción del apartamento...",
  "status": "publish",
  "fields": {
    "impress_property_operation": "venta",
    "impress_property_price": 250000,
    "impress_property_city": "Madrid"
  }
}
```

### Buscar Propiedades

```http
GET /wp-json/inmopress/v1/properties/search?q=apartamento&operation=venta&city=Madrid&min_price=200000&max_price=300000
Authorization: Bearer {token}
```

### Crear Lead (Público)

```http
POST /wp-json/inmopress/v1/leads
Content-Type: application/json

{
  "name": "Juan Pérez",
  "email": "juan@example.com",
  "phone": "+34 600 000 000",
  "message": "Estoy interesado en esta propiedad",
  "property_id": 123
}
```

## Respuestas de Error

Todas las respuestas de error siguen este formato:

```json
{
  "success": false,
  "error": {
    "code": "error_code",
    "message": "Mensaje de error descriptivo"
  }
}
```

Códigos de estado HTTP comunes:
- `400` - Bad Request (datos inválidos)
- `401` - Unauthorized (token inválido o faltante)
- `404` - Not Found (recurso no encontrado)
- `429` - Too Many Requests (rate limit excedido)
- `500` - Internal Server Error

## Seguridad

- Todos los endpoints (excepto login y creación de leads) requieren autenticación JWT
- Los tokens JWT expiran después de 7 días
- Rate limiting previene abuso
- Los webhooks incluyen firma HMAC para verificación
