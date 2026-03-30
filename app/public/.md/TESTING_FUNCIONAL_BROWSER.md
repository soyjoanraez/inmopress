# Testing Funcional en Navegador - Dashboard Inmopress

## Fecha: 6 de Febrero de 2026
## Versión del Sistema: 1.0.0

---

## Instrucciones de Testing

Este documento contiene un checklist completo para probar todas las funcionalidades del dashboard en un navegador real.

**URL del Dashboard:** `/wp-admin/admin.php?page=inmopress-panel`

---

## 1. Testing de Enlaces

### 1.1. Navegación Principal (Sidebar)

**Ubicación:** Sidebar izquierdo

**Checklist:**
- [ ] **Panel** - Click en "Panel" → Debe mostrar dashboard home
- [ ] **Inmuebles** - Click en "Inmuebles" → Debe mostrar listado de propiedades
- [ ] **Prospectos** - Click en "Prospectos" → Debe mostrar listado de leads
- [ ] **Clientes** - Click en "Clientes" → Debe mostrar listado de clientes
- [ ] **Oportunidades** - Click en "Oportunidades" → Debe mostrar oportunidades
- [ ] **Agencias** - Click en "Agencias" → Debe mostrar listado de agencias
- [ ] **Agentes** - Click en "Agentes" → Debe mostrar listado de agentes
- [ ] **Visitas** - Click en "Visitas" → Debe mostrar listado de visitas
- [ ] **Propietarios** - Click en "Propietarios" → Debe mostrar listado de propietarios
- [ ] **Transacciones** - Click en "Transacciones" → Debe mostrar listado de transacciones
- [ ] **Eventos** - Click en "Eventos" → Debe mostrar calendario de eventos

**Verificaciones:**
- [ ] El enlace activo se resalta con color púrpura
- [ ] La URL cambia correctamente
- [ ] El contenido se carga sin errores
- [ ] En mobile, el sidebar se cierra después de hacer click

---

### 1.2. Enlaces del Dashboard Home

**Ubicación:** Hero section y Summary Cards

**Checklist:**
- [ ] **"Nuevo inmueble"** - Click → Debe abrir formulario de nueva propiedad
- [ ] **"Nuevo cliente"** - Click → Debe abrir formulario de nuevo cliente
- [ ] **"Nueva tarea"** - Click → Debe abrir formulario de nuevo evento
- [ ] **Summary Cards** - Click en cada card → Debe navegar a la sección correspondiente

**Verificaciones:**
- [ ] Los botones tienen hover effect
- [ ] Los enlaces funcionan correctamente
- [ ] Las cards tienen hover effect

---

### 1.3. Enlaces en Listados

**Ubicación:** Tablas de listados (propiedades, clientes, etc.)

**Checklist:**
- [ ] **Enlace de edición** - Click en título/referencia → Debe abrir formulario de edición
- [ ] **Enlace de propietario** - Click en nombre de propietario → Debe abrir formulario del propietario
- [ ] **Enlace de leads relacionados** - Click en "Ver leads" → Debe mostrar leads relacionados
- [ ] **Paginación** - Click en números de página → Debe cambiar de página
- [ ] **Botones de acción** - Click en iconos de acción → Debe ejecutar acción correspondiente

**Verificaciones:**
- [ ] Los enlaces abren en la misma ventana
- [ ] Los parámetros GET se pasan correctamente (`?edit=ID`)
- [ ] La paginación mantiene los filtros activos

---

## 2. Testing de Formularios

### 2.1. Formulario de Propiedades

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

### 2.2. Formulario de Clientes/Leads

**URL:** `/wp-admin/admin.php?page=inmopress-panel&tab=clients&new=1`

**Checklist:**
- [ ] **Campos básicos** - Nombre, teléfono, email → Se guardan correctamente
- [ ] **Campos ACF** - Completar todos los campos → Se guardan correctamente
- [ ] **Botón Guardar** - Click → Guarda y redirige
- [ ] **Validación** - Dejar campos requeridos vacíos → Muestra error

**Verificaciones:**
- [ ] Validación de email funciona
- [ ] Validación de teléfono funciona
- [ ] Los campos se prellenan al editar

---

### 2.3. Formulario de Búsqueda

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

### 2.4. Formulario de Filtros (Listado de Propiedades)

**Ubicación:** Barra de filtros en listado de propiedades

**Checklist:**
- [ ] **Búsqueda** - Escribir en campo de búsqueda → Filtra resultados
- [ ] **Select Tipo** - Seleccionar tipo → Filtra por tipo
- [ ] **Select Operación** - Seleccionar operación → Filtra por operación
- [ ] **Select Ciudad** - Seleccionar ciudad → Filtra por ciudad
- [ ] **Precio mínimo** - Escribir precio mínimo → Filtra por precio
- [ ] **Precio máximo** - Escribir precio máximo → Filtra por precio
- [ ] **Botón Filtrar** - Click → Aplica todos los filtros
- [ ] **Botón Limpiar** - Click → Limpia todos los filtros

