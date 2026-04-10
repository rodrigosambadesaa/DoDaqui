# Base PHP con Docker

O repositorio queda reducido a unha base mínima para desenvolver a aplicación con arquivos PHP servidos por Apache dentro de Docker. O JavaScript irá cargado directamente desde as páxinas PHP mediante etiquetas script no head ou antes do peche de body usando defer ou async.

## Estrutura actual

- backend/: contedor web con PHP 8.2 + Apache.
- backend/public/: document root onde crear as páxinas .php.
- docker/mysql/: imaxe opcional de MySQL para entornos restrinxidos.
- docker-compose.yml: arranque normal con web + MySQL.
- docker-compose.restricted.yml: arranque alternativo sen bind mounts para MySQL.

## Arranque

Desde a raíz do proxecto:

```bash
docker compose up --build
```

Modo alternativo para equipos con restricións:

```bash
docker compose -f docker-compose.restricted.yml up --build
```

## Servizos

- web: PHP 8.2 + Apache en http://localhost:8080
- db: MySQL 8.0 en localhost:3306

Podes cambiar os portos temporalmente:

```bash
set WEB_PORT=8081
set DB_PORT=3307
docker compose up --build
```

## Onde empezar

- Crea as túas páxinas PHP en backend/public.
- Usa etiquetas script nas páxinas PHP para cargar JavaScript propio.
- Se necesitas esquema inicial, engádeo en docker/mysql/init.sql.

## Reinicio limpo

```bash
docker compose down -v
docker compose up --build
```

Para o modo restrinxido:

```bash
docker compose -f docker-compose.restricted.yml down -v
docker compose -f docker-compose.restricted.yml up --build
```

## Sobre a persoa autora

> *Tarefa*: Realiza unha breve descrición de quen es desde unha perspectiva profesional, os teus puntos fortes, tecnoloxías que máis dominas e o motivo de por que te decantaches por este proxecto. **Non máis de 200 palabras**. Indica unha forma fiable de contactar contigo no presente e no futuro.
# DoDaquí - Plataforma de E-Commerce Local

Sistema moderno de comercio electrónico para conectar productores locales con consumidores de su comunidad. Implementado en 10 commits progresivos (v0.1.0 → v1.0.0) con diseño responsivo y características completas de carrito y checkout.

## 🚀 Inicio Rápido

### Opción 1: Servidor PHP Local (Recomendado para Desarrollo)

```bash
cd backend/public
php -S localhost:8080
```

Accede a:
- **Home:** http://localhost:8080/home.php
- **Carrito:** http://localhost:8080/cart.php
- **Checkout:** http://localhost:8080/checkout.php
- **Test:** http://localhost:8080/test.php

### Opción 2: Docker Compose

```bash
docker compose up --build
```

Accede a:
- **Web:** http://localhost:8080
- **MySQL:** localhost:3306

## 📋 Requisitos

### Para Desarrollo Local (PHP)
- PHP 8.2+ (Instalado: PHP 8.4.8 ✅)
- Navegador moderno

### Para Docker
- Docker Desktop
- Docker Compose

## 🗂️ Estructura del Proyecto

```
backend/
├── public/
│   ├── home.php           # Landing page principal
│   ├── cart.php           # Página de carrito
│   ├── checkout.php       # Página de pago
│   ├── test.php           # Página de test
│   ├── bootstrap.php      # Configuración
│   └── assets/
│       ├── styles.css     # Estilos (956 líneas)
│       └── app.js         # JavaScript (240 líneas)
├── Dockerfile             # Imagen Docker PHP
└── docker/
	└── mysql/
		├── Dockerfile     # Imagen MySQL 8.0
		└── init.sql       # Script de inicialización
docker-compose.yml         # Orquestación de servicios
```

## 🎨 Características Principales

