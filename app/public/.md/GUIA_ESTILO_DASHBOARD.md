# Inmopress - Guía de Estilo Gráfico del Dashboard

**Fecha:** 6 de Febrero de 2026  
**Versión:** 1.0.0  
**Propósito:** Establecer la línea gráfica completa para el dashboard de Inmopress en desktop y mobile

---

## Resumen Ejecutivo

Esta guía establece el sistema de diseño visual para el dashboard de Inmopress, basado en análisis de dashboards modernos de CRM y gestión inmobiliaria. El estilo combina elementos de diseño limpio, profesional y funcional, optimizado para productividad y experiencia de usuario.

---

## 1. Filosofía de Diseño

### Principios Fundamentales

1. **Claridad y Legibilidad:** Priorizar la información sobre la decoración
2. **Consistencia Visual:** Elementos reutilizables y patrones coherentes
3. **Jerarquía Visual:** Guiar la atención del usuario mediante tamaño, color y espaciado
4. **Eficiencia:** Diseño que acelera el trabajo, no lo entorpece
5. **Profesionalismo:** Estética moderna pero sobria, adecuada para entorno empresarial

### Estilo General

- **Aesthetic:** "Soft UI" / "Neumorphism-lite" con elementos planos
- **Enfoque:** Minimalista pero funcional
- **Sensación:** Moderno, limpio, profesional, accesible

---

## 2. Paleta de Colores

### Colores Principales

#### 2.1. Fondos y Superficies

**Desktop:**
- **Sidebar (Navegación):** 
  - Fondo oscuro: `#191F34` o `#1A1F35` (azul oscuro/púrpura profundo)
  - Alternativa más clara: `#2D3447` para hover states
  
- **Área Principal:**
  - Fondo base: `#F7F8F9` o `#F8F9FB` (gris muy claro/off-white)
  - Fondo de cards/widgets: `#FFFFFF` (blanco puro)
  - Fondo alternativo: `#F8F7F5` (beige muy claro) para variación sutil

**Mobile:**
- Fondo principal: `#F7F8F9` (consistente con desktop)
- Cards: `#FFFFFF` con sombras más sutiles

#### 2.2. Colores de Acento (Accent Colors)

**Color Primario Principal:**
- **Púrpura/Violeta:** `#6C5DD3` o `#7A4B9F`
  - Uso: Botones primarios, estados activos, highlights importantes
  - Variaciones:
    - Hover: `#5A4BC2`
    - Light: `#E8E5FF` (fondo para badges/etiquetas)

**Colores Secundarios (Status/Estados):**

- **Verde (Éxito/Activo):**
  - Principal: `#52C41A` o `#10B981`
  - Fondo claro: `#E6F7E9` o `#D1FAE5`
  - Uso: Estados positivos, "Contacted", "Closed-Won", indicadores online

- **Azul (Información/Neutro):**
  - Principal: `#3D8EFF` o `#2563EB`
  - Fondo claro: `#EAF3FF` o `#DBEAFE`
  - Uso: Información, "New", estados neutros, links

- **Naranja (Advertencia/Media Prioridad):**
  - Principal: `#F7941D` o `#F59E0B`
  - Fondo claro: `#FFF3E0` o `#FEF3C7`
  - Uso: Advertencias, "High Priority", "Going Cold"

- **Rojo/Rosa (Urgente/Alerta):**
  - Principal: `#EF4444` o `#F43F5E` (rojo)
  - Alternativa Rosa: `#EC4899` o `#E91E63` (magenta/rosa)
  - Fondo claro: `#FEE2E2` o `#FCE7F3`
  - Uso: Urgencias, "Urgent", "Closed-Lost", deudas

- **Amarillo (Progreso/Actividad):**
  - Principal: `#FBBF24` o `#EAB308`
  - Fondo claro: `#FEF3C7`
  - Uso: Barras de progreso, indicadores de actividad

#### 2.3. Colores de Texto

