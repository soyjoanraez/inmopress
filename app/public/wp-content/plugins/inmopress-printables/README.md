# Inmopress Printables Plugin

Plugin para generar plantillas imprimibles de inmuebles.

## Funcionalidad

- Añade un submenú "Plantillas Imprimibles" en el menú de Inmuebles
- Genera una página de impresión para cada inmueble accesible en `/print-property/?id={property_id}`

## Uso

1. Crear una página en WordPress con slug `print-property`
2. Configurar la página con template Full Width y sin header/footer en Astra
3. El plugin interceptará las visitas a esta página y mostrará el template de impresión

## Template de Impresión

El template muestra:
- Título y referencia del inmueble
- Imagen destacada
- Detalles principales (operación, precio, ciudad, dirección, superficie, dormitorios, baños)
- Descripción completa


