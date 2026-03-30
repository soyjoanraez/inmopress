# 📝 Guía del Módulo SEO - WP Total Optimizer

## ✅ Funcionalidades incluidas

1. **Meta Boxes en el Editor**
   - Título SEO (máx. 70 caracteres)
   - Meta Descripción (máx. 160 caracteres)
   - Keywords
   - Palabra clave focus
   - Contadores y estados recomendados

2. **Vista Previa SERP en tiempo real**
   - Vista Desktop y Mobile
   - Actualización en vivo mientras escribes
   - Truncado automático según vista

3. **Análisis SEO básico**
   - Longitud de título y descripción
   - Focus keyword definida
   - Focus keyword en el título

4. **Panel de Encabezados (H1-H6)**
   - Detecta estructura de encabezados del contenido
   - Alertas por múltiples H1 o saltos de nivel

5. **Edición Masiva de SEO**
   - Tabla con posts/páginas
   - Edición inline de título, descripción, keywords y focus
   - Generación automática básica (título del post + extracto)
   - Guardado masivo con sincronización

6. **Compatibilidad con Rank Math / Yoast**
   - Lectura y escritura sincronizada de campos clave
   - No duplica datos

7. **Schema Markup automático**
   - Genera JSON-LD según el tipo de contenido

8. **Sitemap XML**
   - Genera `sitemap.xml`
   - Ping automático (si está activado)

9. **Robots.txt personalizado**
   - Permite definir reglas y añade el sitemap

---

## 🚀 Cómo usar el módulo

### 1) Editar SEO de un post/página
1. Abre el post/página.
2. Baja al meta box **WP Total Optimizer - SEO**.
3. Completa título, descripción, keywords y focus.
4. Revisa la vista previa SERP y el análisis.
5. Guarda/actualiza el contenido.

### 2) Panel de encabezados
- En el editor verás el panel de **Estructura de Encabezados (H1-H6)**.
- Úsalo para validar jerarquía H1/H2/H3.

### 3) Edición masiva
1. Ve a **Total Optimizer → Edición Masiva SEO**.
2. Filtra por tipo, estado y completitud.
3. Edita inline o usa la generación automática.
4. Guarda todos los cambios.

---

## 🔄 Sincronización con Rank Math / Yoast

Se sincronizan automáticamente:

```
_wpto_seo_title        ↔ rank_math_title / _yoast_wpseo_title
_wpto_seo_description  ↔ rank_math_description / _yoast_wpseo_metadesc
_wpto_focus_keyword    ↔ rank_math_focus_keyword / _yoast_wpseo_focuskw
```

---

## 🧾 Campos y recomendaciones

| Campo | Recomendado |
|------|-------------|
| Título SEO | 50–60 caracteres |
| Meta Descripción | 150–160 caracteres |
| Keywords | 5–7 máximo |
| Focus Keyword | 1–3 palabras |

---

## ℹ️ Notas
- Si tienes Rank Math/Yoast activo, el módulo se sincroniza automáticamente.
- El sitemap y robots se exponen desde WordPress sin plugins extra.