**Desktop:**
- **Texto Principal:** `#333333` o `#1F2937` (gris muy oscuro/casi negro)
- **Texto Secundario:** `#666666` o `#6B7280` (gris medio)
- **Texto Terciario/Placeholder:** `#999999` o `#9CA3AF` (gris claro)
- **Texto en Fondo Oscuro:** `#FFFFFF` (blanco) o `#F3F4F6` (gris muy claro)
- **Texto en Fondos de Color:** `#FFFFFF` para legibilidad

**Mobile:**
- Mismos colores, pero considerar aumentar tamaño mínimo para legibilidad

#### 2.4. Bordes y Separadores

- **Bordes Sutiles:** `#E5E7EB` o `#E4E7EB` (gris muy claro)
- **Separadores:** `#F3F4F6` (gris extremadamente claro)
- **Bordes de Cards:** `#E5E7EB` con opacidad 0.5-0.8

---

## 3. Tipografía

### Familia de Fuentes

**Fuente Principal (Recomendada):**
- **Inter** (primera opción) - Excelente legibilidad en pantallas
- **Alternativas:** Roboto, Lato, Poppins, SF Pro Display (Mac)

**Fuente Monospace (Datos/Tablas):**
- **SF Mono** o **Roboto Mono** para números y datos tabulares

### Escala Tipográfica

#### Desktop

**Títulos Principales (H1):**
- Tamaño: `32px` / `2rem`
- Peso: `700` (Bold)
- Altura de línea: `1.2`
- Color: `#333333`

**Títulos de Sección (H2):**
- Tamaño: `24px` / `1.5rem`
- Peso: `600` (Semi-bold)
- Altura de línea: `1.3`
- Color: `#333333`

**Títulos de Card/Widget (H3):**
- Tamaño: `18px` / `1.125rem`
- Peso: `600` (Semi-bold)
- Altura de línea: `1.4`
- Color: `#333333`

**Texto de Cuerpo (Body):**
- Tamaño: `16px` / `1rem`
- Peso: `400` (Regular)
- Altura de línea: `1.5`
- Color: `#333333` (principal), `#666666` (secundario)

**Texto Pequeño (Small):**
- Tamaño: `14px` / `0.875rem`
- Peso: `400` (Regular)
- Altura de línea: `1.5`
- Color: `#666666`

**Texto Muy Pequeño (Caption):**
- Tamaño: `12px` / `0.75rem`
- Peso: `400` (Regular)
- Altura de línea: `1.4`
- Color: `#999999`

**Números/Métricas (KPIs):**
- Tamaño: `36px` - `48px` / `2.25rem` - `3rem`
- Peso: `700` (Bold)
- Altura de línea: `1.1`
- Color: `#333333` o color de acento según contexto

#### Mobile

**Títulos Principales (H1):**
- Tamaño: `28px` / `1.75rem`
- Peso: `700`

**Títulos de Sección (H2):**
- Tamaño: `22px` / `1.375rem`
- Peso: `600`

**Títulos de Card (H3):**
- Tamaño: `16px` / `1rem`
- Peso: `600`

**Texto de Cuerpo:**
- Tamaño: `16px` / `1rem` (mínimo para legibilidad)
- Peso: `400`

**Texto Pequeño:**
- Tamaño: `14px` / `0.875rem`
- Peso: `400`

---

## 4. Espaciado y Layout

### Sistema de Espaciado

**Base:** `4px` (unidad base)

**Escala:**
- `4px` (0.25rem) - Espaciado mínimo
- `8px` (0.5rem) - Espaciado pequeño
- `12px` (0.75rem) - Espaciado medio-pequeño
- `16px` (1rem) - Espaciado estándar
- `24px` (1.5rem) - Espaciado medio
- `32px` (2rem) - Espaciado grande
- `48px` (3rem) - Espaciado muy grande
- `64px` (4rem) - Espaciado extra grande

### Grid System

**Desktop:**
- **Contenedor Principal:** Max-width `1440px` o `1600px`
- **Columnas:** Sistema de 12 columnas
- **Gutter:** `24px` entre columnas
- **Padding de Cards:** `20px` - `24px`

