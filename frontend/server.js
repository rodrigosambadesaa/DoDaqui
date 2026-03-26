const express = require('express');
const path = require('path');

// Servidor web mínimo para entregar archivos estáticos del frontend.
const app = express();
// Puerto configurable para adaptarse a docker compose normal o restringido.
const port = process.env.PORT || 3000;
// URL base de la API inyectada en tiempo de ejecución.
const apiBaseUrl = process.env.API_BASE_URL || 'http://localhost:8080/api';

// Publica todo el contenido de frontend/public.
app.use(express.static(path.join(__dirname, 'public')));

// Endpoint dinámico para exponer configuración sin hardcodearla en el JS cliente.
app.get('/config.js', (_req, res) => {
    res.type('application/javascript');
    res.send(`window.APP_CONFIG = { API_BASE_URL: '${apiBaseUrl}' };`);
});

// Arranque final del servidor HTTP.
app.listen(port, () => {
    console.log(`Frontend listo en http://localhost:${port}`);
});