**Verificaciones:**
- [ ] Los filtros se combinan correctamente
- [ ] Los valores se preservan en los selects después de filtrar
- [ ] La URL incluye todos los parámetros de filtro
- [ ] Los resultados se actualizan correctamente

---

## 3. Testing de Búsqueda Global

**Ubicación:** Dashboard home, sección "Búsqueda Global"

**Checklist:**
- [ ] **Escribir menos de 2 caracteres** - No debe hacer búsqueda
- [ ] **Escribir 2+ caracteres** - Debe mostrar resultados después de 300ms
- [ ] **Resultados de propiedades** - Debe mostrar propiedades encontradas
- [ ] **Resultados de clientes** - Debe mostrar clientes encontrados
- [ ] **Resultados de leads** - Debe mostrar leads encontrados
- [ ] **Click en resultado** - Debe navegar a la página de edición
- [ ] **Click fuera** - Debe ocultar resultados
- [ ] **Tecla ESC** - Debe ocultar resultados
- [ ] **Sin resultados** - Debe mostrar mensaje "No se encontraron resultados"
- [ ] **Error de conexión** - Simular error → Debe mostrar mensaje de error

**Verificaciones:**
- [ ] El debounce funciona (no busca en cada tecla)
- [ ] Los resultados se muestran en dropdown
- [ ] Los resultados tienen formato correcto (título + tipo)
- [ ] Los enlaces funcionan correctamente
- [ ] No hay errores en consola del navegador

**Casos de prueba:**
- [ ] Buscar por referencia de propiedad
- [ ] Buscar por título de propiedad
- [ ] Buscar por nombre de cliente
- [ ] Buscar por nombre de lead
- [ ] Buscar texto que no existe

---

## 4. Testing de Gráficas

### 4.1. Gráfica de Actividad (Línea)

**Ubicación:** Dashboard home, sección de gráficas

**Checklist:**
- [ ] **Renderizado** - La gráfica se muestra correctamente
- [ ] **Datos de propiedades** - Línea púrpura muestra datos correctos
- [ ] **Datos de clientes** - Línea verde muestra datos correctos
- [ ] **Datos de leads** - Línea azul muestra datos correctos
- [ ] **Tooltip** - Hover sobre puntos → Muestra valores
- [ ] **Leyenda** - Click en leyenda → Oculta/muestra línea
- [ ] **Responsive** - Redimensionar ventana → Gráfica se adapta

**Verificaciones:**
- [ ] Los colores son correctos (púrpura, verde, azul)
- [ ] Los datos coinciden con los KPIs mostrados
- [ ] La gráfica es responsive
- [ ] No hay errores en consola

---

### 4.2. Gráfica de Operaciones (Doughnut)

**Ubicación:** Dashboard home, sección de gráficas

**Checklist:**
- [ ] **Renderizado** - La gráfica se muestra correctamente
- [ ] **Datos de venta** - Segmento púrpura muestra cantidad correcta
- [ ] **Datos de alquiler** - Segmento verde muestra cantidad correcta
- [ ] **Datos reales** - Los números coinciden con propiedades reales
- [ ] **Tooltip** - Hover sobre segmentos → Muestra cantidad y porcentaje
- [ ] **Leyenda** - Muestra "Venta" y "Alquiler" correctamente
- [ ] **Sin datos** - Si no hay propiedades → Muestra mensaje apropiado
- [ ] **Solo venta** - Si solo hay ventas → Solo muestra segmento de venta
- [ ] **Solo alquiler** - Si solo hay alquileres → Solo muestra segmento de alquiler

**Verificaciones:**
- [ ] Los datos son reales (no de ejemplo)
- [ ] Los colores son correctos (púrpura para venta, verde para alquiler)
- [ ] Los porcentajes se calculan correctamente
- [ ] La gráfica se adapta si hay "Otras" operaciones
- [ ] No hay errores en consola

**Casos de prueba:**
- [ ] Dashboard con propiedades de venta y alquiler
- [ ] Dashboard solo con propiedades de venta
- [ ] Dashboard solo con propiedades de alquiler
- [ ] Dashboard sin propiedades

---

## 5. Testing de Sidebar Mobile

**Dispositivo:** Mobile o ventana < 1024px

**Checklist:**
- [ ] **Sidebar oculto por defecto** - No se ve al cargar la página
- [ ] **Hamburger menu visible** - Botón de menú aparece en header
- [ ] **Click en hamburger** - Abre sidebar desde la izquierda
- [ ] **Overlay aparece** - Fondo oscuro aparece cuando sidebar está abierto
- [ ] **Click en overlay** - Cierra sidebar
- [ ] **Tecla ESC** - Cierra sidebar
- [ ] **Click en enlace** - Cierra sidebar después de navegar
- [ ] **Cambio a desktop** - Al redimensionar a > 1024px → Sidebar se cierra automáticamente
- [ ] **Scroll del body** - Cuando sidebar está abierto → Body no hace scroll