**Mobile:**
- **Contenedor:** 100% width con padding lateral `16px`
- **Columnas:** 1 columna (stack vertical)
- **Gutter:** `16px` entre elementos
- **Padding de Cards:** `16px`

### Layout Principal

#### Desktop

**Sidebar (Navegación):**
- **Ancho:** `240px` - `280px` (fijo)
- **Altura:** 100vh (fijo)
- **Posición:** Fixed left
- **Padding:** `20px` vertical, `16px` horizontal

**Header (Top Bar):**
- **Altura:** `64px` - `72px` (fijo)
- **Posición:** Fixed top
- **Padding:** `16px` - `24px` horizontal
- **Z-index:** Superior a contenido pero inferior a modales

**Área de Contenido Principal:**
- **Margin-left:** Igual al ancho del sidebar
- **Margin-top:** Igual a la altura del header
- **Padding:** `24px` - `32px`
- **Max-width:** Contenedor principal (1440px)

#### Mobile

**Header:**
- **Altura:** `56px` - `64px`
- **Posición:** Fixed top
- **Padding:** `12px` - `16px`

**Sidebar:**
- **Estado por defecto:** Oculto (hamburger menu)
- **Estado abierto:** Overlay full-screen o drawer desde izquierda
- **Ancho:** 80% del viewport o `320px` máximo

**Área de Contenido:**
- **Padding:** `16px`
- **Sin margin** (sidebar oculto por defecto)

---

## 5. Componentes Principales

### 5.1. Cards / Widgets

**Estilo Base:**
- **Fondo:** `#FFFFFF`
- **Border-radius:** `12px` - `16px` (esquinas redondeadas suaves)
- **Sombra:** 
  - Desktop: `0 2px 8px rgba(0, 0, 0, 0.08)` o `0 1px 3px rgba(0, 0, 0, 0.12)`
  - Mobile: `0 1px 4px rgba(0, 0, 0, 0.06)` (más sutil)
- **Padding:** `20px` - `24px` (desktop), `16px` (mobile)
- **Border:** Opcional `1px solid #E5E7EB` (muy sutil)

**Estados:**
- **Hover:** Sombra ligeramente más pronunciada
- **Active:** Border de color de acento (`2px solid #6C5DD3`)

### 5.2. Botones

#### Botón Primario
- **Fondo:** Color de acento principal (`#6C5DD3`)
- **Texto:** `#FFFFFF`
- **Padding:** `12px 24px` (desktop), `10px 20px` (mobile)
- **Border-radius:** `8px` - `10px`
- **Font-size:** `16px` (desktop), `16px` (mobile)
- **Font-weight:** `600` (Semi-bold)
- **Hover:** Fondo más oscuro (`#5A4BC2`)
- **Active:** Escala `0.98` o sombra interna

#### Botón Secundario
- **Fondo:** Transparente o `#F3F4F6`
- **Texto:** `#333333` o color de acento
- **Border:** `1px solid #E5E7EB` o color de acento
- **Padding:** `12px 24px`
- **Border-radius:** `8px`
- **Hover:** Fondo `#F9FAFB`

#### Botón Terciario / Texto
- **Fondo:** Transparente
- **Texto:** Color de acento o `#666666`
- **Padding:** `8px 16px`
- **Hover:** Fondo `#F3F4F6`

#### Botones de Acción Rápida (Iconos)
- **Tamaño:** `40px` x `40px` (desktop), `44px` x `44px` (mobile - touch target)
- **Border-radius:** `50%` (circular) o `8px` (cuadrado redondeado)
- **Fondo:** Color de acento o `#F3F4F6`
- **Icono:** Centrado, tamaño `20px`

### 5.3. Inputs / Formularios

