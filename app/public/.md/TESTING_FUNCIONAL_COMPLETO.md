# Testing Funcional Completo - Dashboard Inmopress

## Fecha: 6 de Febrero de 2026
## Versión del Sistema: 1.0.0

---

## 🚀 Instrucciones de Inicio

**URL del Dashboard:** `http://inmopress.local/wp-admin/admin.php?page=inmopress-panel`

**Requisitos:**
1. El sitio Local debe estar corriendo
2. Debes estar logueado en WordPress
3. Navegador actualizado (Chrome, Firefox, Safari o Edge)

---

## ✅ Checklist de Testing Funcional

### 1. Testing de Búsqueda Global

**Ubicación:** Dashboard home → Sección "Búsqueda Global"

#### 1.1. Funcionalidad Básica
- [ ] **Input visible** - El campo de búsqueda se muestra correctamente
- [ ] **Placeholder** - Muestra "Buscar propiedades, clientes, leads..."
- [ ] **Escribir 1 carácter** - No debe hacer búsqueda (mínimo 2 caracteres)
- [ ] **Escribir 2+ caracteres** - Debe hacer búsqueda después de 300ms (debounce)
- [ ] **Resultados aparecen** - Dropdown con resultados se muestra debajo del input

#### 1.2. Resultados de Búsqueda
- [ ] **Propiedades encontradas** - Muestra propiedades con formato "REF-XXX - Título"
- [ ] **Clientes encontrados** - Muestra clientes con nombre
- [ ] **Leads encontrados** - Muestra leads con nombre
- [ ] **Badges de tipo** - Cada resultado muestra badge con tipo (Propiedad/Cliente/Lead)
- [ ] **Orden correcto** - Propiedades primero, luego Clientes, luego Leads

#### 1.3. Interacción con Resultados
- [ ] **Click en resultado** - Navega a la página de edición correcta
- [ ] **Hover en resultado** - Cambia color de fondo (hover state)
- [ ] **Click fuera** - Oculta el dropdown de resultados
- [ ] **Tecla ESC** - Oculta el dropdown de resultados
- [ ] **Click en overlay** - No cierra el dropdown (click dentro de resultados)

#### 1.4. Casos Especiales
- [ ] **Sin resultados** - Muestra mensaje "No se encontraron resultados"
- [ ] **Búsqueda por referencia** - Encuentra propiedades por referencia (ej: "REF-123")
- [ ] **Búsqueda por título** - Encuentra propiedades por título
- [ ] **Búsqueda por nombre cliente** - Encuentra clientes por nombre
- [ ] **Búsqueda texto inexistente** - Muestra mensaje de "sin resultados"
- [ ] **Límite de resultados** - Muestra máximo 15 resultados (5 por tipo)

#### 1.5. Performance
- [ ] **Debounce funciona** - No busca en cada tecla, espera 300ms
- [ ] **Respuesta rápida** - Los resultados aparecen en < 500ms
- [ ] **Sin errores en consola** - No hay errores JavaScript

**Resultado esperado:** ✅ Búsqueda funcional con resultados en tiempo real

---

### 2. Testing de Enlaces

#### 2.1. Navegación Sidebar (11 enlaces)

**Verificar cada enlace:**
- [ ] **Panel** → `/wp-admin/admin.php?page=inmopress-panel`
- [ ] **Inmuebles** → `/wp-admin/admin.php?page=inmopress-panel&tab=properties`
- [ ] **Prospectos** → `/wp-admin/admin.php?page=inmopress-panel&tab=leads`
- [ ] **Clientes** → `/wp-admin/admin.php?page=inmopress-panel&tab=clients`
- [ ] **Oportunidades** → `/wp-admin/admin.php?page=inmopress-panel&tab=opportunities`
- [ ] **Agencias** → `/wp-admin/admin.php?page=inmopress-panel&tab=agencies`
- [ ] **Agentes** → `/wp-admin/admin.php?page=inmopress-panel&tab=agents`
- [ ] **Visitas** → `/wp-admin/admin.php?page=inmopress-panel&tab=visits`
- [ ] **Propietarios** → `/wp-admin/admin.php?page=inmopress-panel&tab=owners`
- [ ] **Transacciones** → `/wp-admin/admin.php?page=inmopress-panel&tab=transactions`
- [ ] **Eventos** → `/wp-admin/admin.php?page=inmopress-panel&tab=events`