### Página Principal (Home)
- ✅ Navbar sticky con logo y navegación
- ✅ Sección hero con badge "Support Local Craftsmen & Farmers"
- ✅ Grid de 4 productos destacados
- ✅ 3 tarjetas informativas (Calidad, Sostenibilidad, Comunidad)
- ✅ Footer completo con redes sociales y newsletter

### Carrito de Compras
- ✅ Lista dinámica de productos
- ✅ Cálculo automático de totales
- ✅ Resumen de pedido en sidebar
- ✅ Integración con localStorage

### Checkout
- ✅ Formulario de 3 pasos (Dirección, Pago, Confirmación)
- ✅ Métodos de pago (Tarjeta y PayPal)
- ✅ Validación de formularios en cliente
- ✅ Modal de confirmación

## 💻 Tecnologías

- **Frontend:** HTML5 semántico, CSS3 (Grid, Flexbox), JavaScript Vanilla
- **Backend:** PHP 8.4
- **Base de Datos:** MySQL 8.0
- **Containerización:** Docker + Docker Compose
- **Gestión de Estado:** localStorage
- **Herramientas:** Git, Composer

## 🎯 Versión Actual

- **Versión:** v1.0.0 ✅
- **Estado:** Production Ready
- **Commits:** 10 versiones progresivas
- **Líneas de Código:** 2,500+

## 📚 Documentación

- [DESIGN.md](./DESIGN.md) - Guía de diseño y arquitectura
- [CHANGELOG.md](./CHANGELOG.md) - Historial de 10 commits
- [SUMMARY.md](./SUMMARY.md) - Resumen ejecutivo del proyecto
- [doc/api_reference.md](./doc/api_reference.md) - Referencia de API

## 📱 Responsive Design

- ✅ Desktop (1400px+)
- ✅ Tablet (768px - 1399px)
- ✅ Mobile (480px - 767px)

## 🔧 Configuración

### Variables de Entorno

Crea un archivo `.env` en la raíz:

```env
DB_HOST=localhost
DB_NAME=dodaqui
DB_USER=root
DB_PASS=password
DB_PORT=3306
WEB_PORT=8080
```

### Uso con Docker

```bash
# Iniciar servicios
docker compose up --build

# Ver logs
docker compose logs -f

# Parar servicios
docker compose down

# Limpiar volúmenes (reinicio completo)
docker compose down -v
docker compose up --build
```

### Uso con PHP Local

```bash
# Verificar versión PHP
php --version

# Iniciar servidor (en backend/public)
php -S localhost:8080

# Testear servidor
curl http://localhost:8080/test.php
```

## 🚀 Historia de Versiones

| Versión | Fecha | Descripción |
|---------|-------|-------------|
| v1.0.0  | 2026  | Lanzamiento final con todas las features |
| v0.9.0  | 2026  | Diseño responsivo y animaciones |
| v0.8.0  | 2026  | Resumen de pedido |
| v0.7.0  | 2026  | Formulario de pago |
| v0.6.0  | 2026  | Página de carrito |
| v0.5.0  | 2026  | Footer |
| v0.4.0  | 2026  | Tarjetas informativas |
| v0.3.0  | 2026  | Grid de productos |
| v0.2.0  | 2026  | Sección hero |
| v0.1.0  | 2026  | Navbar inicial |

## 📧 Soporte y Contacto

Para reportar problemas o sugerencias, contacta con el equipo de desarrollo.

## 📄 Licencia

MIT License - Libre para usar, modificar y distribuir

## ✨ Próximas Mejoras

- [ ] Backend API completo
- [ ] Sistema de autenticación
- [ ] Integración de pagos real (Stripe, PayPal)
- [ ] Admin panel
- [ ] Búsqueda y filtros avanzados
- [ ] Sistema de reseñas
- [ ] Wishlist

---

**¿Listo para empezar?**

```bash
# Opción rápida: PHP local
cd backend/public && php -S localhost:8080

# Opción con Docker
docker compose up --build
```

Luego abre http://localhost:8080/home.php en tu navegador 🎉