#### Input de Texto
- **Fondo:** `#FFFFFF`
- **Border:** `1px solid #E5E7EB`
- **Border-radius:** `8px`
- **Padding:** `12px 16px`
- **Font-size:** `16px` (importante para mobile - evita zoom automático)
- **Altura:** `44px` mínimo (mobile touch target)
- **Focus:** Border `2px solid #6C5DD3`, outline `none`
- **Placeholder:** Color `#999999`

#### Search Bar
- **Estilo:** Similar a input pero con icono de búsqueda a la izquierda
- **Padding-left:** `40px` (espacio para icono)
- **Border-radius:** `8px` o `24px` (más redondeado para búsqueda)

#### Select / Dropdown
- **Estilo:** Similar a input
- **Icono:** Flecha hacia abajo a la derecha
- **Padding-right:** `40px`

### 5.4. Badges / Etiquetas / Pills

**Estilo:**
- **Display:** `inline-block`
- **Padding:** `6px 12px` (desktop), `8px 14px` (mobile)
- **Border-radius:** `16px` - `20px` (muy redondeado, pill-shaped)
- **Font-size:** `12px` - `14px`
- **Font-weight:** `500` - `600`

**Variantes por Color:**
- **Verde:** Fondo `#E6F7E9`, texto `#52C41A`
- **Azul:** Fondo `#EAF3FF`, texto `#3D8EFF`
- **Naranja:** Fondo `#FFF3E0`, texto `#F7941D`
- **Rojo:** Fondo `#FEE2E2`, texto `#EF4444`
- **Púrpura:** Fondo `#E8E5FF`, texto `#6C5DD3`
- **Gris:** Fondo `#F3F4F6`, texto `#666666`

### 5.5. Avatares

**Estilo:**
- **Forma:** Circular (`border-radius: 50%`)
- **Tamaños:**
  - Pequeño: `32px` x `32px`
  - Medio: `40px` x `40px`
  - Grande: `56px` x `56px`
  - Extra grande: `80px` x `80px`
- **Border:** Opcional `2px solid #FFFFFF` para superposición
- **Fondo:** `#E5E7EB` si no hay imagen
- **Iniciales:** Centradas, color `#666666`, font-weight `600`

### 5.6. Iconografía

**Estilo:**
- **Tipo:** Outline icons (línea delgada) por defecto
- **Grosor de línea:** `1.5px` - `2px`
- **Tamaños estándar:**
  - Pequeño: `16px` x `16px`
  - Medio: `20px` x `20px`
  - Grande: `24px` x `24px`
- **Color:** `#666666` por defecto, `#6C5DD3` para activos
- **Librería recomendada:** Heroicons, Feather Icons, Lucide

**Estados:**
- **Default:** Outline, color `#666666`
- **Hover:** Color de acento o `#333333`
- **Active:** Filled o color de acento `#6C5DD3`

### 5.7. Tabs / Pestañas

**Estilo:**
- **Display:** Flex horizontal
- **Border-bottom:** `2px solid #E5E7EB`
- **Padding:** `0 16px` (espaciado entre tabs)

**Tab Individual:**
- **Padding:** `12px 16px` (desktop), `10px 14px` (mobile)
- **Font-size:** `16px` (desktop), `15px` (mobile)
- **Font-weight:** `500` (inactivo), `600` (activo)
- **Color:** `#666666` (inactivo), `#333333` (activo)
- **Border-bottom:** `2px solid transparent` (inactivo), `2px solid #6C5DD3` (activo)

### 5.8. Kanban Board / Columnas

**Columnas:**
- **Fondo:** `#F7F8F9` (muy sutil)
- **Border-radius:** `8px`
- **Padding:** `16px`
- **Min-width:** `280px` - `320px` (desktop)
- **Margin-right:** `16px` - `24px`

**Header de Columna:**
- **Título:** Uppercase, font-size `12px`, font-weight `600`, color `#666666`
- **Contador:** Badge con número, color de acento
- **Acciones:** Icono de tres puntos (`...`), color `#999999`

