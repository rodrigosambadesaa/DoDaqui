# Despregamento en Internet (DigitalOcean App Platform)

## Plataforma elixida

Para este proxecto (PHP + Apache en Docker + MySQL), a opción máis equilibrada é **DigitalOcean App Platform** con base de datos xestionada de MySQL:

- Permite despregar directamente desde repositorio.
- Encaixa ben co `Dockerfile` xa existente no proxecto.
- Evita administrar manualmente unha VM completa para o primeiro despregamento público.

## Estado actual

- Configuración de despregamento creada en `.do/app.yaml`.
- Proba técnica de CLI feita con `doctl` (en contedor Docker).
- Bloqueo actual para despregar: falta autenticación de conta (`doctl auth init` con token persoal).

## Pasos para publicar

1. Crear un token en DigitalOcean (`API -> Personal Access Tokens`).
2. Autenticar `doctl`:

```bash
docker run --rm -it -v "$HOME/.config/doctl:/root/.config/doctl" digitalocean/doctl auth init
```

3. Crear a app desde a especificación:

```bash
cd /ruta/do/repositorio
docker run --rm -it \
  -v "$HOME/.config/doctl:/root/.config/doctl" \
  -v "$PWD":/workspace -w /workspace \
  digitalocean/doctl apps create --spec .do/app.yaml
```

4. Anotar a URL pública devolta por DigitalOcean.
5. Verificar funcionamento:

- `GET /` carga da portada.
- `GET /auth.php` carga de login/rexistro.
- Rexistro e login funcionando.
- Compra de proba en `checkout.php`.

## Post-despregamento recomendado

- Configurar dominio propio (ou subdominio temporal).
- Activar HTTPS obrigatorio.
- Crear issue en GitLab co enlace da URL pública e capturas das probas.
