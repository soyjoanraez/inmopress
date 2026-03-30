# 🏠 ESTRUCTURA COMPLETA INMOPRESS
## CPTs + Campos ACF + Taxonomías + Relaciones

---

## 📋 ÍNDICE DE CPTs

1. **CPT Inmuebles** (`impress_property`)
2. **CPT Clientes** (`impress_client`)
3. **CPT Leads** (`impress_lead`)
4. **CPT Visitas** (`impress_visit`)
5. **CPT Agencias** (`impress_agency`)
6. **CPT Agentes** (`impress_agent`)
7. **CPT Propietarios** (`impress_owner`)
8. **CPT Promociones** (`impress_promotion`)

---

# 1️⃣ CPT INMUEBLES (`impress_property`)

## Grupo ACF: Información General

| Campo | Nombre ACF | Tipo | Ejemplo/Opciones |
|-------|-----------|------|------------------|
| **Título** | `title` | Title | Casa de campo en Manises |
| **Publica** | `publicada` | Switch | on/off |
| **Vendida** | `vendida` | Switch | on/off |
| **Reservada** | `reservada` | Switch | on/off |
| **Dirección** | `direccion` | Text | Avenida Tir de Colom 6 46980 Paterna |
| **Descripción** | `descripcion` | WYSIWYG | - |
| **Propósito** | `proposito` | Select | Alquiler, Venta |
| **Referencia** | `referencia` | Text | N4569Z |
| **Provincia** | - | Taxonomy | Valencia |
| **Población** | - | Taxonomy | Paterna |
| **Zona** | `zona` | Text | Mercado Colón etc |
| **Agrupación** | `agrupacion` | Select | Villas, Apartamentos, Casas, Chalets, Terrenos, Solares |
| **Agrupación especial** | `agrupacion_especial` | Select | Alquiler vacacional, Frente al mar, Para estudiantes, De lujo, Con instalaciones deportivas, Con opción a compra |
| **Tipo de vivienda** | - | Taxonomy | Piso, Casa, Chalet, etc |
| **Orientación** | `orientacion` | Select | Norte, Sur, Este, Oeste, Noreste, Noroeste, Sureste, Suroeste, Este-Oeste, Norte-Sur |

---

## Grupo ACF: Relaciones

| Campo | Nombre ACF | Tipo | Relación |
|-------|-----------|------|----------|
| **Agencia colaboradora** | `agencia_colaboradora` | Post Object | CPT Agency |
| **Agente** | `agente` | Post Object o User | CPT Agent o Usuario Admin |
| **Propietario** | `propietario` | Post Object | CPT Owner |

---

## Grupo ACF: Características Especiales

| Campo | Nombre ACF | Tipo |
|-------|-----------|------|
| **Solo VIP** | `solo_vip` | Switch |
| **Exclusiva** | `exclusiva` | Switch |

---

## Grupo ACF: Características de la Ficha

| Campo | Nombre ACF | Tipo | Ejemplo |
|-------|-----------|------|---------|
| **Estado** | `estado` | Select | Nuevo, Reformado, Buen estado, Para reformar, En construcción, En proyecto |
| **Superficie útil** | `superficie_util` | Number | 100 |
| **Superficie construida** | `superficie_construida` | Number | 120 |
| **Superficie parcela** | `superficie_parcela` | Number | 180 |
| **Plantas** | `plantas` | Number | 1 |
| **Año de construcción** | `ano` | Number | 1982 |
| **Dormitorios** | `dormitorios` | Number | 3 |
| **Baños** | `banos` | Number | 2 |
| **Baños en suite** | `banos_suite` | Number | 1 |
| **Cocinas** | `cocinas` | Number | 1 |
| **Salones** | `salones` | Number | 1 |
| **Balcones** | `balcones` | Number | 1 |
| **Terrazas** | `terrazas` | Number | 1 |
| **Trasteros** | `trasteros` | Number | 2 |
| **Certificación energética** | `certificacion_energetica` | Select | En trámite, Sin certificación, A, B, C, D, E, F, G, Pendiente renovación, Caducada |
| **Ficha energética** | `ficha_energetica` | File | ficha.pdf |
| **Calefacción** | `calefaccion` | Select | Ver glosario completo |
| **Jardín** | `jardin` | Select | Privado, Comunitario, Patio/terraza, Zonas verdes |
| **Piscina** | `piscina` | Select | Privada, Comunitaria, Aire libre, Cubierta, Climatizada, Infinita |
| **Medida Piscina** | `medida_piscina` | Number | 120 |
| **Garajes** | `garajes` | Number | 2 |
| **Amueblado** | `amueblado` | Select | Totalmente, Parcialmente, Sin amueblar, Lujo, Básico, Moderno, Clásico |
| **Estacionamiento** | `estacionamiento` | Select | Pago, Público, Privado, Individual, Comunitario, Subterráneo, Aire libre, Cubierto, Calle, 24h, Videovigilancia |
| **Ventanas** | `ventanas` | Select | Climalit, Doble cristal, PVC, Aluminio, Madera, Corredizas, Abatibles, Oscilobatientes |
| **Tipo suelo** | `suelo` | Select | Mármol, Parquet, Cerámicas, Madera, Terrazo, Laminado, Vinilo, Hormigón pulido, Moqueta |
| **IBI** | `ibi` | Text | - |
| **Impuesto basura** | `impuesto_basura` | Text | 180€ al año |
| **Gastos comunidad** | `gastos_comunidad` | Number | 50-80€ |
| **CO2 Emisiones** | `co2_emisiones` | Text | - |