**Cards en Kanban:**
- **Fondo:** `#FFFFFF`
- **Border-radius:** `8px`
- **Sombra:** `0 1px 3px rgba(0, 0, 0, 0.1)`
- **Padding:** `16px`
- **Margin-bottom:** `12px`
- **Hover:** Sombra más pronunciada, cursor `grab` o `pointer`

### 5.9. Tablas / Listas

**Tabla:**
- **Fondo:** `#FFFFFF`
- **Border:** `1px solid #E5E7EB`
- **Border-radius:** `8px`
- **Overflow:** `hidden`

**Header de Tabla:**
- **Fondo:** `#F9FAFB`
- **Padding:** `12px 16px`
- **Font-weight:** `600`
- **Font-size:** `14px`
- **Color:** `#666666`
- **Border-bottom:** `1px solid #E5E7EB`

**Filas:**
- **Padding:** `16px`
- **Border-bottom:** `1px solid #F3F4F6`
- **Hover:** Fondo `#F9FAFB`

### 5.10. Modales / Dialogs

**Overlay:**
- **Fondo:** `rgba(0, 0, 0, 0.5)` o `rgba(0, 0, 0, 0.6)`
- **Backdrop-filter:** `blur(4px)` (opcional, efecto moderno)

**Modal:**
- **Fondo:** `#FFFFFF`
- **Border-radius:** `16px`
- **Sombra:** `0 20px 25px -5px rgba(0, 0, 0, 0.1)`
- **Padding:** `24px` - `32px`
- **Max-width:** `500px` (pequeño), `800px` (medio), `1200px` (grande)
- **Max-height:** `90vh` con scroll interno si es necesario

---

## 6. Estados y Feedback Visual

### 6.1. Estados de Interacción

**Hover:**
- Transición suave: `transition: all 0.2s ease`
- Cambio de color/sombra sutil
- Cursor `pointer` para elementos clickeables

**Active/Pressed:**
- Escala: `transform: scale(0.98)`
- O sombra interna: `box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1)`

**Focus:**
- Outline: `2px solid #6C5DD3`
- Outline-offset: `2px`
- Importante para accesibilidad

**Disabled:**
- Opacidad: `0.5` o `0.6`
- Cursor: `not-allowed`
- Pointer-events: `none`

### 6.2. Estados de Carga

**Skeleton Loaders:**
- Fondo: `#F3F4F6`
- Animación: `shimmer` o `pulse`
- Border-radius: Igual al elemento que reemplaza

**Spinners:**
- Color: Color de acento principal
- Tamaño: `24px` - `32px`
- Animación: `spin` suave

### 6.3. Notificaciones / Alerts

**Toast Notifications:**
- Posición: Top-right (desktop), Bottom (mobile)
- Fondo: `#FFFFFF`
- Sombra: `0 10px 15px -3px rgba(0, 0, 0, 0.1)`
- Border-radius: `8px`
- Border-left: `4px solid` (color según tipo)
- Padding: `16px`
- Max-width: `400px`

**Tipos:**
- **Success:** Border verde `#52C41A`
- **Error:** Border rojo `#EF4444`
- **Warning:** Border naranja `#F7941D`
- **Info:** Border azul `#3D8EFF`

---

## 7. Responsive Design

### Breakpoints

```css
/* Mobile First Approach */
/* Mobile: por defecto (sin media query) */
/* Tablet: 768px y superior */
/* Desktop: 1024px y superior */
/* Large Desktop: 1440px y superior */
```

**Breakpoints específicos:**
- **Mobile:** `0px - 767px`
- **Tablet:** `768px - 1023px`
- **Desktop:** `1024px - 1439px`
- **Large Desktop:** `1440px+`

### Adaptaciones Mobile

#### Sidebar
- **Estado:** Oculto por defecto
- **Trigger:** Hamburger menu en header
- **Apertura:** Drawer desde izquierda o overlay full-screen
- **Ancho:** 80% viewport o `320px` máximo

#### Header
- **Elementos:** Reducir a esenciales
- **Search:** Puede colapsar en icono, expandirse al hacer tap
- **Acciones:** Agrupar en menú de tres puntos