**Verificaciones:**
- [ ] El enlace activo se resalta con color púrpura (#6C5DD3)
- [ ] La URL cambia correctamente
- [ ] El contenido se carga sin errores
- [ ] No hay errores 404
- [ ] En mobile, el sidebar se cierra después de hacer click

---

#### 2.2. Enlaces del Dashboard Home

**Hero Actions:**
- [ ] **"Nuevo inmueble"** → Abre formulario de nueva propiedad
- [ ] **"Nuevo cliente"** → Abre formulario de nuevo cliente
- [ ] **"Nueva tarea"** → Abre formulario de nuevo evento

**Summary Cards:**
- [ ] **Card "Inmuebles"** → Navega a listado de propiedades
- [ ] **Card "Clientes"** → Navega a listado de clientes
- [ ] **Card "Prospectos"** → Navega a listado de leads
- [ ] **Card "Visitas"** → Navega a listado de visitas
- [ ] **Card "Oportunidades"** → Navega a oportunidades
- [ ] **Card "Eventos"** → Navega a calendario de eventos

**Verificaciones:**
- [ ] Los botones tienen hover effect
- [ ] Los enlaces funcionan correctamente
- [ ] Las cards tienen hover effect

---

#### 2.3. Enlaces en Listados

**Listado de Propiedades:**
- [ ] **Click en referencia/título** → Abre formulario de edición
- [ ] **Click en nombre de propietario** → Abre formulario del propietario
- [ ] **Click en "Ver leads"** → Muestra leads relacionados con la propiedad
- [ ] **Paginación** → Cambia de página correctamente
- [ ] **Botones de acción** → Ejecutan acción correspondiente

**Verificaciones:**
- [ ] Los enlaces abren en la misma ventana
- [ ] Los parámetros GET se pasan correctamente (`?edit=ID`)
- [ ] La paginación mantiene los filtros activos

---

### 3. Testing de Formularios

#### 3.1. Formulario de Propiedades

**URL:** `/wp-admin/admin.php?page=inmopress-panel&tab=properties&new=1`

**Checklist:**
- [ ] **Título del inmueble** - Escribir texto → Se guarda correctamente
- [ ] **Campos ACF** - Completar campos → Se guardan correctamente
- [ ] **Upload de imágenes** - Subir imágenes → Se muestran en galería
- [ ] **Taxonomías** - Seleccionar operación, tipo, ciudad → Se guardan correctamente
- [ ] **Botón Guardar** - Click → Guarda y redirige al listado
- [ ] **Botón Cancelar** - Click → Vuelve al listado sin guardar

**Verificaciones:**
- [ ] Validación de campos requeridos funciona
- [ ] Los campos se prellenan al editar
- [ ] Los cambios se guardan correctamente
- [ ] No hay errores de JavaScript en consola
- [ ] Los mensajes de éxito/error se muestran

---

#### 3.2. Formulario de Búsqueda (Header)

**Ubicación:** Header del dashboard (cuando no hay título)

**Checklist:**
- [ ] **Escribir búsqueda** - Escribir texto → Envía formulario GET
- [ ] **Presionar Enter** - Presionar Enter → Envía formulario
- [ ] **Click en icono** - Click en icono de búsqueda → Envía formulario
- [ ] **Resultados** - Verificar que muestra propiedades filtradas

**Verificaciones:**
- [ ] La búsqueda funciona correctamente
- [ ] Los resultados se filtran por referencia o título
- [ ] La URL incluye parámetro `?s=query`

---

#### 3.3. Formulario de Filtros (Listado)

**Ubicación:** Barra de filtros en listado de propiedades

**Checklist:**
- [ ] **Búsqueda** - Escribir en campo → Filtra resultados
- [ ] **Select Tipo** - Seleccionar tipo → Filtra por tipo
- [ ] **Select Operación** - Seleccionar operación → Filtra por operación
- [ ] **Select Ciudad** - Seleccionar ciudad → Filtra por ciudad
- [ ] **Precio mínimo** - Escribir precio → Filtra por precio
- [ ] **Precio máximo** - Escribir precio → Filtra por precio
- [ ] **Botón Filtrar** - Click → Aplica todos los filtros
- [ ] **Botón Limpiar** - Click → Limpia todos los filtros

**Verificaciones:**
- [ ] Los filtros se combinan correctamente
- [ ] Los valores se preservan en los selects después de filtrar
- [ ] La URL incluye todos los parámetros de filtro
- [ ] Los resultados se actualizan correctamente

---

### 4. Testing de Gráficas

#### 4.1. Gráfica de Actividad (Línea)

**Ubicación:** Dashboard home → Sección de gráficas

**Checklist:**
- [ ] **Renderizado** - La gráfica se muestra correctamente
- [ ] **Canvas presente** - Elemento `#inmopress-chart-activity` existe
- [ ] **Datos de propiedades** - Línea púrpura (#6C5DD3) muestra datos
- [ ] **Datos de clientes** - Línea verde (#52C41A) muestra datos
- [ ] **Datos de leads** - Línea azul (#3D8EFF) muestra datos
- [ ] **Tooltip** - Hover sobre puntos → Muestra valores correctos
- [ ] **Leyenda** - Click en leyenda → Oculta/muestra línea correspondiente
- [ ] **Responsive** - Redimensionar ventana → Gráfica se adapta
- [ ] **Labels** - Los labels de fechas se muestran correctamente

**Verificaciones:**
- [ ] Los colores son correctos según nueva paleta
- [ ] Los datos coinciden con los KPIs mostrados arriba
- [ ] La gráfica es responsive
- [ ] No hay errores en consola del navegador
- [ ] Chart.js se carga correctamente

---

#### 4.2. Gráfica de Operaciones (Doughnut) ⭐ MEJORADA

**Ubicación:** Dashboard home → Sección de gráficas

**Checklist:**
- [ ] **Renderizado** - La gráfica se muestra correctamente
- [ ] **Canvas presente** - Elemento `#inmopress-chart-operations` existe
- [ ] **Datos reales** - Los números coinciden con propiedades reales del sistema
- [ ] **Segmento Venta** - Segmento púrpura (#6C5DD3) muestra cantidad correcta
- [ ] **Segmento Alquiler** - Segmento verde (#52C41A) muestra cantidad correcta
- [ ] **Tooltip** - Hover sobre segmentos → Muestra cantidad y porcentaje
- [ ] **Leyenda** - Muestra "Venta" y "Alquiler" correctamente
- [ ] **Porcentajes** - Los porcentajes se calculan correctamente

**Casos Especiales:**
- [ ] **Solo venta** - Si solo hay propiedades de venta → Solo muestra segmento de venta
- [ ] **Solo alquiler** - Si solo hay propiedades de alquiler → Solo muestra segmento de alquiler
- [ ] **Ambos tipos** - Si hay ambos → Muestra ambos segmentos con porcentajes
- [ ] **Sin datos** - Si no hay propiedades → Muestra mensaje "No hay datos de operaciones disponibles"
- [ ] **Otras operaciones** - Si hay traspasos u otros → Muestra segmento "Otras" en azul

**Verificaciones:**
- [ ] ✅ Los datos son REALES (no de ejemplo) - Verificar contando propiedades manualmente
- [ ] Los colores son correctos (púrpura para venta, verde para alquiler)
- [ ] Los porcentajes se calculan correctamente (suma = 100%)
- [ ] La gráfica se adapta si hay "Otras" operaciones
- [ ] No hay errores en consola del navegador

**Cómo verificar datos reales:**
1. Contar propiedades de venta manualmente en el listado
2. Contar propiedades de alquiler manualmente
3. Comparar con los números en la gráfica
4. Deben coincidir exactamente

**Resultado esperado:** ✅ Gráfica muestra datos reales de operaciones

---

### 5. Testing de Sidebar Mobile

**Dispositivo:** Mobile o ventana < 1024px (usar DevTools para simular)

**Checklist:**
- [ ] **Sidebar oculto por defecto** - No se ve al cargar la página
- [ ] **Hamburger menu visible** - Botón de menú aparece en header
- [ ] **Click en hamburger** - Abre sidebar desde la izquierda con animación
- [ ] **Overlay aparece** - Fondo oscuro semitransparente aparece
- [ ] **Click en overlay** - Cierra sidebar
- [ ] **Tecla ESC** - Cierra sidebar
- [ ] **Click en enlace** - Cierra sidebar después de navegar
- [ ] **Cambio a desktop** - Al redimensionar a > 1024px → Sidebar se cierra automáticamente
- [ ] **Scroll del body** - Cuando sidebar está abierto → Body no hace scroll

**Verificaciones:**
- [ ] Las animaciones son suaves
- [ ] El overlay tiene opacidad correcta (50%)
- [ ] El sidebar tiene ancho correcto (320px o 85vw)
- [ ] No hay glitches visuales

---

### 6. Testing de Elementos Visuales

#### 6.1. Admin Bar y Sidebar de WordPress

**Verificaciones:**
- [ ] **Admin bar oculta** - La barra superior de WordPress NO se muestra
- [ ] **Sidebar WordPress oculta** - El sidebar izquierdo de WordPress NO se muestra
- [ ] **Pantalla completa** - El dashboard ocupa toda la pantalla
- [ ] **Sin elementos WordPress** - No se ven elementos del admin de WordPress

---

#### 6.2. Colores y Estilos

**Verificaciones:**
- [ ] **Sidebar oscuro** - Fondo #191F34 (azul oscuro)
- [ ] **Sidebar hover** - Hover en items muestra #2D3447
- [ ] **Sidebar activo** - Item activo muestra #6C5DD3 (púrpura)
- [ ] **Botones primarios** - Color púrpura #6C5DD3
- [ ] **Cards** - Fondo blanco con sombra sutil
- [ ] **Textos** - Colores correctos (#333333, #666666, #999999)

---

## 📊 Resumen de Testing

### Funcionalidades Implementadas ✅

1. **Búsqueda Global AJAX**
   - ✅ Handler AJAX implementado
   - ✅ Búsqueda en propiedades, clientes y leads
   - ✅ Debounce de 300ms
   - ✅ Dropdown con resultados
   - ✅ Manejo de errores

2. **Gráfica Doughnut con Datos Reales**
   - ✅ Método `get_operations_data()` implementado
   - ✅ Cuenta propiedades por operación usando taxonomía
   - ✅ JavaScript actualizado para usar datos reales
   - ✅ Manejo de casos sin datos
   - ✅ Tooltips con porcentajes

3. **Ocultar Admin Bar y Sidebar WordPress**
   - ✅ Admin bar oculta en dashboard
   - ✅ Sidebar WordPress oculta
   - ✅ Pantalla completa funcional

---

## 🐛 Issues Encontrados Durante Testing

### Issue #1: [Título del Issue]
- **Descripción:**
- **Ubicación:** 
- **Severidad:** Alta/Media/Baja
- **Pasos para reproducir:**
  1. 
  2. 
  3. 
- **Comportamiento esperado:**
- **Comportamiento actual:**
- **Screenshot:** (si aplica)

---

### Issue #2: [Título del Issue]
- **Descripción:**
- **Ubicación:**
- **Severidad:**
- **Pasos para reproducir:**
- **Comportamiento esperado:**
- **Comportamiento actual:**

---

## 📝 Notas del Testing

**Fecha de testing:** ________________  
**Navegador usado:** ________________  
**Versión del navegador:** ________________  
**Dispositivo:** Desktop / Tablet / Mobile  
**Usuario de prueba:** ________________

**Observaciones:**
- 

**Recomendaciones:**
- 

---

## ✅ Checklist Final

### Búsqueda Global
- [ ] Funciona correctamente
- [ ] Muestra resultados en tiempo real
- [ ] Los enlaces funcionan
- [ ] Manejo de errores funciona

### Gráfica Doughnut
- [ ] Muestra datos reales (verificado manualmente)
- [ ] Los números coinciden con propiedades reales
- [ ] Los porcentajes son correctos
- [ ] Se adapta a diferentes casos (solo venta, solo alquiler, ambos)

### Enlaces
- [ ] Todos los enlaces del sidebar funcionan
- [ ] Enlaces del dashboard home funcionan
- [ ] Enlaces en listados funcionan

### Formularios
- [ ] Formulario de propiedades funciona
- [ ] Formulario de búsqueda funciona
- [ ] Formulario de filtros funciona

### Responsive
- [ ] Sidebar mobile funciona
- [ ] Elementos se adaptan correctamente
- [ ] Touch targets son adecuados (44px mínimo)

---

**Testing completado:** ✅ / ❌  
**Estado general:** Listo para producción / Requiere correcciones
