# 🚀 Configurar ngrok para Google Drive en Desarrollo Local

## Problema

Google no acepta dominios `.test` como URIs de redirección válidas. Necesitas usar ngrok para exponer tu servidor local.

## Solución Rápida

### 1. Instalar ngrok (si no lo tienes)

```bash
# macOS
brew install ngrok

# O descarga desde https://ngrok.com/download
```

### 2. Configurar ngrok

Ejecuta ngrok apuntando a tu dominio de Herd:

```bash
ngrok http medical-ethics-tribunal.test --host-header=rewrite
```

**O si Herd usa otro puerto:**

```bash
# Para puerto 80
ngrok http 80 --host-header=medical-ethics-tribunal.test

# Para puerto específico (ej: 8080)
ngrok http 8080 --host-header=medical-ethics-tribunal.test
```

### 3. Copiar la URL de ngrok

ngrok te dará una URL como:
```
Forwarding: https://abc123.ngrok-free.app -> http://localhost:80
```

**Copia la URL HTTPS** (la que empieza con `https://`)

### 4. Actualizar `.env`

```env
GOOGLE_CLIENT_ID=226859702011-hvd0rostls77gp1lvri4o3rd1276en43.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=tu-client-secret
GOOGLE_REDIRECT_URI=https://abc123.ngrok-free.app/api/admin/templates/auth/callback
```

### 5. Actualizar Google Cloud Console

1. Ve a [Google Cloud Console](https://console.cloud.google.com/)
2. **APIs y servicios** → **Credenciales**
3. Haz clic en tu OAuth 2.0 Client ID
4. En **"URI de redirección autorizada"**, agrega:
   ```
   https://abc123.ngrok-free.app/api/admin/templates/auth/callback
   ```
   ⚠️ **Importante:** Asegúrate de que sea `/auth/callback` (no `/auth/calll` con 3 L's)
5. Guarda los cambios

### 6. Probar

1. Asegúrate de que ngrok esté corriendo
2. Obtén la URL de autorización:
   ```bash
   curl -X GET "http://medical-ethics-tribunal.test/api/admin/templates/auth/url" \
     -H "Authorization: Bearer TU_TOKEN"
   ```
3. Abre el `auth_url` en el navegador
4. Autoriza con Google
5. Google redirigirá correctamente a través de ngrok

## ⚠️ Notas Importantes

- **La URL de ngrok cambia cada vez** que lo reinicias (en el plan gratuito)
- Cada vez que cambies la URL de ngrok, debes actualizar:
  1. El archivo `.env`
  2. Google Cloud Console
  3. Reiniciar el servidor Laravel: `php artisan config:clear`

## 🔄 Workflow Recomendado

1. Inicia ngrok primero
2. Copia la URL de ngrok
3. Actualiza `.env` con la nueva URL
4. Actualiza Google Cloud Console
5. Limpia la caché: `php artisan config:clear`
6. Prueba la autenticación

## 🎯 Alternativa: Usar localhost

Si no quieres lidiar con ngrok cambiando, puedes usar `localhost`:

```env
GOOGLE_REDIRECT_URI=http://localhost:8080/api/admin/templates/auth/callback
```

Asegúrate de configurar Herd para que responda también en `localhost:8080` o el puerto que uses.