#### Cards/Widgets
- **Layout:** Stack vertical (1 columna)
- **Padding:** Reducir a `16px`
- **Font-sizes:** Mantener legibilidad mínima `16px` para body

#### Kanban Board
- **Columnas:** Stack vertical en lugar de horizontal
- **Scroll:** Horizontal dentro de cada columna si es necesario
- **Cards:** Full-width dentro de columna

#### Tablas
- **Estrategia:** 
  - Opción 1: Scroll horizontal
  - Opción 2: Convertir a cards (cada fila = card)
  - Opción 3: Mostrar columnas críticas, resto en modal

#### Botones
- **Tamaño mínimo:** `44px` x `44px` (touch target)
- **Espaciado:** Mínimo `8px` entre botones

#### Inputs
- **Font-size:** Mínimo `16px` (evita zoom automático en iOS)
- **Altura:** Mínimo `44px`

---

## 8. Animaciones y Transiciones

### Principios

- **Duración:** Cortas (`0.2s` - `0.3s` para la mayoría)
- **Easing:** `ease` o `ease-in-out` (suaves)
- **Propósito:** Mejorar UX, no distraer

### Transiciones Comunes

```css
/* Transición estándar */
transition: all 0.2s ease;

/* Hover de botones */
transition: background-color 0.2s ease, transform 0.1s ease;

/* Modales */
transition: opacity 0.3s ease, transform 0.3s ease;

/* Sidebar mobile */
transition: transform 0.3s ease;
```

### Animaciones Específicas

**Fade In:**
```css
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
```

**Slide In (Sidebar):**
```css
@keyframes slideInLeft {
  from { transform: translateX(-100%); }
  to { transform: translateX(0); }
}
```

**Pulse (Loading):**
```css
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}
```

**Shimmer (Skeleton):**
```css
@keyframes shimmer {
  0% { background-position: -1000px 0; }
  100% { background-position: 1000px 0; }
}
```

---

## 9. Accesibilidad

### Contraste

- **Texto normal:** Mínimo ratio 4.5:1 con fondo
- **Texto grande (18px+):** Mínimo ratio 3:1
- **Elementos interactivos:** Mínimo ratio 3:1

### Navegación por Teclado

- **Focus visible:** Outline claro en todos los elementos interactivos
- **Tab order:** Lógico y predecible
- **Skip links:** Para saltar navegación repetitiva

### Touch Targets (Mobile)

- **Tamaño mínimo:** `44px` x `44px`
- **Espaciado:** Mínimo `8px` entre elementos clickeables

### Screen Readers

- **Labels:** Todos los inputs deben tener labels asociados
- **ARIA:** Usar atributos ARIA cuando sea necesario
- **Alt text:** Todas las imágenes deben tener texto alternativo

---

## 10. Implementación Técnica

### CSS Variables (Custom Properties)

```css
:root {
  /* Colores principales */
  --color-primary: #6C5DD3;
  --color-primary-hover: #5A4BC2;
  --color-primary-light: #E8E5FF;
  
  /* Fondos */
  --bg-sidebar: #191F34;
  --bg-main: #F7F8F9;
  --bg-card: #FFFFFF;
  
  /* Texto */
  --text-primary: #333333;
  --text-secondary: #666666;
  --text-tertiary: #999999;
  
  /* Bordes */
  --border-color: #E5E7EB;
  
  /* Espaciado */
  --spacing-xs: 4px;
  --spacing-sm: 8px;
  --spacing-md: 16px;
  --spacing-lg: 24px;
  --spacing-xl: 32px;
  
  /* Sombras */
  --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.12);
  --shadow-md: 0 2px 8px rgba(0, 0, 0, 0.08);
  --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  
  /* Border radius */
  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 16px;
  --radius-full: 9999px;
}
```

### Estructura de Archivos CSS

