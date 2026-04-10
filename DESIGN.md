# DoDaquí - E-Commerce Platform

Una plataforma de e-commerce moderna diseñada para conectar a productores locales con consumidores de su comunidad. DoDaquí es un marketplace elegante y responsivo que celebra los productos artesanales y agrícolas locales.

## Características

### Páginas Principales

- **Home (home.php)** - Landing page con hero section, featured products, y información de valor
- **Cart (cart.php)** - Carrito de compras con resumen de pedidos
- **Checkout (checkout.php)** - Proceso de pago con múltiples métodos de pago

### Diseño

- Interfaz moderna y limpia con tipografía elegante
- Paleta de colores: Negro primario (#1a1a1a), Marrón secundario (#c0a080)
- Responsive design: Desktop, Tablet (768px), Mobile (480px)
- Animaciones suaves y transiciones
- Efectos hover en elementos interactivos

### Funcionalidades

- Sistema de navegación persistente
- Carrito de compras con localStorage
- Múltiples métodos de pago (Tarjeta de crédito, PayPal)
- Formulario de dirección de envío
- Resumen de pedido dinámico
- Notificaciones tipo toast
- Modal de detalle de producto

## Estructura de Archivos

```
backend/public/
├── home.php              # Página principal
├── cart.php              # Página de carrito
├── checkout.php          # Página de checkout
├── assets/
│   ├── styles.css        # Estilos globales
│   └── app.js            # JavaScript principal
└── api/
    └── (rutas de API)
```

## Estilos CSS

### Variables de Diseño

```css
--primary: #1a1a1a              /* Color principal (negro) */
--secondary: #c0a080            /* Color secundario (marrón) */
--gray-light: #f5f5f5           /* Gris claro */
--text-dark: #1e1e1e            /* Texto oscuro */
--text-light: #666              /* Texto claro */
```

### Componentes

- **Navbar** - Navegación sticky con logo, menú y carrito
- **Hero** - Sección promocional con badge y CTA
- **Product Card** - Tarjeta de producto con imagen, precio y acciones
- **Info Card** - Tarjeta informativa con icono y descripción
- **Footer** - Pie de página con enlaces y formulario newsletter
- **Checkout** - Formulario de pago con pasos numerados

## JavaScript (app.js)

### Funcionalidades

- Gestión de carrito con localStorage
- Añadir/eliminar productos del carrito
- Modal de detalle de producto
- Notificaciones toast
- Validación de formularios

## Responsividad

### Breakpoints

- **Desktop**: 1400px (máximo ancho de contenedor)
- **Tablet**: 768px y menos
- **Mobile**: 480px y menos

Cada breakpoint ajusta:
- Tamaño de fuentes
- Espaciado y padding
- Layouts de grid
- Visibilidad de elementos

## Commits Realizados

1. **v0.1.0** - Setup navbar con logo y elementos básicos
2. **v0.2.0** - Sección hero con banner promocional
3. **v0.3.0** - Grid de productos destacados
4. **v0.4.0** - Tarjetas con iconos informativos
5. **v0.5.0** - Footer con información y redes
6. **v0.6.0** - Página de carrito completa
7. **v0.7.0** - Formulario de pago con métodos
8. **v0.8.0** - Resumen de pedido y estilos mejorados
9. **v0.9.0** - Datos de envío y responsive design
10. **v1.0.0** - Polish final y release

## Cómo Usar

### Desarrollo Local

```bash
# Con Docker
docker compose up --build

# Sin Docker (requiere PHP 8.2+)
php -S localhost:8080 -t backend/public
```

### Acceso

- Home: http://localhost:8080/home.php
- Cart: http://localhost:8080/cart.php
- Checkout: http://localhost:8080/checkout.php

## Tecnologías

- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Backend**: PHP 8.2
- **Base de Datos**: MySQL 8.0
- **Containerización**: Docker

## Próximas Mejoras

- [ ] Integración de base de datos real
- [ ] Sistema de autenticación completo
- [ ] Procesamiento de pagos real
- [ ] Gestión de inventario
- [ ] Sistema de comentarios y reseñas
- [ ] Búsqueda y filtros avanzados
- [ ] Wishlist de productos
- [ ] Seguimiento de pedidos

## Autor

DoDaquí Development Team

## Licencia

MIT License - Ver LICENSE.md para detalles
