# Despregamento en Internet (Vercel)

## Plataforma elixida

Para este proxecto, a plataforma de despregamento real é **Vercel**:

- Permite despregamento continuo desde repositorio (GitHub/GitLab).
- Encaixa coa configuración do proxecto en `vercel.json`.
- Facilita publicación rápida e redeploy sen administrar infraestrutura propia.

## Estado actual

- Configuración de despregamento activa en `vercel.json`.
- Fluxo de variables de base de datos documentado e automatizado en `scripts/vercel/configure-db-env.sh`.
- Despregamento operativo en Vercel.

## Pasos para publicar

1. Conectar o repositorio do proxecto a Vercel (se non está xa conectado).
2. Configurar variables de contorno en Vercel para **Production**:

- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

3. Opcional: cargar variables desde terminal co script incluído no proxecto:

```bash
export DB_HOST='...'
export DB_PORT='3306'
export DB_DATABASE='...'
export DB_USERNAME='...'
export DB_PASSWORD='...'
./scripts/vercel/configure-db-env.sh
```

4. Lanzar redeploy desde o panel de Vercel ou mediante un novo push á rama de produción.
5. Verificar funcionamento:

- `GET /` carga da portada.
- `GET /auth.php` carga de login/rexistro.
- Rexistro e login funcionando.
- Compra de proba en `checkout.php`.

## Post-despregamento recomendado

- Configurar dominio propio (ou subdominio temporal).
- Activar HTTPS obrigatorio.
- Crear issue en GitLab co enlace da URL pública de Vercel e capturas das probas.