---

## Grupo ACF: Distancias

| Campo | Nombre ACF | Tipo | Opciones |
|-------|-----------|------|----------|
| **Autobús** | `autobus` | Select | 1min, 2min, 3min, 4min, 5min, 10min, 15min, 20min, 25min, 30min, 35min, 40min, 45min, 50min, 55min, 1h, 1-2h, 2-3h |
| **Metro** | `metro` | Select | (mismas opciones) |
| **Centros escolares** | `centros_escolares` | Select | (mismas opciones) |
| **Supermercados** | `supermercados` | Select | (mismas opciones) |
| **Centro de salud** | `centros_salud` | Select | (mismas opciones) |
| **Áreas verdes** | `areas_verdes` | Select | (mismas opciones) |
| **Centros comerciales** | `centros_comerciales` | Select | (mismas opciones) |
| **Gimnasios** | `gimnasios` | Select | (mismas opciones) |
| **Farmacia** | `farmacias` | Select | (mismas opciones) |
| **Teatro** | `teatros` | Select | (mismas opciones) |
| **Cine** | `cines` | Select | (mismas opciones) |

---

## Grupo ACF: Características (Switches)

| Campo | Nombre ACF |
|-------|-----------|
| **Aire acondicionado** | `aire_acondicionado` |
| **Barbacoa** | `barbacoa` |
| **Lavavajillas** | `lavabajillas` |
| **Ascensor** | `ascensor` |
| **Gimnasio** | `gimnasio` |
| **Encimera de granito** | `encimera_granito` |
| **Lavandería** | `lavanderia` |
| **Solar** | `solar` |
| **Spa** | `spa` |
| **Adaptado minusválidos** | `minusvalidos` |
| **Luminoso** | `luminoso` |
| **Horno** | `horno` |
| **Puerta blindada** | `puerta_blindada` |
| **Patio** | `patio` |
| **Conserje** | `conserje` |
| **Buhardilla** | `buhardilla` |
| **Chimenea** | `chimenea` |
| **Agua potable** | `agua_potable` |
| **Alarma** | `alarma` |
| **Armarios empotrados** | `armarios_empotrados` |
| **Porche** | `porche` |
| **Despensa** | `despensa` |
| **Portero automático / video** | `portero_automatico` |
| **Jacuzzi** | `jacuzzi` |
| **Sótano** | `sotano` |
| **Vistas al mar** | `vistas_mar` |
| **Vistas a la montaña** | `vistas_montana` |
| **Suelo radiante** | `suelo_radiante` |
| **Aislamiento Térmico/Acústico** | `aislamiento_termico` |
| **Sistema riego automático** | `sistema_riego_automatico` |
| **Internet** | `internet` |
| **SAT** | `sat` |
| **Vitrocerámica** | `vitroceramica` |
| **Frigorífico** | `frigorifico` |
| **Microondas** | `microondas` |
| **Zona infantil** | `zona_infantil` |
| **Tenis** | `tenis` |
| **Padel** | `padel` |
| **Muebles jardín** | `muebles_jardin` |

---

## Grupo ACF: Venta

| Campo | Nombre ACF | Tipo |
|-------|-----------|------|
| **Precio venta deseado propietario** | `precio_venta_propietario` | Number |
| **Precio mínimo venta** | `precio_minimo_venta` | Number |
| **Precio venta** | `precio_venta` | Number |
| **Tipo de descuento venta** | `tipo_descuento_venta` | Select |
| **Cantidad descuento venta** | `cantidad_descuento_venta` | Number |
| **Tipo de comisión venta** | `tipo_comision_venta` | Select |
| **Cantidad de comisión venta** | `cantidad_comision_venta` | Number |