**Verificaciones:**
- [ ] Las animaciones son suaves
- [ ] El overlay tiene opacidad correcta
- [ ] El sidebar tiene ancho correcto (320px o 85vw)
- [ ] No hay glitches visuales

---

## 6. Testing de Responsive

### 6.1. Desktop (> 1024px)

**Checklist:**
- [ ] Sidebar siempre visible
- [ ] Grid de 2-3 columnas funciona
- [ ] Cards tienen padding de 24px
- [ ] Tipografía tamaño desktop

---

### 6.2. Tablet (768px - 1023px)

**Checklist:**
- [ ] Sidebar oculto por defecto
- [ ] Grid se adapta a 2 columnas
- [ ] Cards tienen padding adecuado
- [ ] Tipografía se ajusta

---

### 6.3. Mobile (< 768px)

**Checklist:**
- [ ] Sidebar oculto por defecto
- [ ] Grid en 1 columna
- [ ] Cards tienen padding de 16px
- [ ] Botones tienen tamaño mínimo 44px
- [ ] Inputs tienen tamaño mínimo 44px
- [ ] Tipografía mobile (H1: 28px, H2: 22px)
- [ ] Tablas tienen scroll horizontal
- [ ] Columnas menos importantes ocultas

---

## 7. Testing de Accesibilidad

**Checklist:**
- [ ] **Navegación por teclado** - Tab funciona en todos los elementos
- [ ] **Focus visible** - Los elementos tienen outline visible al hacer focus
- [ ] **Contraste** - Los colores tienen suficiente contraste
- [ ] **Aria-labels** - Los botones tienen labels descriptivos
- [ ] **Alt text** - Las imágenes tienen texto alternativo
- [ ] **Lectores de pantalla** - La estructura es semántica

---

## 8. Testing de Performance

**Checklist:**
- [ ] **Carga inicial** - La página carga en < 3 segundos
- [ ] **Gráficas** - Las gráficas se renderizan sin lag
- [ ] **Búsqueda AJAX** - Las búsquedas responden en < 500ms
- [ ] **Navegación** - Los cambios de página son rápidos
- [ ] **Sin errores** - No hay errores en consola del navegador
- [ ] **Sin warnings** - No hay warnings en consola

**Herramientas recomendadas:**
- Chrome DevTools → Network tab
- Chrome DevTools → Performance tab
- Lighthouse (Chrome DevTools)

---

## 9. Testing de Navegadores

### 9.1. Chrome

**Checklist:**
- [ ] Todas las funcionalidades funcionan
- [ ] Las gráficas se renderizan correctamente
- [ ] Los estilos se aplican correctamente
- [ ] No hay errores en consola

---

### 9.2. Firefox

**Checklist:**
- [ ] Todas las funcionalidades funcionan
- [ ] Las gráficas se renderizan correctamente
- [ ] Los estilos se aplican correctamente
- [ ] No hay errores en consola

---

### 9.3. Safari

**Checklist:**
- [ ] Todas las funcionalidades funcionan
- [ ] Las gráficas se renderizan correctamente
- [ ] Los estilos se aplican correctamente
- [ ] No hay errores en consola
- [ ] Inputs no hacen zoom automático (font-size 16px)

---

### 9.4. Edge

**Checklist:**
- [ ] Todas las funcionalidades funcionan
- [ ] Las gráficas se renderizan correctamente
- [ ] Los estilos se aplican correctamente

---

## 10. Issues Encontrados

### Durante el Testing

**Issue 1:**
- **Descripción:**
- **Ubicación:**
- **Severidad:** (Alta/Media/Baja)
- **Screenshot:** (si aplica)

**Issue 2:**
- **Descripción:**
- **Ubicación:**
- **Severidad:**
- **Screenshot:**

---

## 11. Resumen del Testing

### ✅ Funcionalidades Probadas

- [ ] Enlaces: ___ / 20+ enlaces probados
- [ ] Formularios: ___ / 4 formularios probados
- [ ] Búsqueda global: ✅ / ❌ Funciona correctamente
- [ ] Gráficas: ___ / 2 gráficas probadas
- [ ] Sidebar mobile: ✅ / ❌ Funciona correctamente
- [ ] Responsive: ___ / 3 breakpoints probados

### 📊 Métricas

- **Tiempo de carga:** ___ segundos
- **Errores encontrados:** ___
- **Warnings encontrados:** ___
- **Navegadores probados:** ___

---

## 12. Notas Adicionales

**Observaciones:**
- 

**Recomendaciones:**
- 

**Próximos pasos:**
- 

---

**Testing realizado por:** ________________  
**Fecha:** ________________  
**Navegador usado:** ________________  
**Versión del navegador:** ________________