```
assets/
├── css/
│   ├── base/
│   │   ├── reset.css
│   │   ├── typography.css
│   │   └── variables.css
│   ├── components/
│   │   ├── buttons.css
│   │   ├── cards.css
│   │   ├── forms.css
│   │   ├── navigation.css
│   │   └── ...
│   ├── layouts/
│   │   ├── sidebar.css
│   │   ├── header.css
│   │   └── grid.css
│   └── dashboard.css (main)
```

---

## 11. Ejemplos de Componentes

### Card de Métrica (KPI)

```html
<div class="metric-card">
  <div class="metric-label">Total Propiedades</div>
  <div class="metric-value">1,234</div>
  <div class="metric-change positive">+12% este mes</div>
</div>
```

### Botón Primario

```html
<button class="btn btn-primary">
  <span class="btn-icon">+</span>
  Nueva Propiedad
</button>
```

### Badge de Estado

```html
<span class="badge badge-success">Activo</span>
<span class="badge badge-warning">Pendiente</span>
<span class="badge badge-danger">Urgente</span>
```

### Card de Lead (Kanban)

```html
<div class="kanban-card">
  <div class="card-header">
    <img src="avatar.jpg" class="avatar" alt="Juan Pérez">
    <button class="card-menu">⋯</button>
  </div>
  <div class="card-body">
    <h4 class="card-title">Juan Pérez</h4>
    <p class="card-meta">Hoy, 6:38 PM</p>
    <div class="card-info">
      <span class="info-item">📧 juan@example.com</span>
      <span class="info-item">📞 +34 600 123 456</span>
    </div>
  </div>
  <div class="card-footer">
    <button class="btn-status status-contacted">Contactado</button>
  </div>
</div>
```

---

## 12. Checklist de Implementación

### Desktop
- [ ] Sidebar con navegación oscura implementada
- [ ] Header fijo con búsqueda y acciones
- [ ] Grid system para widgets/cards
- [ ] Cards con sombras y border-radius consistentes
- [ ] Botones con estados hover/active
- [ ] Formularios con inputs estilizados
- [ ] Kanban board funcional (si aplica)
- [ ] Tablas responsivas
- [ ] Modales con overlay

### Mobile
- [ ] Sidebar colapsable (hamburger menu)
- [ ] Header adaptado para mobile
- [ ] Cards en stack vertical
- [ ] Touch targets de mínimo 44px
- [ ] Inputs con font-size mínimo 16px
- [ ] Kanban adaptado a scroll vertical
- [ ] Navegación bottom bar (opcional)

### Accesibilidad
- [ ] Contraste de colores verificado
- [ ] Navegación por teclado funcional
- [ ] Focus states visibles
- [ ] Labels en todos los inputs
- [ ] Alt text en imágenes

---

## 13. Recursos y Referencias

### Librerías Recomendadas

- **Iconos:** Heroicons, Feather Icons, Lucide
- **Gráficas:** Chart.js, Recharts (React)
- **Animaciones:** Framer Motion (React), GSAP
- **UI Components:** Tailwind CSS (opcional, para acelerar desarrollo)

### Herramientas de Diseño

- **Figma:** Para mockups y prototipos
- **Adobe XD:** Alternativa
- **Sketch:** Alternativa Mac

### Testing de Accesibilidad

- **WAVE:** Extension de navegador
- **axe DevTools:** Extension de navegador
- **Lighthouse:** Herramienta de Chrome DevTools

---

## Conclusión

Esta guía establece la base visual para el dashboard de Inmopress, combinando elementos de diseño moderno con funcionalidad empresarial. El estilo es limpio, profesional y optimizado para productividad tanto en desktop como en mobile.

**Próximos pasos:**
1. Crear componentes base en código
2. Desarrollar sistema de diseño en Figma/Sketch
3. Implementar componentes uno por uno
4. Testing en diferentes dispositivos y navegadores
5. Iterar basándose en feedback de usuarios

---

**Documentación generada por:** Claude (Anthropic)  
**Fecha:** 6 de Febrero de 2026  
**Versión:** 1.0.0  
**Basado en:** Análisis de dashboards modernos de CRM y gestión inmobiliaria