---

## Grupo ACF: Alquiler

| Campo | Nombre ACF | Tipo | Opciones |
|-------|-----------|------|----------|
| **Precio alquiler deseado propietario** | `precio_alquiler_propietario` | Number | - |
| **Precio alquiler** | `precio_alquiler` | Number | 850 |
| **Depósito** | `deposito` | Number | 1000 |
| **Tipo de descuento alquiler** | `tipo_descuento_alquiler` | Select | Cantidad, Porcentaje |
| **Cantidad descuento alquiler** | `cantidad_descuento_alquiler` | Number | 5 |
| **Tipo de comisión alquiler** | `tipo_comision_alquiler` | Select | Cantidad, Porcentaje |
| **Cantidad de comisión alquiler** | `cantidad_comision_alquiler` | Number | 5 |
| **Mascotas permitidas** | `mascotas` | Select | Si, No |
| **Periodo de pago** | `periodo_pago` | Select | Semanal, Mensual |
| **Plazo mínimo** | `plazo_minimo` | Select | Mensual, 2 meses, 3 meses, 6 meses, Anual |
| **Seguro** | `seguro` | Select | Opcional, Obligatorio, Sin seguro, Incluido en precio, A cargo arrendador, A cargo arrendatario |
| **Fumar** | `fumar` | Switch | - |

---

## Grupo ACF: Media

| Campo | Nombre ACF | Tipo |
|-------|-----------|------|
| **Galerías fotos** | `fotos` | Gallery |
| **Vídeo** | `videos` | URL |

---

# 2️⃣ CPT CLIENTES (`impress_client`)

## Grupo ACF: Datos Personales

| Campo | Nombre ACF | Tipo | Ejemplo |
|-------|-----------|------|---------|
| **Nombre** | `nombre` | Text | Jose |
| **Apellidos** | `apellidos` | Text | Hilario |
| **Teléfono** | `telefono` | Number | 607954491 |
| **Correo** | `correo` | Email | hola@generacionweb.es |
| **Dirección** | `direccion` | Text | Avenida tir de colom 6 |
| **Ciudad** | - | Taxonomy | Valencia |
| **Municipio** | - | Taxonomy | Paterna |

---

## Grupo ACF: Estado y Clasificación

| Campo | Nombre ACF | Tipo | Opciones |
|-------|-----------|------|----------|
| **Estado Lead** | `estado_lead` | Select | Pendiente, Llamado, Llamado y no contesta, etc |
| **Semáforo Estado** | `semaforo_estado` | Select | HOT, WARM, COLD |
| **Rol** | - | User Role | Cliente (default), VIP |

---

## Grupo ACF: Más Información

| Campo | Nombre ACF | Tipo | Opciones |
|-------|-----------|------|----------|
| **Canal** | `canal` | Select | Referido, Web, Teléfono, Redes sociales, etc |
| **Idioma** | `idioma` | Select | Castellano, Inglés, Francés, etc |
| **Interés** | `interes` | Select | Compra, Alquiler |
| **Provincia de interés** | - | Taxonomy | Valencia |
| **Población de interés** | - | Taxonomy | Paterna |
| **Zona de interés** | `zona_interes` | Text | Mercado Colón etc |
| **Agrupación de interés** | `agrupacion_interes` | Select | Villas, etc |
| **Agrupación especial de interés** | `agrupacion_especial_interes` | Select | Alquiler vacacional, etc |
| **Tipo de vivienda de interés** | `tipo_vivienda_interes` | Select | Piso, Casa, Chalet, etc |

---

## Grupo ACF: Visitas a Inmuebles (Repeater)

| Campo | Nombre ACF | Tipo |
|-------|-----------|------|
| **Referencia** | `referencia_visita` | Post Object |
| **Fecha** | `fecha_visita` | DateTime |
| **Nota** | `nota_visita` | Textarea |

---

## Grupo ACF: Solicitudes (Repeater)

| Campo | Nombre ACF | Tipo |
|-------|-----------|------|
| **Referencia** | `referencia_solicitud` | Post Object |
| **Fecha** | `fecha_solicitud` | DateTime |
| **Interés** | `interes_solicitud` | Select |

---

## Grupo ACF: Configuración

| Campo | Nombre ACF | Tipo |
|-------|-----------|------|
| **Newsletter** | `newsletter` | Switch |

---

# 3️⃣ CPT LEADS (`impress_lead`)

> **Nota del documento:** INSERT / UPDATE CLIENTE - INSERT LEAD CON VINCULACIÓN CLIENTE

