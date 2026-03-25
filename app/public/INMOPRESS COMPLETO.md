# 📘 INMOPRESS - DOCUMENTO MAESTRO DEFINITIVO
## Resumen Completo de Campos, Funcionalidades y Plan de Implementación

**Versión:** 1.0  
**Fecha:** Enero 2025  
**Proyecto:** CRM Inmobiliario WordPress completo

---

## 📋 ÍNDICE

### PARTE 1: ARQUITECTURA DEL SISTEMA
1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [CPTs y Estructura](#cpts-y-estructura)
3. [Taxonomías Completas](#taxonomías-completas)
4. [Campos ACF por CPT](#campos-acf-por-cpt)
5. [Bloques Gutenberg](#bloques-gutenberg)
6. [Relaciones entre Entidades](#relaciones-entre-entidades)

### PARTE 2: FUNCIONALIDADES
7. [Panel Frontend](#panel-frontend)
8. [Sistema SEO Híbrido](#sistema-seo-híbrido)
9. [Plantillas Imprimibles](#plantillas-imprimibles)
10. [Email y Automatizaciones](#email-y-automatizaciones)
11. [Roles y Permisos](#roles-y-permisos)

### PARTE 3: PLAN DE ACCIÓN
12. [Plan Detallado Paso a Paso](#plan-detallado-paso-a-paso)
13. [Timeline y Recursos](#timeline-y-recursos)
14. [Checklist de Validación](#checklist-de-validación)

---

# PARTE 1: ARQUITECTURA DEL SISTEMA

---

## 1. RESUMEN EJECUTIVO

### Stack Tecnológico

```
WordPress 6.4+
├── Tema: Astra + Astra Pro
├── Constructor: Gutenberg (nativo)
├── Campos: ACF Pro
├── SEO: Rank Math
└── Plugins Custom:
    ├── inmopress-core (CPTs + Taxonomías + Roles)
    ├── inmopress-frontend (Panel agentes)
    ├── inmopress-blocks (25 bloques Gutenberg)
    └── inmopress-printables (6 plantillas impresión)
```

### Números del Proyecto

| Elemento | Cantidad | Complejidad |
|----------|----------|-------------|
| **CPTs** | 11 | Media |
| **Taxonomías** | 19 | Media |
| **Campos ACF** | 235 | Alta |
| **Field Groups** | 33 | Alta |
| **Bloques Gutenberg** | 25 | Alta |
| **Plantillas Imprimibles** | 6 | Media |
| **Roles Personalizados** | 3 | Baja |
| **Shortcodes Frontend** | 11 | Media |
| **Páginas del Panel** | 11 | Media |

**Total estimado de implementación:** 40-50 horas

---

## 2. CPTS Y ESTRUCTURA

### 2.1 Los 11 Custom Post Types

#### CPT 1: INMUEBLES (`impress_property`)
**Slug:** `inmuebles`  
**Público:** Sí  
**Archive:** Sí  
**Campos ACF:** 106 campos  
**Taxonomías:** 11 taxonomías

**Función principal:**
- Inventario de propiedades
- Ficha completa del inmueble
- SEO para posicionamiento
- Relación con propietarios, agentes y agencias

---

#### CPT 2: CLIENTES (`impress_client`)
**Slug:** `clientes`  
**Público:** No  
**Archive:** No  
**Campos ACF:** 21 campos + 2 repeaters  
**Taxonomías:** 3 taxonomías

**Función principal:**
- Gestión de contactos
- CRM comercial
- Matching de demandas
- Historial de interacciones

---

#### CPT 3: LEADS (`impress_lead`)
**Slug:** `leads`  
**Público:** No  
**Archive:** No  
**Campos ACF:** 24 campos  
**Taxonomías:** 3 taxonomías

**Función principal:**
- Captura desde formularios
- Cualificación inicial
- Conversión a Cliente
- Trazabilidad de origen

---

#### CPT 4: VISITAS (`impress_visit`)
**Slug:** `visitas`  
**Público:** No  
**Archive:** No  
**Campos ACF:** 10 campos  
**Taxonomías:** 1 taxonomía

**Función principal:**
- Programación de visitas
- Gestión de agenda
- Hoja de visita digital
- Seguimiento post-visita

---

#### CPT 5: AGENCIAS (`impress_agency`)
**Slug:** `agencias`  
**Público:** No  
**Archive:** No  
**Campos ACF:** 12 campos  
**Taxonomías:** 2 taxonomías (compartidas)

**Función principal:**
- Multi-agencia (modelo SaaS)
- Datos de contacto
- Configuración por agencia
- Aislamiento de datos

---

#### CPT 6: AGENTES (`impress_agent`)
**Slug:** `agentes`  
**Público:** No (o Sí si página equipo)  
**Archive:** Opcional  
**Campos ACF:** 10 campos  
**Taxonomías:** 1 taxonomía

**Función principal:**
- Gestión del equipo
- Asignación de inmuebles
- Página pública del agente (opcional)
- Performance tracking

---

#### CPT 7: PROPIETARIOS (`impress_owner`)
**Slug:** `propietarios`  
**Público:** No  
**Archive:** No  
**Campos ACF:** 8 campos  
**Taxonomías:** 2 taxonomías (compartidas)

**Función principal:**
- Separación datos propietarios
- Gestión de exclusivas
- Ubicación de llaves
- RGPD compliance

---

#### CPT 8: PROMOCIONES (`impress_promotion`)
**Slug:** `promociones`  
**Público:** Sí  
**Archive:** Sí  
**Campos ACF:** 8 campos  
**Taxonomías:** 1 taxonomía

**Función principal:**
- Agrupación de inmuebles
- Marketing de obra nueva
- Landings específicas
- Galería y dossier

---

#### CPT 9: TRANSACCIONES (`impress_transaction`)
**Slug:** `transacciones`  
**Público:** No  
**Archive:** No  
**Campos ACF:** 8 campos  
**Taxonomías:** 0

**Función principal:**
- Registro de cierres (venta/alquiler)
- Seguimiento de comisiones
- Métricas financieras

---

#### CPT 10: PLANTILLAS EMAIL (`impress_email_template`)
**Slug:** `plantillas-email`  
**Público:** No  
**Archive:** No  
**Campos ACF:** 7 campos  
**Taxonomías:** 0

**Función principal:**
- Gestión de plantillas de email
- Activación por disparadores
- Personalización con variables dinámicas

---

#### CPT 11: EVENTOS (`impress_event`)
**Slug:** `eventos`  
**Público:** No  
**Archive:** No  
**Campos ACF:** 21 campos  
**Taxonomías:** 0

**Función principal:**
- Tareas y agenda diaria del agente
- Calendario unificado
- Relación con cliente, lead, inmueble y propietario

---

## 3. TAXONOMÍAS COMPLETAS

### 3.1 Taxonomías de INMUEBLES (11 taxonomías)

#### 1. `impress_operation` - Operación
**Jerárquica:** No  
**Pública:** Sí  
**Slug:** `operacion`

**Términos iniciales:**
- Venta
- Alquiler
- Alquiler vacacional
- Traspaso

**Uso SEO:** `/venta/valencia/piso/`

---

#### 2. `impress_property_type` - Tipo de Vivienda
**Jerárquica:** No  
**Pública:** Sí  
**Slug:** `tipo`

**Términos iniciales (18):**
- Piso
- Ático
- Dúplex
- Tríplex
- Chalet independiente
- Chalet pareado
- Chalet adosado
- Planta baja
- Estudio / Loft
- Apartamento
- Oficina
- Local comercial
- Nave industrial
- Terreno / Parcela
- Solar
- Garaje
- Trastero
- Edificio

**Uso SEO:** `/venta/valencia/piso/`

---

#### 3. `impress_province` - Provincia
**Jerárquica:** Sí  
**Pública:** Sí  
**Slug:** `provincia`

**Términos ejemplo:**
- Valencia
- Alicante
- Castellón
- Madrid
- Barcelona

**Uso SEO:** `/venta/valencia/`

---

#### 4. `impress_city` - Ciudad/Municipio
**Jerárquica:** Sí (bajo Provincia)  
**Pública:** Sí  
**Slug:** `ciudad`

**Términos ejemplo (bajo Valencia):**
- Valencia ciudad
- Paterna
- Torrent
- Mislata
- Burjassot
- (etc - según zona operativa)

**Uso SEO:** `/venta/valencia/paterna/`

---

#### 5. `impress_property_group` - Agrupación
**Jerárquica:** No  
**Pública:** Sí  
**Slug:** `grupo`

**Términos iniciales:**
- Villas
- Apartamentos
- Casas
- Chalets
- Terrenos
- Solares

**Uso:** Clasificación marketing

---

#### 6. `impress_features` - Características Premium
**Jerárquica:** No  
**Pública:** Sí  
**Slug:** `caracteristica`

**Términos iniciales (13):**
- Lujo
- Exclusivo
- Frente al mar
- Vistas al mar
- Vistas a la montaña
- Primera línea playa
- Campo de golf
- Urbanización privada
- Obra nueva
- Llave en mano
- Con piscina privada
- Con jardín privado
- Domótica

**Uso:** Filtros premium, SEO

---

#### 7. `impress_condition` - Estado de Conservación
**Jerárquica:** No  
**Pública:** Sí  
**Slug:** `estado`

**Términos iniciales (7):**
- Obra nueva
- Buen estado / A estrenar
- Reformado recientemente
- A reformar
- En construcción
- Para rehabilitar
- En ruina / Solar

**Uso:** Filtros, SEO

---

#### 8. `impress_energy_rating` - Certificación Energética
**Jerárquica:** No  
**Pública:** Sí  
**Slug:** `certificacion-energetica`

**Términos iniciales (12):**
- A (más eficiente)
- B
- C
- D
- E
- F
- G (menos eficiente)
- En trámite
- Sin certificación
- No requerido
- Exento
- Pendiente renovación

**Uso:** Filtros, legal, SEO

---

#### 9. `impress_amenities` - Equipamiento Top
**Jerárquica:** No  
**Pública:** Sí  
**Slug:** `equipamiento`

**Términos iniciales (13):**
- Ascensor
- Piscina
- Piscina comunitaria
- Garaje incluido
- Terraza
- Balcón
- Aire acondicionado
- Calefacción
- Trastero
- Jardín privado
- Parking comunitario
- Amueblado
- Cocina equipada
- Armarios empotrados

**Uso:** Filtros principales

---

#### 10. `impress_heating` - Tipo de Calefacción
**Jerárquica:** No  
**Pública:** Sí  
**Slug:** `calefaccion`

**Términos iniciales (12):**
- Sin calefacción
- Calefacción central
- Calefacción individual
- Gas natural
- Gas ciudad
- Gasóleo
- Eléctrica
- Aerotermia
- Bomba de calor
- Suelo radiante
- Radiadores
- Aire acondicionado con bomba calor

**Uso:** Filtros, ficha técnica

---

#### 11. `impress_orientation` - Orientación
**Jerárquica:** No  
**Pública:** Sí  
**Slug:** `orientacion`

**Términos iniciales (8):**
- Norte
- Sur
- Este
- Oeste
- Noreste
- Noroeste
- Sureste
- Suroeste

**Uso:** Ficha técnica, filtros

---

### 3.2 Taxonomías de CLIENTES/LEADS (3 taxonomías)

#### 12. `impress_lead_status` - Estado Lead/Cliente
**Jerárquica:** No  
**Pública:** No  
**Slug:** N/A

**Términos iniciales (11):**
- Nuevo lead
- Contactado
- Cualificado
- Interesado
- Visita programada
- Visita realizada
- Muy interesado / Segunda visita
- Oferta presentada
- Negociación
- Reservado / Arras
- Firmado / Ganado
- Perdido / Descartado

**Uso:** Pipeline CRM

---

#### 13. `impress_lead_source` - Canal de Entrada
**Jerárquica:** No  
**Pública:** No  
**Slug:** N/A

**Términos iniciales (11):**
- Web propia
- Idealista
- Fotocasa
- Habitaclia
- Llamada directa
- WhatsApp
- Email directo
- Paso por oficina
- Referido
- Redes sociales (Facebook, Instagram)
- Google Ads
- Otro

**Uso:** Tracking origen, ROI

---

#### 14. `impress_language` - Idioma
**Jerárquica:** No  
**Pública:** No  
**Slug:** N/A

**Términos iniciales (8):**
- Español
- Inglés
- Francés
- Alemán
- Ruso
- Chino
- Árabe
- Otro

**Uso:** Asignación agente, comunicación

---

### 3.3 Taxonomías de VISITAS (1 taxonomía)

#### 15. `impress_visit_status` - Estado Visita
**Jerárquica:** No  
**Pública:** No  
**Slug:** N/A

**Términos iniciales (6):**
- Programada
- Confirmada
- Realizada
- No se presentó
- Cancelada por cliente
- Cancelada por agente

**Uso:** Gestión agenda

---

### 3.4 Taxonomías de AGENTES (1 taxonomía)

#### 16. `impress_agent_specialty` - Especialización
**Jerárquica:** No  
**Pública:** No (o Sí si pública)  
**Slug:** N/A

**Términos iniciales (8):**
- Venta residencial
- Alquiler residencial
- Comercial
- Industrial
- Lujo / Premium
- Obra nueva
- Inversión
- Terrenos / Solares

**Uso:** Asignación inteligente

---

### 3.5 Taxonomías de PROMOCIONES (1 taxonomía)

#### 17. `impress_promotion_status` - Estado Promoción
**Jerárquica:** No  
**Pública:** Sí  
**Slug:** `estado-promocion`

**Términos iniciales (7):**
- En proyecto
- En construcción
- Preventa
- A la venta
- Últimas unidades
- Entregada
- Finalizada

**Uso:** Ciclo de vida obra nueva

---

### 3.6 Taxonomías COMPARTIDAS (ubicación - ya contadas arriba)

Las taxonomías `impress_province` y `impress_city` se comparten entre:
- Inmuebles
- Clientes (preferencias)
- Agencias (ubicación)
- Propietarios (ubicación)

---

## 4. CAMPOS ACF POR CPT

### 4.1 INMUEBLES (106 campos en 11 Field Groups)

#### Field Group 1: Información General (7 campos)
```
1. publicada (True/False)
2. vendida (True/False)
3. reservada (True/False)
4. direccion (Text)
5. descripcion (WYSIWYG)
6. referencia (Text - Required - Unique)
7. zona (Text)
```

#### Field Group 2: Ubicación (3 campos)
```
1. gps_lat (Text)
2. gps_lng (Text)
3. ocultar_direccion (True/False)
```

#### Field Group 3: Relaciones (3 campos)
```
1. agencia_colaboradora (Post Object → impress_agency)
2. agente (Post Object → impress_agent)
3. propietario (Post Object → impress_owner)
```

#### Field Group 4: Características Físicas (14 campos)
```
1. superficie_util (Number, suffix: m²)
2. superficie_construida (Number, suffix: m²)
3. superficie_parcela (Number, suffix: m²)
4. plantas (Number)
5. ano (Number, min: 1800, max: 2100)
6. dormitorios (Number)
7. banos (Number)
8. banos_suite (Number)
9. cocinas (Number)
10. salones (Number)
11. balcones (Number)
12. terrazas (Number)
13. trasteros (Number)
14. planta (Select: Bajo, 1º, 2º, 3º, 4º, 5º+, Ático, Sótano)
```

#### Field Group 5: Detalles Técnicos (8 campos)
```
1. ficha_energetica (File - PDF)
2. ventanas (Select: Aluminio, PVC, Madera, Climalit, Doble cristal)
3. tipo_suelo (Select: Mármol, Gres, Parquet, Tarima, Cerámica, Hidráulico)
4. jardin (Select: No, Privado, Comunitario)
5. piscina (Select: No, Privada, Comunitaria)
6. medida_piscina (Text)
7. garajes (Number - cantidad plazas)
8. tipo_estacionamiento (Select: Subterráneo, Superficie, Mixto)
```

#### Field Group 6: Costes y Gastos (4 campos)
```
1. ibi (Number, suffix: €/año)
2. impuesto_basura (Number, suffix: €/año)
3. gastos_comunidad (Number, suffix: €/mes)
4. emisiones_co2 (Number, suffix: kg CO2/m²año)
```

#### Field Group 7: Distancias a Servicios (11 campos - todos Select)
```
Valores: 1-5 min, 5-10 min, 10-15 min, 15-20 min, +20 min, No aplica

1. distancia_autobus
2. distancia_metro
3. distancia_colegios
4. distancia_supermercados
5. distancia_centros_salud
6. distancia_areas_verdes
7. distancia_centros_comerciales
8. distancia_gimnasios
9. distancia_farmacias
10. distancia_teatros
11. distancia_cines
```

#### Field Group 8: Características Secundarias (33 switches - True/False)
```
1. barbacoa
2. lavavajillas
3. gimnasio_comunitario
4. encimera_granito
5. lavanderia
6. spa
7. luminoso
8. horno
9. puerta_blindada
10. patio
11. conserje
12. buhardilla
13. chimenea
14. agua_potable
15. alarma
16. porche
17. despensa
18. portero_automatico
19. jacuzzi
20. sotano
21. aislamiento_termico
22. riego_automatico
23. internet_fibra
24. tv_satelite
25. vitroceramica
26. frigorifico
27. microondas
28. zona_infantil
29. tenis
30. padel
31. muebles_jardin
32. solárium
33. adaptado_minusvalidos
```

#### Field Group 9: Datos Venta (7 campos - Conditional Logic: operacion = venta)
```
1. precio_venta_propietario (Number)
2. precio_venta_minimo (Number)
3. precio_venta (Number - Required si venta)
4. tipo_descuento_venta (Select: Porcentaje, Cantidad fija)
5. cantidad_descuento_venta (Number)
6. tipo_comision_venta (Select: Porcentaje, Cantidad fija)
7. cantidad_comision_venta (Number)
```

#### Field Group 10: Datos Alquiler (12 campos - Conditional Logic: operacion = alquiler)
```
1. precio_alquiler_propietario (Number)
2. precio_alquiler (Number - Required si alquiler, suffix: €/mes)
3. deposito_fianza (Number, suffix: €)
4. tipo_descuento_alquiler (Select)
5. cantidad_descuento_alquiler (Number)
6. tipo_comision_alquiler (Select)
7. cantidad_comision_alquiler (Number)
8. mascotas_permitidas (True/False)
9. periodo_pago (Select: Mensual, Trimestral, Anual)
10. plazo_minimo_alquiler (Number, suffix: meses)
11. seguro_obligatorio (Select: No, Opcional, Obligatorio)
12. fumar_permitido (True/False)
```

#### Field Group 11: Media (4 campos)
```
1. fotos (Gallery)
2. video_url (URL - YouTube/Vimeo)
3. tour_360_url (URL - Matterport, etc)
4. planos (File - PDF o Image)
```

**TOTAL INMUEBLES: 106 campos**

---

### 4.2 CLIENTES (21 campos + 2 repeaters en 5 Field Groups)

#### Field Group 1: Datos Personales (5 campos)
```
1. nombre (Text - Required)
2. apellidos (Text)
3. telefono (Text)
4. correo (Email - Required)
5. direccion (Textarea)
```

#### Field Group 2: Clasificación (2 campos)
```
1. semaforo_estado (Select: hot, warm, cold)
2. puntuacion (Range: 0-10)
```

#### Field Group 3: Preferencias de Búsqueda (8 campos)
```
1. interes (Select: compra, alquiler, inversion)
2. presupuesto_min (Number)
3. presupuesto_max (Number)
4. zona_interes (Taxonomy Select: impress_city - Multiple)
5. dormitorios_min (Number)
6. banos_min (Number)
7. superficie_min (Number)
8. notas_preferencias (Textarea)
```

#### Field Group 4: Gestión (3 campos)
```
1. agente_asignado (Post Object → impress_agent)
2. fecha_proximo_contacto (Date Picker)
3. notas_internas (Textarea)
```

#### Field Group 5: Configuración (1 campo + 2 repeaters)
```
1. newsletter_activo (True/False)

REPEATER 1: visitas_realizadas
  - inmueble (Post Object → impress_property)
  - fecha (Date Picker)
  - valoracion (Range: 1-5)
  - nota (Text)

REPEATER 2: solicitudes
  - inmueble (Post Object → impress_property)
  - fecha (Date)
  - tipo (Select: Info, Visita, Tasación)
  - estado (Select: Pendiente, Atendida, Cerrada)
```

**TOTAL CLIENTES: 21 campos + 2 repeaters**

---

### 4.3 LEADS (24 campos en 5 Field Groups)

**Nota:** Los leads tienen los mismos campos que Clientes + 3 adicionales

#### Campos adicionales:
```
22. convertido_cliente (True/False)
23. cliente_relacionado (Post Object → impress_client)
24. fecha_conversion (Date Picker)
```

**TOTAL LEADS: 24 campos + 2 repeaters**

---

### 4.4 VISITAS (10 campos en 1 Field Group)

```
1. fecha_hora (Date Time Picker - Required)
2. cliente (Post Object → impress_client - Required)
3. inmueble (Post Object → impress_property - Required)
4. agente (Post Object → impress_agent - Required)
5. duracion (Number, suffix: minutos)
6. valoracion_cliente (Range: 1-5)
7. interes_mostrado (Select: Ninguno, Bajo, Medio, Alto, Muy alto)
8. notas (Textarea)
9. firma_cliente (Image)
10. fotos_visita (Gallery)
```

**TOTAL VISITAS: 10 campos**

---

### 4.5 AGENCIAS (12 campos en 3 Field Groups)

#### Field Group 1: Contacto (4 campos)
```
1. telefono (Text)
2. email (Email)
3. web (URL)
4. direccion (Textarea)
```

#### Field Group 2: Datos de la Agencia (7 campos)
```
1. nombre_comercial (Text)
2. razon_social (Text)
3. cif (Text)
4. logo (Image)
5. ciudad (Taxonomy: impress_city)
6. codigo_postal (Text)
7. horario (Textarea)
```

#### Field Group 3: Usuario (1 campo)
```
1. usuario_wordpress (User - Select)
```

**TOTAL AGENCIAS: 12 campos**

---

### 4.6 AGENTES (10 campos en 2 Field Groups)

#### Field Group 1: Vinculación y Datos (7 campos)
```
1. usuario_wordpress (User - Required)
2. agencia_relacionada (Post Object → impress_agency)
3. nombre (Text)
4. apellidos (Text)
5. telefono (Text)
6. email (Email)
7. biografia (Textarea)
```

#### Field Group 2: Perfil Público (3 campos)
```
1. avatar (Image)
2. activo (True/False)
3. color_calendario (Color Picker)
```

**TOTAL AGENTES: 10 campos**

---

### 4.7 PROPIETARIOS (8 campos en 1 Field Group)

```
1. nombre (Text - Required)
2. apellidos (Text)
3. telefono (Text - Required)
4. email (Email)
5. dni_cif (Text)
6. direccion (Textarea)
7. notas (Textarea)
8. puede_publicar_directo (True/False)
```

**TOTAL PROPIETARIOS: 8 campos**

---

### 4.8 PROMOCIONES (8 campos en 2 Field Groups)

#### Field Group 1: Datos (5 campos)
```
1. descripcion (WYSIWYG)
2. inmuebles_relacionados (Relationship → impress_property)
3. fecha_inicio (Date Picker)
4. fecha_fin (Date Picker)
5. fecha_entrega_estimada (Date Picker)
```

#### Field Group 2: Media (3 campos)
```
1. promotora_nombre (Text)
2. galeria (Gallery)
3. dossier_pdf (File)
```

**TOTAL PROMOCIONES: 8 campos**

---

## RESUMEN CAMPOS ACF

| CPT | Campos | Field Groups | Complejidad |
|-----|--------|--------------|-------------|
| Inmuebles | 106 | 11 | ⭐⭐⭐⭐⭐ |
| Clientes | 21 + 2 rep | 5 | ⭐⭐⭐ |
| Leads | 24 + 2 rep | 5 | ⭐⭐⭐ |
| Visitas | 10 | 1 | ⭐⭐ |
| Agencias | 12 | 3 | ⭐⭐ |
| Agentes | 10 | 2 | ⭐⭐ |
| Propietarios | 8 | 1 | ⭐ |
| Promociones | 8 | 2 | ⭐⭐ |
| Transacciones | 8 | 1 | ⭐⭐ |
| Plantillas Email | 7 | 1 | ⭐⭐ |
| Eventos | 21 | 1 | ⭐⭐ |
| **TOTAL** | **235** | **33** | |

---

## 5. BLOQUES GUTENBERG

### 25 Bloques Organizados en 7 Categorías

#### Categoría 1: ESENCIALES (6 bloques) ⭐⭐⭐

1. **hero-inmobiliaria**
   - Banner principal con buscador opcional
   - Título, subtítulo, imagen fondo, 2 CTAs

2. **buscador-inmuebles**
   - 3 variantes: Horizontal, Vertical, Compacto
   - Filtros configurables

3. **grid-inmuebles**
   - Listado cuadrícula
   - Fuente: Recientes/Destacados/Taxonomía/Manual
   - 2-4 columnas

4. **ficha-tecnica**
   - Características inmueble
   - Layout: Tabla/Cards/Iconos

5. **galeria-inmueble**
   - Grid+Lightbox / Carrusel / Mosaico / Fullscreen
   - Marca de agua opcional

6. **formulario-contacto-inmueble**
   - Genera lead automático
   - Email a agente asignado

#### Categoría 2: PÁGINAS PÚBLICAS (1 bloque) ⭐⭐⭐

7. **stats-numeros**
   - Contador números destacados
   - 500+ Inmuebles, 1000+ Clientes, etc

#### Categoría 3: LANDING PAGES (3 bloques) ⭐⭐

8. **destacado-promocion**
   - Ficha completa de promoción
   - Galería + Inmuebles + Form

9. **servicios-inmobiliaria**
   - Grid de servicios con iconos
   - 2-4 columnas

10. **zonas-destacadas**
    - Ciudades con inmuebles
    - Grid/Carrusel/Lista

#### Categoría 4: BÚSQUEDA Y FILTROS (2 bloques) ⭐⭐⭐

11. **filtros-avanzados**
    - Sidebar/Top/Modal
    - Todos los filtros configurables

12. **mapa-interactivo**
    - Google Maps con inmuebles
    - Clusters, popup con info

#### Categoría 5: LISTADOS (2 bloques) ⭐⭐

13. **tarjeta-destacada**
    - Inmueble destacado individual
    - 4 layouts

14. **carrusel-inmuebles**
    - Slider de inmuebles
    - Auto-play, responsive

#### Categoría 6: DETALLE INMUEBLE (3 bloques) ⭐⭐⭐

15. **caracteristicas-equipamiento**
    - Lista de extras
    - Agrupado por categorías

16. **ubicacion-mapa**
    - Ubicación + distancias servicios
    - Dirección exacta/aproximada

17. **inmuebles-similares**
    - Recomendaciones
    - Algoritmo de matching

#### Categoría 7: MARKETING (4 bloques) ⭐⭐

18. **equipo-agentes**
    - Grid del equipo
    - Foto, bio, contacto

19. **testimonios**
    - Reseñas clientes
    - Sistema estrellas

20. **blog-noticias**
    - Posts recientes
    - Filtros categoría

21. **newsletter**
    - Suscripción alertas
    - Integración Mailchimp/Sendinblue

#### Categoría 8: CONTENIDO (3 bloques) ⭐

22. **faq**
    - Acordeón preguntas
    - Schema FAQ para SEO

23. **cta**
    - Call to action
    - Banner/Caja/Lateral

24. **comparador**
    - Tabla comparativa inmuebles
    - 2-4 propiedades

25. **(Reserva para futuros)**

---

## 6. RELACIONES ENTRE ENTIDADES

### Diagrama de Relaciones

```
INMUEBLE
├── → Propietario (1 a 1)
├── → Agente asignado (1 a 1)
├── → Agencia (1 a 1)
├── → Promoción (N a 1)
├── ← Visitas (1 a N)
└── ← Clientes interesados (N a N via favoritos)

CLIENTE
├── → Agente asignado (1 a 1)
├── → Inmuebles favoritos (N a N)
├── ← Visitas (1 a N)
└── ← Solicitudes (repeater interno)

LEAD
├── → Cliente convertido (1 a 1 si convertido)
└── → Agente asignado (1 a 1)

VISITA
├── → Inmueble (1 a 1) Required
├── → Cliente (1 a 1) Required
└── → Agente (1 a 1) Required

AGENTE
├── → Agencia (1 a 1)
├── → Usuario WordPress (1 a 1)
├── ← Inmuebles asignados (1 a N)
├── ← Clientes asignados (1 a N)
└── ← Visitas asignadas (1 a N)

AGENCIA
├── → Usuario WordPress manager (1 a 1)
├── ← Agentes (1 a N)
└── ← Inmuebles (1 a N)

PROPIETARIO
├── ← Inmuebles (1 a N)
└── (Sin relaciones bidireccionales automáticas)

PROMOCIÓN
└── → Inmuebles (Relationship N a N)
```

### Relaciones Bidireccionales Automáticas

**Implementadas con hooks ACF:**

1. **Inmueble ↔ Agente**
   - Al asignar agente a inmueble → se añade inmueble a lista del agente

2. **Lead ↔ Cliente**
   - Al convertir lead → se crea cliente y se vincula

3. **Visita ↔ Cliente ↔ Inmueble**
   - Al crear visita → se registra en historial cliente + inmueble

---

# PARTE 2: FUNCIONALIDADES

---

## 7. PANEL FRONTEND

### 7.1 Estructura del Panel

**URL Base:** `/mi-panel/`

**11 Páginas:**

1. `/mi-panel/` - Dashboard
2. `/mi-panel/inmuebles/` - Listado
3. `/mi-panel/inmuebles/nuevo/` - Formulario crear
4. `/mi-panel/inmuebles/editar/?id=X` - Formulario editar
5. `/mi-panel/clientes/` - Listado
6. `/mi-panel/clientes/nuevo/` - Formulario
7. `/mi-panel/clientes/editar/?id=X` - Formulario
8. `/mi-panel/visitas/` - Calendario/Listado
9. `/mi-panel/visitas/nueva/` - Formulario
10. `/mi-panel/propietarios/` - Listado
11. `/mi-panel/propietarios/nuevo/` - Formulario

### 7.2 Dashboard Principal

**KPIs mostrados:**
- Inmuebles activos
- Clientes totales
- Visitas hoy
- Leads HOT

**Widgets:**
- Próximas visitas (5 siguientes)
- Últimos leads (5 recientes)
- Accesos rápidos (4 botones)
- Tareas pendientes (opcional)

### 7.3 Listados con Filtros

**Inmuebles:**
- Filtros: Referencia, Operación, Estado
- Acciones: Editar, Ver, Eliminar, Imprimir

**Clientes:**
- Filtros: Nombre, Semáforo, Estado
- Acciones: Editar, Ver, Eliminar

### 7.4 Formularios ACF Frontend

**Tecnología:** ACF Frontend Forms nativo

**Características:**
- Todos los campos del CPT
- Validación en tiempo real
- Upload de imágenes con preview
- Auto-save
- Campos condicionales
- Redirección post-guardado

### 7.5 Sistema de Permisos

**Por Rol:**

| Acción | Admin | Agencia | Agente | Trabajador |
|--------|-------|---------|--------|------------|
| Ver todos inmuebles | ✅ | ✅ | ❌ | ❌ |
| Ver sus inmuebles | ✅ | ✅ | ✅ | ✅ |
| Crear inmuebles | ✅ | ✅ | ✅ | ✅ |
| Publicar inmuebles | ✅ | ✅ | ✅ | ❌ |
| Eliminar inmuebles | ✅ | ✅ | ❌ | ❌ |
| Ver todos clientes | ✅ | ✅ | ❌ | ❌ |
| Ver sus clientes | ✅ | ✅ | ✅ | ✅ |
| Gestionar agentes | ✅ | ✅ | ❌ | ❌ |

---

## 8. SISTEMA SEO HÍBRIDO

### 8.1 SEO Automático (IA + Rank Math)

**Para cada inmueble publicado:**

1. **Meta Title automático:**
   ```
   {Tipo} en {Operación} en {Ciudad} - {Características} | {Sitio}
   
   Ejemplo:
   Piso en Venta en Valencia - 3 hab, 120m², Terraza | Inmobiliaria XYZ
   ```

2. **Meta Description automática:**
   ```
   {Operación} {Tipo} en {Ciudad}, {Zona}. {Superficie}m², {Habitaciones} hab, {Baños} baños. 
   {Características destacadas}. Ref: {Referencia}. Precio: {Precio}€. ¡Visítalo!
   
   Ejemplo:
   Venta Piso en Valencia, Ruzafa. 120m², 3 hab, 2 baños. Terraza, Ascensor, Reformado. 
   Ref: N4569Z. Precio: 185.000€. ¡Visítalo!
   ```

3. **Schema.org RealEstateListing:**
   ```json
   {
     "@type": "RealEstateListing",
     "name": "Título inmueble",
     "price": "185000",
     "priceCurrency": "EUR",
     "numberOfRooms": 3,
     "floorSize": {"@type": "QuantitativeValue", "value": 120}
   }
   ```

4. **URLs semánticas:**
   ```
   /venta/valencia/piso-3-habitaciones-ruzafa/
   /alquiler/paterna/chalet-piscina-jardin/
   ```

### 8.2 SEO Manual (Editable por Admin/Agente)

**Campos editables en cada inmueble:**
- Meta Title personalizado (override automático)
- Meta Description personalizada
- Texto introductorio (encima de características)
- FAQ personalizado (repeater)

### 8.3 SEO Taxonomías

**Cada taxonomía tiene ACF:**
- SEO Title
- SEO Description
- Texto intro (H1 + párrafo)
- FAQ (repeater)

**Ejemplo página taxonomía:**
```
URL: /venta/valencia/piso/

H1: Pisos en Venta en Valencia
Texto intro: Descubre nuestra selección de pisos...
[Grid de inmuebles]
FAQ:
- ¿Cuánto cuesta un piso en Valencia?
- ¿Qué zonas son mejores?
```

### 8.4 Sitemap Personalizado

**Incluye:**
- ✅ Inmuebles (prioridad 0.8)
- ✅ Promociones (prioridad 0.7)
- ✅ Taxonomías públicas (prioridad 0.6)
- ❌ CPTs privados (clientes, leads, etc)

---

## 9. PLANTILLAS IMPRIMIBLES

### 6 Plantillas Profesionales

#### 1. Cartel Vertical A4 (21x29.7cm)
**Uso:** Escaparate estándar

**Incluye:**
- Logo agencia
- Imagen principal grande
- Precio destacado
- Características (hab/baños/m²)
- Ubicación
- Referencia
- QR code a ficha online
- Datos contacto footer

#### 2. Cartel Horizontal A4 (29.7x21cm)
**Uso:** Escaparate ancho

**Incluye:**
- Galería 4 fotos
- Datos completos lado derecho
- Características extras
- Descripción corta

#### 3. Cartel Compacto A5 (14.8x21cm)
**Uso:** Tablones, espacios reducidos

**Incluye:**
- Foto + precio + contacto
- Info mínima
- QR grande

#### 4. Banner Premium A3 (29.7x42cm)
**Uso:** Propiedades exclusivas

**Incluye:**
- Gran visual
- Toda la información
- Diseño premium

#### 5. Ficha Técnica Completa A4
**Uso:** Entregar a clientes, visitas

**Incluye:**
- Todas características
- Galería 6 fotos
- Plano ubicación
- Condiciones venta/alquiler

#### 6. QR Sticker (10x10cm)
**Uso:** Stickers adhesivos

**Incluye:**
- QR grande
- Referencia
- Precio
- Logo

### Sistema de Generación

**Desde el admin:**
- Botones "Imprimir" en cada inmueble
- Selección de plantilla
- Preview antes de imprimir
- Generación PDF (opcional)

**Desde el panel frontend:**
- Shortcode `[inmopress_print_buttons]`
- 6 botones de acción
- Apertura en nueva ventana
- Auto-print al cargar

---

## 10. EMAIL Y AUTOMATIZACIONES

### 10.1 Sistema de Emails

**CPT adicional:** `impress_email_template`

**Plantillas por defecto:**
1. Bienvenida nuevo lead
2. Confirmación visita
3. Recordatorio visita (24h antes)
4. Seguimiento post-visita
5. Nuevos inmuebles matching
6. Solicitud documentación propietario
7. Notificación oferta recibida

**Variables dinámicas:**
```
{{cliente_nombre}}
{{inmueble_titulo}}
{{inmueble_precio}}
{{inmueble_url}}
{{agente_nombre}}
{{agente_telefono}}
{{fecha_visita}}
etc...
```

### 10.2 Automatizaciones

**Trigger → Condition → Action**

**Ejemplo 1: Nueva propiedad matching**
```
Trigger: Inmueble pasa a "Publicada"
Condition: Coincide con criterios de Cliente
Action: 
  - Enviar email al cliente
  - Crear tarea "Seguimiento" al agente
```

**Ejemplo 2: Lead sin contactar**
```
Trigger: Lead creado
Condition: No hay evento "llamada" en 48h
Action:
  - Email automático al lead
  - Tarea al agente asignado
```

**Ejemplo 3: Visita completada**
```
Trigger: Visita → estado "Realizada"
Action:
  - Email feedback al cliente
  - Tarea seguimiento 48h al agente
```

### 10.3 Integración Email Marketing

**Proveedores compatibles:**
- Mailchimp
- Sendinblue
- ActiveCampaign
- Webhook custom

**Sincronización bidireccional:**
- Lead desde formulario → Email marketing
- Respuesta email → Actualizar estado lead

---

## 11. ROLES Y PERMISOS

### 11.1 Los 5 Roles

#### 1. Administrador (WordPress nativo)
- Acceso total
- Gestión de todo

#### 2. Agencia (custom)
**Capabilities:**
- Ver todos sus inmuebles
- Crear inmuebles (quedan en borrador)
- Ver todos sus clientes
- Ver todos sus agentes
- Gestionar su perfil de agencia
- No puede eliminar

#### 3. Agente (custom)
**Capabilities:**
- Ver y editar sus inmuebles
- Crear y publicar inmuebles
- Ver y editar sus clientes
- Crear y gestionar visitas
- Ver su calendario
- No puede eliminar
- No ve otros agentes

#### 4. Trabajador (custom)
**Capabilities:**
- Ver sus inmuebles
- Crear inmuebles (borrador)
- Ver sus clientes
- Crear clientes
- No puede publicar
- No puede eliminar

#### 5. Cliente (WordPress subscriber + custom)
**Capabilities:**
- Solo acceso frontend
- Ver su perfil
- Ver sus favoritos
- Solicitar visitas
- Sin acceso admin

### 11.2 Aislamiento Multi-Agencia

**Nivel 1: Query filters**
```php
// Cada query filtra por agency_id
$args['meta_query'][] = array(
    'key' => 'agencia_colaboradora',
    'value' => $current_agency_id,
);
```

**Nivel 2: Capabilities**
- Rol Agencia solo ve su contenido
- Rol Agente solo ve contenido de su agencia

**Nivel 3: UI**
- Menús filtrados
- Listados filtrados
- Formularios pre-rellenados

---

# PARTE 3: PLAN DE ACCIÓN

---

## 12. PLAN DETALLADO PASO A PASO

### FASE 0: PREPARACIÓN (2 horas)

#### 0.1 Infraestructura
- [ ] Contratar hosting (min 2GB RAM, PHP 8.0+, MySQL 5.7+)
- [ ] Configurar dominio y SSL
- [ ] Acceso SSH/FTP configurado
- [ ] Base de datos creada

#### 0.2 Recursos
- [ ] Descargar WordPress 6.4+
- [ ] Comprar licencia Astra Pro
- [ ] Comprar licencia ACF Pro
- [ ] Preparar logo de la inmobiliaria
- [ ] Recopilar datos de contacto

#### 0.3 Estructura Local
```
proyecto-inmopress/
├── documentacion/
│   ├── este-documento-maestro.md
│   ├── INMOPRESS-ESTRUCTURA-DEFINITIVA-ACF-TAX.md
│   ├── INMOPRESS-PANEL-ASTRA-PLUGIN.md
│   ├── INMOPRESS-PLANTILLAS-IMPRIMIBLES.md
│   └── INMOPRESS-ACF-BLOCKS-SISTEMA-COMPLETO.md
├── plugins/
│   ├── inmopress-core/
│   ├── inmopress-frontend/
│   ├── inmopress-blocks/
│   └── inmopress-printables/
├── exports/
│   └── acf-json/
└── backups/
```

---

### FASE 1: INSTALACIÓN BASE (1 hora)

#### 1.1 Instalar WordPress
**Via Softaculous (recomendado):**
```
1. Panel hosting → Softaculous → WordPress
2. Dominio: tudominio.com
3. Directorio: (vacío para raíz)
4. Nombre sitio: Inmopress
5. Admin: tu_usuario
6. Email: tu_email
7. INSTALAR
```

**Via Manual (SSH):**
```bash
cd public_html
wget https://wordpress.org/latest.zip
unzip latest.zip
mv wordpress/* .
rm -rf wordpress latest.zip
cp wp-config-sample.php wp-config.php
nano wp-config.php
# Editar DB_NAME, DB_USER, DB_PASSWORD
# Cambiar salts: https://api.wordpress.org/secret-key/1.1/salt/
```

#### 1.2 Configuración Inicial
```
Login: tudominio.com/wp-admin

Ajustes → Generales:
  - Título: Inmopress CRM Inmobiliario
  - Descripción: (vaciar o breve)
  - Zona horaria: Madrid
  - Formato fecha: d/m/Y
  - Idioma: Español

Ajustes → Enlaces permanentes:
  - ☑ Nombre de la entrada
  - GUARDAR (2 veces para flush)

Ajustes → Lectura:
  - Entradas blog: 20

Usuarios → Tu perfil:
  - Cambiar contraseña fuerte
  - Idioma interfaz: Español
```

#### 1.3 Limpieza Inicial
```
- Plugins → Eliminar Hello Dolly, Akismet (si no se usa)
- Apariencia → Temas → Eliminar todos excepto uno
- Entradas → Eliminar "Hola mundo"
- Páginas → Eliminar "Página ejemplo"
- Comentarios → Ajustes → ☐ Permitir comentarios
```

**Tiempo:** 1 hora  
**Checkpoint:** WordPress funcionando, admin accesible

---

### FASE 2: PLUGINS ESENCIALES (1.5 horas)

#### 2.1 Instalar Astra
```
Apariencia → Temas → Añadir nuevo
Buscar: "Astra"
Instalar → Activar
```

#### 2.2 Instalar Astra Pro
```
Apariencia → Temas → Añadir nuevo → Subir tema
Seleccionar: astra-pro.zip
Instalar → Activar

Appearance → Astra Options
License → Ingresar license key → Activate
```

#### 2.3 Instalar ACF Pro
```
Plugins → Añadir nuevo → Subir plugin
Seleccionar: advanced-custom-fields-pro.zip
Instalar → Activar

ACF → Updates
License Key: (pegar) → Update License
```

#### 2.4 Instalar Rank Math
```
Plugins → Añadir nuevo → Buscar "Rank Math"
Instalar → Activar

Setup Wizard:
  1. Account: Conectar (opcional) o Skip
  2. Site Type: Local Business
  3. Site Logo: Subir logo
  4. Social Profiles: Facebook, Instagram URLs
  5. Sitemap: ☑ Enable
  6. 404 Monitor: ☑ Enable
  7. Schema: ☑ Enable
  8. Finish
```

#### 2.5 Plugins Adicionales
```
UpdraftPlus (Backups):
  - Instalar → Activar
  - Settings → Files: Weekly, DB: Daily
  - Remote: Google Drive o Dropbox

WP Rocket (Cache - opcional, de pago):
  - Instalar → Activar
  - Settings → Basic: ☑ Enable all

Wordfence Security:
  - Instalar → Activar
  - Scan → Start first scan
```

**Tiempo:** 1.5 horas  
**Checkpoint:** Todos los plugins instalados y configurados

---

### FASE 3: PLUGIN INMOPRESS CORE (3 horas)

#### 3.1 Crear Estructura del Plugin

**Crear carpeta:**
```
/wp-content/plugins/inmopress-core/
├── inmopress-core.php
├── includes/
│   ├── class-cpts.php
│   ├── class-taxonomies.php
│   └── class-roles.php
├── acf-json/
└── README.md
```

#### 3.2 Código del Plugin Principal

**Archivo:** `inmopress-core.php`

```php
<?php
/**
 * Plugin Name: Inmopress Core
 * Description: CPTs, Taxonomías y Roles del CRM Inmobiliario
 * Version: 1.0.0
 * Author: Tu Nombre
 * Text Domain: inmopress-core
 */

if (!defined('ABSPATH')) exit;

define('INMOPRESS_CORE_PATH', plugin_dir_path(__FILE__));
define('INMOPRESS_CORE_VERSION', '1.0.0');

class Inmopress_Core {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    private function load_dependencies() {
        require_once INMOPRESS_CORE_PATH . 'includes/class-cpts.php';
        require_once INMOPRESS_CORE_PATH . 'includes/class-taxonomies.php';
        require_once INMOPRESS_CORE_PATH . 'includes/class-roles.php';
    }
    
    private function init_hooks() {
        // Registrar CPTs y Taxonomías
        add_action('init', array('Inmopress_CPTs', 'register'), 0);
        add_action('init', array('Inmopress_Taxonomies', 'register'), 0);
        
        // Activación
        register_activation_hook(__FILE__, array($this, 'activate'));
        
        // ACF JSON
        add_filter('acf/settings/save_json', array($this, 'acf_json_save'));
        add_filter('acf/settings/load_json', array($this, 'acf_json_load'));
    }
    
    public function activate() {
        Inmopress_CPTs::register();
        Inmopress_Taxonomies::register();
        Inmopress_Roles::create_roles();
        flush_rewrite_rules();
    }
    
    public function acf_json_save($path) {
        return INMOPRESS_CORE_PATH . 'acf-json';
    }
    
    public function acf_json_load($paths) {
        unset($paths[0]);
        $paths[] = INMOPRESS_CORE_PATH . 'acf-json';
        return $paths;
    }
}

function inmopress_core() {
    return Inmopress_Core::get_instance();
}
add_action('plugins_loaded', 'inmopress_core');
```

#### 3.3 Copiar Clases

**(Copiar desde documentación anterior)**
- `class-cpts.php` → Registra los 11 CPTs
- `class-taxonomies.php` → Registra las 19 taxonomías
- `class-roles.php` → Crea los 3 roles custom

#### 3.4 Instalar Plugin

```
1. Comprimir carpeta inmopress-core/ → inmopress-core.zip
2. Plugins → Añadir nuevo → Subir plugin
3. Seleccionar inmopress-core.zip
4. Instalar → Activar
5. Verificar: Ver 11 CPTs en menú lateral
```

#### 3.5 Poblar Taxonomías

**Para cada taxonomía, añadir términos:**

```
Inmuebles → Operación:
  - Venta, Alquiler, Alquiler vacacional, Traspaso

Inmuebles → Tipo de Vivienda:
  - Piso, Ático, Dúplex, Chalet, Adosado, etc (18 términos)

Inmuebles → Provincia:
  - Valencia, Alicante, Castellón

Inmuebles → Ciudad:
  - Bajo Valencia: Valencia ciudad, Paterna, Torrent, etc

... (continuar con todas las taxonomías)
```

**Tiempo:** 3 horas  
**Checkpoint:** 11 CPTs visibles, 19 taxonomías con términos

---

### FASE 4: CAMPOS ACF (8-10 horas)

Esta es la fase más larga y crítica.

#### 4.1 Estrategia

**Orden de creación:**
1. Inmuebles (106 campos - 3h)
2. Clientes (21 campos - 1h)
3. Leads (24 campos - 30min)
4. Visitas (10 campos - 30min)
5. Agencias (12 campos - 30min)
6. Agentes (10 campos - 30min)
7. Propietarios (8 campos - 20min)
8. Promociones (8 campos - 30min)

**Total:** 8-10 horas

#### 4.2 Proceso por CPT

**Para INMUEBLES (ejemplo detallado):**

**Field Group 1: Inmuebles - Información General**

```
ACF → Field Groups → Add New

Título: Inmuebles - Información General

Location Rules:
  - Post Type is equal to impress_property

Settings:
  - Hide on screen: (nada)
  - Order: 0
  - Position: Normal (after title)

CAMPOS:

1. Publicada
   - Field Label: Publicada
   - Field Name: publicada
   - Field Type: True / False
   - Instructions: Marcar si el inmueble está visible en la web
   - Default: 0

2. Vendida
   - Field Label: Vendida
   - Field Name: vendida
   - Field Type: True / False
   - Default: 0

3. Reservada
   - Field Label: Reservada
   - Field Name: reservada
   - Field Type: True / False
   - Default: 0

4. Dirección
   - Field Label: Dirección
   - Field Name: direccion
   - Field Type: Text
   - Instructions: Dirección completa del inmueble
   - Placeholder: Calle, número, piso, puerta

5. Descripción
   - Field Label: Descripción
   - Field Name: descripcion
   - Field Type: WYSIWYG Editor
   - Toolbar: Full
   - Instructions: Descripción detallada del inmueble

6. Referencia
   - Field Label: Referencia
   - Field Name: referencia
   - Field Type: Text
   - Required: Yes
   - Instructions: Código único del inmueble (ej: N4569Z)
   - Placeholder: REF-XXXX

7. Zona
   - Field Label: Zona / Barrio
   - Field Name: zona
   - Field Type: Text
   - Instructions: Zona o barrio dentro de la ciudad
   - Placeholder: Ej: Ruzafa, Centro, etc

GUARDAR
```

**Repetir proceso para:**
- Field Group 2: Ubicación (3 campos)
- Field Group 3: Relaciones (3 campos)
- Field Group 4: Características Físicas (14 campos)
- Field Group 5: Detalles Técnicos (8 campos)
- Field Group 6: Costes (4 campos)
- Field Group 7: Distancias (11 campos)
- Field Group 8: Características Secundarias (33 switches)
- Field Group 9: Datos Venta (7 campos con Conditional Logic)
- Field Group 10: Datos Alquiler (12 campos con Conditional Logic)
- Field Group 11: Media (4 campos)

#### 4.3 Exportar ACF a JSON

**Después de crear cada Field Group:**

```
1. ACF → Tools → Export Field Groups
2. Seleccionar el grupo recién creado
3. Generate PHP (copiar y guardar en /documentacion/)
4. Export as JSON
5. Descargar .json
6. Copiar a /wp-content/plugins/inmopress-core/acf-json/
```

#### 4.4 Crear Campos para Resto de CPTs

**Seguir mismo proceso para:**
- Clientes (5 Field Groups)
- Leads (5 Field Groups)
- Visitas (1 Field Group)
- Agencias (3 Field Groups)
- Agentes (2 Field Groups)
- Propietarios (1 Field Group)
- Promociones (2 Field Groups)

**Tiempo total:** 8-10 horas  
**Checkpoint:** 33 Field Groups creados, JSON exportados

---

### FASE 5: VALIDACIONES Y RELACIONES (2 horas)

#### 5.1 Validación Referencia Única

**Añadir a `inmopress-core.php`:**

```php
// Validar que la referencia sea única
add_filter('acf/validate_value/name=referencia', 'inmopress_validate_unique_ref', 10, 4);
function inmopress_validate_unique_ref($valid, $value, $field, $input) {
    if (!$valid) return $valid;
    
    global $post;
    $posts = get_posts(array(
        'post_type' => 'impress_property',
        'meta_key' => 'referencia',
        'meta_value' => $value,
        'post__not_in' => array($post->ID),
        'posts_per_page' => 1
    ));
    
    if (!empty($posts)) {
        $valid = 'Esta referencia ya existe. Por favor usa otra.';
    }
    
    return $valid;
}
```

#### 5.2 Auto-asignación de Agente

```php
// Al crear inmueble, asignar automáticamente el agente actual
add_filter('acf/load_value/name=agente', 'inmopress_auto_assign_agent', 10, 3);
function inmopress_auto_assign_agent($value, $post_id, $field) {
    // Si ya tiene valor, no hacer nada
    if ($value) return $value;
    
    // Si es un nuevo post
    if (!$value && !is_admin()) {
        $current_user = wp_get_current_user();
        
        // Buscar agente vinculado a este usuario
        $agente = get_posts(array(
            'post_type' => 'impress_agent',
            'meta_key' => 'usuario_wordpress',
            'meta_value' => $current_user->ID,
            'posts_per_page' => 1
        ));
        
        if (!empty($agente)) {
            return $agente[0]->ID;
        }
    }
    
    return $value;
}
```

#### 5.3 Relación Bidireccional (Opcional - Avanzado)

```php
// Cuando asignas un agente a un inmueble,
// añadir el inmueble a la lista del agente
add_action('acf/save_post', 'inmopress_sync_property_agent', 20);
function inmopress_sync_property_agent($post_id) {
    // Solo para inmuebles
    if (get_post_type($post_id) != 'impress_property') return;
    
    $agente_id = get_field('agente', $post_id);
    
    if ($agente_id) {
        // Obtener inmuebles actuales del agente
        $inmuebles_agente = get_field('inmuebles_asignados', $agente_id) ?: array();
        
        // Añadir este inmueble si no está
        if (!in_array($post_id, $inmuebles_agente)) {
            $inmuebles_agente[] = $post_id;
            update_field('inmuebles_asignados', $inmuebles_agente, $agente_id);
        }
    }
}
```

**Tiempo:** 2 horas  
**Checkpoint:** Validaciones funcionando

---

### FASE 6: ROLES Y PERMISOS (1.5 horas)

#### 6.1 Completar `class-roles.php`

**(Ya creado en Fase 3, ahora activarlo)**

#### 6.2 Instalar Plugin Members

```
Plugins → Añadir nuevo → "Members"
Instalar → Activar

Users → Roles:
  - Editar rol "Agente"
  - Capabilities:
    ☑ read
    ☑ edit_posts
    ☑ publish_posts
    ☑ edit_impress_property (custom capability)
    ☑ edit_impress_client
    ... (añadir todas las necesarias)
  - Save
```

#### 6.3 Ocultar Admin Bar

**Ya implementado en `inmopress-core.php`**

#### 6.4 Redirect después de Login

**Ya implementado en `inmopress-core.php`**

**Tiempo:** 1.5 horas  
**Checkpoint:** 3 roles creados y funcionando

---

### FASE 7: PLUGIN FRONTEND PANEL (4 horas)

#### 7.1 Crear Plugin Inmopress Frontend

**Estructura:**
```
inmopress-frontend/
├── inmopress-frontend.php
├── includes/
│   ├── class-shortcodes.php
│   ├── class-ajax-handlers.php
│   └── class-acf-forms.php
├── templates/
│   ├── dashboard.php
│   ├── inmuebles-list.php
│   ├── inmueble-form.php
│   ├── clientes-list.php
│   ├── cliente-form.php
│   ├── visitas-list.php
│   └── visita-form.php
└── assets/
    ├── css/panel.css
    └── js/panel.js
```

#### 7.2 Copiar Código

**(Del documento INMOPRESS-PANEL-ASTRA-PLUGIN.md)**
- Archivo principal
- Clase shortcodes
- Clase AJAX handlers
- Templates
- CSS y JS

#### 7.3 Instalar Plugin

```
1. Comprimir carpeta
2. Subir e instalar
3. Activar
```

#### 7.4 Crear Páginas del Panel

```
Páginas → Añadir nueva (x11)

1. Mi Panel
   - Slug: mi-panel
   - Contenido: [inmopress_dashboard]
   - Plantilla: Full Width
   - Astra: Disable Title

2. Inmuebles
   - Slug: inmuebles
   - Parent: Mi Panel
   - Contenido: [inmopress_inmuebles_list]
   
3. Nuevo Inmueble
   - Slug: nuevo-inmueble
   - Parent: Inmuebles
   - Contenido: [inmopress_inmueble_form]

... (crear las 8 restantes)
```

#### 7.5 Configurar Astra por Página

**Para CADA página del panel:**

```
Editar página → Sidebar derecho

Astra Settings:
  - Disable Title: ☑
  - Container: Full Width Stretched
  - Content Layout: Full Width
  - Disable Header: ☐ (mantener)
  - Disable Footer: ☐ (mantener)
  - Sidebar: No Sidebar

Actualizar
```

#### 7.6 (Opcional) Custom Layout Navegación

```
Appearance → Custom Layouts → Add New

Título: Panel Navigation
Layout: Header
Display On: Pages → Seleccionar 11 páginas panel
User Roles: Logged In Users

Contenido:
<nav class="panel-nav">
  <a href="/mi-panel/">🏠 Dashboard</a>
  <a href="/inmuebles/">🏘️ Inmuebles</a>
  <a href="/clientes/">👥 Clientes</a>
  <a href="/visitas/">📅 Visitas</a>
  <a href="/propietarios/">🔑 Propietarios</a>
</nav>

Publish
```

**Tiempo:** 4 horas  
**Checkpoint:** Panel frontend operativo

---

### FASE 8: SEO CONFIGURACIÓN (2 horas)

#### 8.1 Configurar Rank Math para Inmuebles

```
Rank Math → Titles & Meta → Post Types → Inmuebles

Title Template:
%title% en %impress_operation% - %impress_city% | %sitename%

Description Template:
%excerpt% 📍 %impress_city% • %impress_bedrooms% hab • %impress_area%m² • Ref: %impress_ref% • Precio: %impress_price%€

Robots Meta: Index, Follow
Schema Type: RealEstateListing
```

#### 8.2 Añadir Variables Personalizadas

**En `inmopress-core.php`:**

```php
// Registrar variables ACF en Rank Math
add_filter('rank_math/vars/replacements', function($replacements) {
    $replacements['impress_ref'] = array(
        'name' => 'Referencia Inmueble',
        'description' => 'Código de referencia',
        'variable' => 'impress_ref',
        'example' => 'N4569Z',
    );
    
    $replacements['impress_price'] = array(
        'name' => 'Precio',
        'description' => 'Precio del inmueble',
        'variable' => 'impress_price',
        'example' => '185000',
    );
    
    $replacements['impress_city'] = array(
        'name' => 'Ciudad',
        'description' => 'Ciudad del inmueble',
        'variable' => 'impress_city',
        'example' => 'Valencia',
    );
    
    $replacements['impress_bedrooms'] = array(
        'name' => 'Habitaciones',
        'variable' => 'impress_bedrooms',
    );
    
    $replacements['impress_area'] = array(
        'name' => 'Superficie',
        'variable' => 'impress_area',
    );
    
    $replacements['impress_operation'] = array(
        'name' => 'Operación',
        'variable' => 'impress_operation',
    );
    
    return $replacements;
});

// Rellenar variables con datos reales
add_filter('rank_math/replacements', function($replacements) {
    global $post;
    
    if ($post && $post->post_type === 'impress_property') {
        $replacements['impress_ref'] = get_field('referencia', $post->ID);
        
        $precio_venta = get_field('precio_venta', $post->ID);
        $precio_alquiler = get_field('precio_alquiler', $post->ID);
        $replacements['impress_price'] = $precio_venta ?: $precio_alquiler;
        
        $ciudad = get_the_terms($post->ID, 'impress_city');
        $replacements['impress_city'] = $ciudad ? $ciudad[0]->name : '';
        
        $operacion = get_the_terms($post->ID, 'impress_operation');
        $replacements['impress_operation'] = $operacion ? $operacion[0]->name : '';
        
        $replacements['impress_bedrooms'] = get_field('dormitorios', $post->ID);
        $replacements['impress_area'] = get_field('superficie_construida', $post->ID);
    }
    
    return $replacements;
});
```

#### 8.3 Schema.org RealEstateListing

```php
add_filter('rank_math/json_ld', function($data, $jsonld) {
    global $post;
    
    if ($post && $post->post_type === 'impress_property') {
        $precio_venta = get_field('precio_venta', $post->ID);
        $precio_alquiler = get_field('precio_alquiler', $post->ID);
        
        $data['RealEstateListing'] = array(
            '@type' => 'RealEstateListing',
            'name' => get_the_title($post->ID),
            'description' => get_the_excerpt($post->ID),
            'url' => get_permalink($post->ID),
            'image' => get_the_post_thumbnail_url($post->ID, 'full'),
            'price' => $precio_venta ?: $precio_alquiler,
            'priceCurrency' => 'EUR',
            'numberOfRooms' => get_field('dormitorios', $post->ID),
            'numberOfBathroomsTotal' => get_field('banos', $post->ID),
            'floorSize' => array(
                '@type' => 'QuantitativeValue',
                'value' => get_field('superficie_construida', $post->ID),
                'unitCode' => 'MTK'
            ),
        );
    }
    
    return $data;
}, 10, 2);
```

#### 8.4 Configurar Sitemap

```
Rank Math → Sitemap Settings

General:
  ☑ Include Images
  ☑ Include Featured Image

Post Types:
  ☑ Inmuebles
  ☑ Promociones
  ☐ Clientes (excluir - privado)
  ☐ Leads (excluir)
  ☐ Visitas (excluir)
  ☐ Agencias (excluir o incluir según estrategia)
  ☐ Agentes (incluir si página pública equipo)
  ☐ Propietarios (excluir)

Taxonomies:
  ☑ Operación
  ☑ Tipo Vivienda
  ☑ Provincia
  ☑ Ciudad
  ☑ Resto taxonomías públicas
  ☐ Taxonomías privadas (estados, etc)

Save Changes
```

**Tiempo:** 2 horas  
**Checkpoint:** SEO configurado, sitemap generado

---

### FASE 9: BLOQUES GUTENBERG (6-8 horas)

#### 9.1 Crear Plugin Inmopress Blocks

**Estructura:**
```
inmopress-blocks/
├── inmopress-blocks.php
├── blocks/
│   ├── hero-inmobiliaria/
│   ├── buscador-inmuebles/
│   ├── grid-inmuebles/
│   └── ... (22 bloques más)
└── assets/
    ├── css/blocks.css
    └── js/blocks.js
```

#### 9.2 Implementar Bloques Prioritarios (Fase 1)

**Orden de implementación:**

1. **hero-inmobiliaria** (1h)
2. **buscador-inmuebles** (1.5h)
3. **grid-inmuebles** (1h)
4. **ficha-tecnica** (45min)
5. **galeria-inmueble** (1h)
6. **formulario-contacto-inmueble** (1.5h)

**Total Fase 1:** 6.5 horas

#### 9.3 Implementar Resto de Bloques (Opcional - Fase 2-4)

**Fase 2:** Detalle + Listados (4h)
**Fase 3:** Marketing (3h)
**Fase 4:** Avanzados (4h)

**Tiempo mínimo (solo esenciales):** 6-8 horas  
**Tiempo completo (25 bloques):** 17-19 horas  

**Checkpoint:** 6 bloques esenciales funcionando

---

### FASE 10: PLANTILLAS IMPRIMIBLES (3 horas)

#### 10.1 Crear Plugin Inmopress Printables

```
inmopress-printables/
├── inmopress-printables.php
├── includes/
│   ├── class-pdf-generator.php
│   └── class-template-engine.php
├── templates/
│   ├── cartel-vertical-a4.php
│   ├── cartel-horizontal-a4.php
│   ├── cartel-compacto-a5.php
│   ├── banner-premium-a3.php
│   ├── ficha-completa-a4.php
│   └── qr-sticker.php
└── assets/
    └── css/print.css
```

#### 10.2 Implementar Plantillas

**Prioridad 1 (1.5h):**
- Cartel Vertical A4
- Cartel Horizontal A4

**Prioridad 2 (1.5h):**
- Compacto A5
- QR Sticker
- Ficha completa
- Banner A3

#### 10.3 Crear Página print-property

```
Páginas → Añadir nueva

Título: Print Property
Slug: print-property
Plantilla: Blank (sin header/footer)

Astra Settings:
  - Disable Header: ☑
  - Disable Footer: ☑
  - Container: Full Width

Contenido: (vacío - gestionado por plugin)

Publicar
```

#### 10.4 Configurar Opciones

```
Inmuebles → Plantillas Imprimibles → Configuración

Logo empresa: [Subir logo.png]
Teléfono: 900 000 000
Email: info@inmobiliaria.com
Web: www.inmobiliaria.com
Color primario: #667eea
Color secundario: #764ba2

Guardar
```

**Tiempo:** 3 horas  
**Checkpoint:** 6 plantillas funcionando

---

### FASE 11: TESTING (4-6 horas)

#### 11.1 Crear Contenido de Prueba

```
1. Crear 3 Propietarios
2. Crear 2 Agencias
3. Crear 3 Agentes (vincular agencias)
4. Crear 15 Inmuebles:
   - 5 en Venta
   - 5 en Alquiler
   - 5 en Venta (diferentes ciudades)
   - Rellenar TODOS los campos
   - Asignar taxonomías
   - Subir 3-5 fotos cada uno
   - Vincular propietario + agente
5. Crear 8 Clientes
6. Crear 5 Visitas programadas
7. Crear 3 Leads
```

#### 11.2 Testing Funcional

**Checklist (marcar al probar):**

```
CPTs y Taxonomías:
  [ ] 11 CPTs visibles en admin
  [ ] Campos ACF se muestran correctamente
  [ ] Campos ACF se guardan sin errores
  [ ] Taxonomías asignables
  [ ] Filtros por taxonomía funcionan
  [ ] Búsqueda interna funciona

Relaciones:
  [ ] Asignar agente a inmueble funciona
  [ ] Asignar propietario a inmueble funciona
  [ ] Crear visita vincula cliente + inmueble
  [ ] Validación referencia única funciona

Panel Frontend:
  [ ] Dashboard carga y muestra KPIs correctos
  [ ] Listado inmuebles muestra datos
  [ ] Filtros del listado funcionan
  [ ] Crear inmueble desde frontend funciona
  [ ] Editar inmueble desde frontend funciona
  [ ] Formulario cliente funciona
  [ ] Formulario visita funciona
  [ ] Paginación funciona
  [ ] AJAX eliminar funciona

Permisos:
  [ ] Admin ve todo
  [ ] Agente solo ve sus inmuebles
  [ ] Agente puede crear y publicar
  [ ] Trabajador crea en borrador
  [ ] No-autenticado redirige a login

SEO:
  [ ] Meta title correcto en inmuebles
  [ ] Meta description correcta
  [ ] Schema validado (Google Rich Results Test)
  [ ] Sitemap generado
  [ ] URLs amigables funcionan

Bloques:
  [ ] Hero se muestra correctamente
  [ ] Buscador funciona y filtra
  [ ] Grid muestra inmuebles
  [ ] Ficha técnica muestra datos
  [ ] Galería muestra fotos
  [ ] Formulario contacto envía

Plantillas Imprimibles:
  [ ] Vertical A4 se genera
  [ ] Horizontal A4 se genera
  [ ] Imprimir (Ctrl+P) funciona
  [ ] QR code se genera correctamente
  [ ] Datos se muestran correctamente
```

#### 11.3 Testing Rendimiento

```
Herramientas:
  - GTmetrix.com
  - PageSpeed Insights
  - Pingdom

Objetivos:
  - Tiempo carga < 3 segundos
  - PageSpeed Score > 80
  - Queries DB < 50 por página

Si no se cumplen:
  - Activar WP Rocket
  - Optimizar imágenes (Imagify)
  - Lazy load
  - Minify CSS/JS
```

#### 11.4 Testing Responsive

```
Dispositivos:
  - Desktop 1920x1080 ☑
  - Desktop 1366x768 ☑
  - Tablet iPad 768px ☑
  - Mobile iPhone 375px ☑

Verificar:
  - Panel frontend usable
  - Formularios funcionan
  - Listados legibles
  - Bloques responsive
  - Imágenes se adaptan
```

#### 11.5 Testing SEO

```
1. Google Search Console:
   - Añadir propiedad
   - Verificar dominio
   - Enviar sitemap

2. Rich Results Test:
   - Probar URL inmueble
   - Verificar RealEstateListing
   - Verificar breadcrumbs

3. Verificar URLs:
   /inmuebles/ ☑
   /venta/ ☑
   /alquiler/ ☑
   /valencia/ ☑
   /inmuebles/piso-valencia-centro/ ☑
```

**Tiempo:** 4-6 horas  
**Checkpoint:** Todo probado y funcionando

---

### FASE 12: OPTIMIZACIÓN FINAL (2 horas)

#### 12.1 Eliminar Contenido de Prueba

```
- Eliminar inmuebles de prueba
- Eliminar clientes de prueba
- Eliminar visitas de prueba
- Mantener 1-2 inmuebles ejemplo (marcar como "Ejemplo")
```

#### 12.2 Desactivar Plugins Desarrollo

```
- Desactivar Query Monitor
- Desactivar Show Current Template
- Mantener solo plugins producción
```

#### 12.3 Optimizar Base de Datos

```
Plugins → WP-Optimize

Database:
  ☑ Clean post revisions
  ☑ Clean auto-drafts
  ☑ Clean trashed posts
  ☑ Remove spam comments
  ☑ Clean transients

Run Optimization
```

#### 12.4 Configurar WP Rocket

```
WP Rocket → Settings

Cache:
  ☑ Enable caching for mobile
  ☐ Enable caching for logged-in users

File Optimization:
  ☑ Minify CSS
  ☑ Combine CSS
  ☑ Minify JavaScript
  ☐ Combine JavaScript (puede dar problemas)

Media:
  ☑ Enable Lazy Load images
  ☑ Enable Lazy Load iframes

Advanced:
  ☑ Optimize Google Fonts
  ☑ Remove query strings

Save & Clear Cache
```

#### 12.5 Configurar wp-config.php Producción

```php
// Deshabilitar debug
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
define('WP_DEBUG_DISPLAY', false);

// Límite revisiones
define('WP_POST_REVISIONS', 5);

// Memory
define('WP_MEMORY_LIMIT', '256M');

// Cache
define('WP_CACHE', true);

// Deshabilitar edición archivos
define('DISALLOW_FILE_EDIT', true);
```

**Tiempo:** 2 horas  
**Checkpoint:** Sistema optimizado

---

### FASE 13: SEGURIDAD (1.5 horas)

#### 13.1 Configurar Wordfence

```
Wordfence → All Options

Firewall:
  ☑ Enable Extended Protection
  ☑ Block fake Google crawlers
  ☑ Immediately block IPs

Login Security:
  ☑ Enable 2FA for admins
  Lockout after: 5 failed logins
  Lock out after: 20 minutes

Scan:
  Scheduled scan: Daily
  Email alerts: tu@email.com

Save Changes
```

#### 13.2 SSL y HTTPS

```
1. Verificar SSL instalado
2. Really Simple SSL plugin:
   - Instalar → Activar
   - Go ahead, activate SSL
3. Verificar https:// funciona
```

#### 13.3 Cambiar Prefijo Tablas (Opcional - Solo si nuevo)

```
Plugin: iThemes Security

Security → Settings → Database:
  ☑ Change database prefix
  Nuevo prefijo: wp_abc_
  Change Database Prefix
```

#### 13.4 Configurar Backups Automáticos

```
UpdraftPlus → Settings

Files backup schedule: Weekly
Database backup schedule: Daily

Remote Storage:
  - Seleccionar Google Drive / Dropbox
  - Authenticate
  - Test connection

Save Changes
```

**Tiempo:** 1.5 horas  
**Checkpoint:** Seguridad configurada

---

### FASE 14: GO LIVE (2 horas)

#### 14.1 Checklist Pre-Lanzamiento

```
Contenido:
  [ ] Logo actualizado
  [ ] Datos contacto correctos
  [ ] Política privacidad publicada
  [ ] Aviso legal publicado
  [ ] Cookies configuradas

Técnico:
  [ ] SSL activo y funcionando
  [ ] Redirects http → https
  [ ] www configurado (con o sin)
  [ ] Favicon configurado
  [ ] Emails transaccionales funcionan (WP Mail SMTP)
  [ ] Formularios de contacto probados
  [ ] Enlaces rotos verificados

SEO:
  [ ] Google Analytics instalado
  [ ] Google Tag Manager (opcional)
  [ ] Search Console verificado
  [ ] Sitemap enviado
  [ ] Robots.txt correcto

Legal:
  [ ] Banner cookies (Cookie Notice)
  [ ] RGPD compliance
  [ ] Formularios con checkbox RGPD
```

#### 14.2 Configurar Analytics

```
1. Crear cuenta Google Analytics
2. Crear propiedad
3. Obtener ID medición (G-XXXXXXXXXX)

4. Rank Math → General Settings → Analytics:
   - Connect Google Analytics
   - Autorizar
   - Seleccionar propiedad

5. Verificar tracking code en código fuente
```

#### 14.3 DNS Final

```
Verificar:
  A record → IP servidor ☑
  CNAME www → dominio.com ☑
  MX records → email (si aplica) ☑

Propagación:
  - Esperar 24-48h
  - Verificar en https://www.whatsmydns.net/
```

#### 14.4 Monitorización

```
UptimeRobot (free):
  1. Crear cuenta
  2. Add New Monitor
  3. Monitor Type: HTTPS
  4. URL: https://tudominio.com
  5. Monitoring Interval: 5 minutes
  6. Alert Contacts: tu@email.com
  7. Create Monitor
```

#### 14.5 Formación Usuario

```
Preparar:
  - Video tutorial acceso panel (5 min)
  - PDF guía rápida crear inmueble
  - PDF guía crear cliente
  - Contacto soporte

Entregar:
  - Credenciales admin
  - Credenciales agente ejemplo
  - Documentación
```

**Tiempo:** 2 horas  
**Checkpoint:** SITIO LIVE ✅

---

## 13. TIMELINE Y RECURSOS

### Timeline Completo

| Fase | Descripción | Horas | Acumulado | Días |
|------|-------------|-------|-----------|------|
| 0 | Preparación | 2h | 2h | 0.5 |
| 1 | Instalación Base | 1h | 3h | 0.5 |
| 2 | Plugins Esenciales | 1.5h | 4.5h | 1 |
| 3 | Plugin Core (CPTs + Tax) | 3h | 7.5h | 1 |
| 4 | Campos ACF | 8-10h | 17.5h | 2.5 |
| 5 | Validaciones | 2h | 19.5h | 3 |
| 6 | Roles | 1.5h | 21h | 3 |
| 7 | Panel Frontend | 4h | 25h | 3.5 |
| 8 | SEO | 2h | 27h | 4 |
| 9 | Bloques (esenciales) | 6-8h | 35h | 5 |
| 10 | Plantillas Imprimibles | 3h | 38h | 5.5 |
| 11 | Testing | 4-6h | 44h | 6 |
| 12 | Optimización | 2h | 46h | 6 |
| 13 | Seguridad | 1.5h | 47.5h | 6.5 |
| 14 | Go Live | 2h | 49.5h | 7 |
| **TOTAL** | | **~50h** | | **7 días** |

### Distribución Recomendada

**Semana 1: Base Funcional**
- Día 1: Fases 0-3 (CPTs y Taxonomías)
- Día 2-3: Fase 4 (Campos ACF - la más larga)
- Día 4: Fases 5-6 (Validaciones y Roles)
- Día 5: Fase 7 (Panel Frontend)

**Semana 2: Funcionalidades Avanzadas**
- Día 6: Fase 8-9 (SEO + Bloques esenciales)
- Día 7: Fase 10-11 (Plantillas + Testing)
- Día 8: Fases 12-14 (Optimización + Seguridad + Go Live)

**Total:** 8 días laborables (10-12 días calendario)

### Recursos Necesarios

**Humanos:**
- 1 Desarrollador WordPress Senior (full-time)
- Opcional: 1 Diseñador UI/UX (part-time Fase 9)

**Software:**
- WordPress 6.4+: Gratis
- Astra Pro: ~$59/año
- ACF Pro: ~$49/año
- Rank Math Pro (opcional): ~$59/año
- WP Rocket (opcional): ~$49/año

**Total inversión software:** ~$200-300/año

**Hosting:**
- Mínimo: 2GB RAM, PHP 8.0+, MySQL 5.7+
- Recomendado: 4GB RAM, SSD, CDN
- Costo: $10-50/mes según proveedor

---

## 14. CHECKLIST DE VALIDACIÓN

### Checklist por Fase

#### ✅ FASE 0-1: Instalación
- [ ] WordPress instalado
- [ ] Admin accesible
- [ ] SSL funcionando
- [ ] Enlaces permanentes configurados

#### ✅ FASE 2: Plugins
- [ ] Astra activado
- [ ] Astra Pro licencia activa
- [ ] ACF Pro licencia activa
- [ ] Rank Math configurado
- [ ] UpdraftPlus configurado

#### ✅ FASE 3: Core
- [ ] Plugin Inmopress Core activado
- [ ] 11 CPTs visibles
- [ ] 19 Taxonomías creadas
- [ ] Términos taxonomías poblados

#### ✅ FASE 4: ACF
- [ ] 33 Field Groups creados
- [ ] 235 campos ACF creados
- [ ] JSON exportados
- [ ] Inmueble de prueba con todos campos guardados

#### ✅ FASE 5-6: Validaciones y Roles
- [ ] Referencia única validada
- [ ] Auto-asignación agente funciona
- [ ] 3 roles creados
- [ ] Capabilities asignadas

#### ✅ FASE 7: Panel
- [ ] Plugin Frontend activado
- [ ] 11 páginas creadas
- [ ] Dashboard muestra KPIs
- [ ] Formularios guardan datos
- [ ] Listados filtran correctamente

#### ✅ FASE 8: SEO
- [ ] Variables personalizadas funcionan
- [ ] Schema validado
- [ ] Sitemap generado
- [ ] Meta tags correctos

#### ✅ FASE 9: Bloques
- [ ] Plugin Blocks activado
- [ ] 6 bloques esenciales creados
- [ ] Bloques se insertan sin errores
- [ ] Bloques muestran datos correctos

#### ✅ FASE 10: Imprimibles
- [ ] Plugin Printables activado
- [ ] 2 plantillas mínimo creadas
- [ ] Impresión funciona (Ctrl+P)
- [ ] QR se genera

#### ✅ FASE 11: Testing
- [ ] 15 inmuebles de prueba creados
- [ ] Todos los tests funcionales pasados
- [ ] Rendimiento < 3 seg
- [ ] Responsive OK

#### ✅ FASE 12-14: Final
- [ ] Optimizaciones aplicadas
- [ ] Seguridad configurada
- [ ] Backups automáticos activos
- [ ] Analytics funcionando
- [ ] SITIO LIVE ✅

---

## APÉNDICES

### A. Comandos Útiles SSH

```bash
# Backup manual base datos
mysqldump -u usuario -p nombre_bd > backup.sql

# Backup archivos
tar -czf backup-files.tar.gz wp-content/

# Permisos correctos
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;

# Regenerar thumbnails
wp media regenerate --yes

# Flush cache
wp cache flush

# Update plugins
wp plugin update --all
```

### B. Snippets Code Útiles

**Deshabilitar Gutenberg para CPTs específicos:**
```php
add_filter('use_block_editor_for_post_type', function($use, $post_type) {
    if ($post_type === 'impress_client') return false;
    return $use;
}, 10, 2);
```

**Añadir columna personalizada en listado:**
```php
add_filter('manage_impress_property_posts_columns', function($columns) {
    $columns['referencia'] = 'Referencia';
    return $columns;
});

add_action('manage_impress_property_posts_custom_column', function($column, $post_id) {
    if ($column === 'referencia') {
        echo get_field('referencia', $post_id);
    }
}, 10, 2);
```

### C. Recursos y Documentación

**Oficial:**
- WordPress Codex: https://codex.wordpress.org/
- ACF Documentation: https://advancedcustomfields.com/resources/
- Astra Docs: https://wpastra.com/docs/
- Rank Math KB: https://rankmath.com/kb/

**Comunidad:**
- WordPress Stack Exchange
- ACF Support Forum
- Astra Facebook Group

---

**FIN DEL DOCUMENTO MAESTRO**

---

**Próximos pasos recomendados:**

1. Leer este documento completo
2. Preparar entorno (Fase 0)
3. Comenzar Fase 1
4. Seguir checklist paso a paso
5. Validar cada checkpoint

**Tiempo total estimado:** 50 horas (7-10 días)

**¿Listo para empezar?** 🚀