Este CPT se crea automáticamente cuando llega un lead desde formularios y se puede convertir en Cliente.

**Campos sugeridos:**
- Mismos campos que Cliente
- Campo adicional: `convertido_a_cliente` (Switch)
- Campo adicional: `cliente_relacionado` (Post Object → CPT Client)

---

# 4️⃣ CPT VISITAS (`impress_visit`)

> **Nota del documento:** Hoja vacía

**Campos sugeridos basados en la estructura de Clientes:**

| Campo | Nombre ACF | Tipo |
|-------|-----------|------|
| **Fecha y hora** | `fecha_hora` | DateTime |
| **Cliente** | `cliente` | Post Object |
| **Inmueble** | `inmueble` | Post Object |
| **Agente** | `agente` | Post Object/User |
| **Estado** | `estado` | Select |
| **Notas** | `notas` | Textarea |
| **Firma** | `firma` | Image/Canvas |
| **Fotos** | `fotos` | Gallery |

---

# 5️⃣ CPT AGENCIAS (`impress_agency`)

## Grupo ACF: Datos de Contacto

| Campo | Nombre ACF | Tipo | Ejemplo |
|-------|-----------|------|---------|
| **Nombre** | `nombre` | Text | Jose |
| **Apellidos** | `apellidos` | Text | Hilario |
| **Teléfono** | `telefono` | Number | 607954491 |

---

## Grupo ACF: Datos de la Agencia

| Campo | Nombre ACF | Tipo | Ejemplo |
|-------|-----------|------|---------|
| **Nombre agencia** | `nombre_agencia` | Text | Lloma Llarga |
| **Logo agencia** | `logo_agencia` | Image | logoagencia.jpg |
| **Dirección agencia** | `direccion_agencia` | Text | Avenida tir de colom 6 |
| **Correo agencia** | `correo_agencia` | Email | hola@generacionweb.es |
| **Teléfono agencia** | `telefono_agencia` | Number | 607954491 |
| **Ciudad** | - | Taxonomy | Valencia |
| **Municipio** | - | Taxonomy | Paterna |

---

## Grupo ACF: Datos de Acceso

| Campo | Nombre ACF | Tipo |
|-------|-----------|------|
| **Usuario** | `usuario` | User | (Vincular con usuario WP) |
| **Contraseña** | - | - | (Gestionado por WP) |

---

# 6️⃣ CPT AGENTES (`impress_agent`)

> **Nota del documento:** USARLO CON USUARIO CON ROL DE AGENTE Y VINCULADO A AGENCIA

**Campos sugeridos:**

| Campo | Nombre ACF | Tipo |
|-------|-----------|------|
| **Usuario WordPress** | `usuario_wp` | User |
| **Agencia** | `agencia` | Post Object |
| **Nombre** | `nombre` | Text |
| **Apellidos** | `apellidos` | Text |
| **Teléfono** | `telefono` | Number |
| **Email** | `email` | Email |
| **Avatar** | `avatar` | Image |
| **Bio** | `bio` | Textarea |

---

# 7️⃣ CPT PROPIETARIOS (`impress_owner`)

## Grupo ACF: Datos

| Campo | Nombre ACF | Tipo | Ejemplo |
|-------|-----------|------|---------|
| **Nombre** | `nombre` | Text | Jose |
| **Apellidos** | `apellidos` | Text | Hilario |
| **Teléfono** | `telefono` | Number | 607954491 |
| **Correo** | `correo` | Email | hola@generacionweb.es |
| **Dirección** | `direccion` | Text | Avenida tir de colom 6 |
| **Ciudad** | - | Taxonomy | Valencia |
| **Municipio** | - | Taxonomy | Paterna |
| **DNI** | `dni` | Text | - |

> **Nota del documento:** Opción que puedan subir inmuebles y que se quede en borrador

---

# 8️⃣ CPT PROMOCIONES (`impress_promotion`)

> **Nota del documento:** PROMOCIONES CON Inmuebles relacionados

**Campos sugeridos:**

| Campo | Nombre ACF | Tipo |
|-------|-----------|------|
| **Título promoción** | `titulo` | Text |
| **Descripción** | `descripcion` | WYSIWYG |
| **Inmuebles** | `inmuebles` | Relationship |
| **Fecha inicio** | `fecha_inicio` | Date |
| **Fecha fin** | `fecha_fin` | Date |
| **Imagen destacada** | - | Featured Image |
| **Galería** | `galeria` | Gallery |

---

# 🏷️ TAXONOMÍAS

## Taxonomía: Operación
- Alquiler
- Venta
- Alquiler vacacional

## Taxonomía: Tipo de Vivienda
- Apartamento
- Casa
- Chalet
- Piso
- Loft
- Ático
- Estudio
- Dúplex
- Bungalow
- Finca
- Adosado
- Casa de Campo
- Mansión

## Taxonomía: Ciudad (Provincia)
- Alicante
- Valencia
- ... (según necesites)

## Taxonomía: Población (Municipio)
- Paterna
- Manises
- ... (jerárquica bajo Ciudad)

---

# 🔗 RELACIONES ENTRE CPTs

```
AGENCIA (1) ──→ (N) AGENTES
AGENCIA (1) ──→ (N) INMUEBLES (agencia colaboradora)

AGENTE (1) ──→ (N) INMUEBLES (agente responsable)
AGENTE (1) ──→ (N) CLIENTES (agente asignado)
AGENTE (1) ──→ (N) VISITAS (agente que realiza)

PROPIETARIO (1) ──→ (N) INMUEBLES

CLIENTE (N) ──→ (N) INMUEBLES (favoritos/interesados)
CLIENTE (1) ──→ (N) VISITAS
CLIENTE (1) ──→ (N) SOLICITUDES

INMUEBLE (1) ──→ (N) VISITAS
INMUEBLE (N) ──→ (1) PROMOCIÓN

LEAD → se convierte en → CLIENTE
```

---

# 👥 ROLES Y PERMISOS

## Roles definidos:
1. **Administrador** - Control total
2. **Agencia** - Ve sus inmuebles relacionados
3. **Agente** - Ve sus asignaciones (clientes, inmuebles, visitas)
4. **Trabajador** - Acceso limitado definido
5. **Cliente** - Acceso frontend (favoritos, perfil)

## Permisos por Rol:

| Acción | Administrador | Agente | Trabajador | Agencia | Cliente |
|--------|--------------|--------|------------|---------|---------|
| Ver Inmuebles | ✅ | ✅ | ✅ | ✅ | ❌ |
| Nuevo Inmueble | ✅ | ✅ | ✅ | ✅ | ❌ |
| Editar Inmueble | ✅ | ❌ | ❌ | ❌ | ❌ |
| Ver Clientes | ✅ | ✅ | ✅ | ✅ | ❌ |
| Nuevo Cliente | ✅ | ✅ | ✅ | ✅ | ❌ |
| Ver Leads | ✅ | ✅ | ✅ | ✅ | ❌ |
| Ver Visitas | ✅ | ✅ | ✅ | ✅ | ❌ |
| Ver Agencias | ✅ | ✅ | ✅ | ✅ | ❌ |
| Ver Agentes | ✅ | ✅ | ✅ | ✅ | ❌ |
| Ver Propietarios | ✅ | ✅ | ✅ | ✅ | ❌ |
| Ver Promociones | ✅ | ✅ | ✅ | ✅ | ❌ |
| Ver Usuarios | ✅ | ❌ | ❌ | ❌ | ❌ |
| Perfil | ✅ | ✅ | ✅ | ✅ | ✅ |

---

# 💡 IDEAS Y FEATURES

## Features Frontend Cliente
- ✅ Registro de usuario
- ✅ Guardar en favoritos
- ✅ Editar datos de contacto
- ✅ Ajustar información de interés
- ✅ Configurar newsletter
- ✅ Borrar cuenta

## Ideas de UX
1. **Opción de añadir a favoritos** en cada inmueble
2. **Creación automática de cuenta** tras enviar formulario con favoritos guardados
3. **Botón "Vendido"** en admin que cambia CPT a `impress_property_sold`
4. **Panel de ganancias** con cálculo automático de comisiones
5. **Precios en oferta** destacados en listados
6. **Fotos animadas** en cards de listados
7. **Botón proponer precio** al lado del precio en ficha
8. **Botón "Avisarme si baja"** para suscripción a alertas

## Promoción de Inmuebles
- Sistema de destacados (fecha, switch, etc)
- Ver cómo implementar

---

# ✅ PRÓXIMOS PASOS RECOMENDADOS

1. **Crear plugin `inmopress-core`** con registro de CPTs
2. **Exportar grupos ACF** en JSON
3. **Registrar Taxonomías** con jerarquías
4. **Crear Roles personalizados** con capacidades
5. **Tema hijo base** con templates para CPTs
6. **Plantillas Gutenberg** para archive y single

---

**Documento generado:** `inmopress-estructura-completa.md`
**Fecha:** 2025-01-25
**Proyecto:** Inmopress CRM Inmobiliario
