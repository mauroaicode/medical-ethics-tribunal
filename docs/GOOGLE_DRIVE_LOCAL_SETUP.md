# 🏠 Guía Rápida: Autenticación Google Drive en Local

Esta guía te ayudará a configurar la autenticación con Google Drive en tu entorno de desarrollo local.

## ⚡ Configuración Rápida (5 minutos)

### 1. **Configurar en Google Cloud Console**

1. Ve a [Google Cloud Console](https://console.cloud.google.com/)
2. Selecciona tu proyecto o crea uno nuevo
3. **Habilita las APIs:**
   - Ve a "APIs y servicios" → "Biblioteca"
   - Busca y habilita: **Google Drive API**
   - Busca y habilita: **Google Docs API**

4. **Crear Credenciales OAuth 2.0:**
   - Ve a "APIs y servicios" → "Credenciales"
   - Click en "+ CREAR CREDENCIALES" → "ID de cliente OAuth 2.0"
   - Selecciona "Aplicación web"
   - **Configuración:**
     - **Nombre**: "Medical Ethics Tribunal Local"
     - **URI de redirección autorizada**: Agrega estas URIs:
       ```
       http://medical-ethics-tribunal.test/api/admin/templates/auth/callback
       http://localhost:8000/api/admin/templates/auth/callback
       http://127.0.0.1:8000/api/admin/templates/auth/callback
       ```
   - Click "Crear"
   - **Copia el `Client ID` y `Client secret`**

### 2. **Configurar ngrok (Necesario porque Google no acepta `.test`)**

Google **NO acepta** dominios `.test` como URIs de redirección. Necesitas usar ngrok:

#### **2.1. Iniciar ngrok:**

```bash
ngrok http medical-ethics-tribunal.test --host-header=rewrite
```

Copia la URL HTTPS que ngrok te da (ejemplo: `https://abc123.ngrok-free.app`)

#### **2.2. Configurar el archivo `.env`**

Abre tu archivo `.env` y agrega:

```env
# Google Drive API
GOOGLE_CLIENT_ID=tu-client-id-aqui.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=tu-client-secret-aqui
GOOGLE_REDIRECT_URI=https://TU-URL-NGROK.ngrok-free.app/api/admin/templates/auth/callback
```

**⚠️ Importante:** 
- Usa la URL **HTTPS** de ngrok (no HTTP)
- La ruta debe terminar en `/auth/callback` (no `/auth/calll` con 3 L's)
- Cada vez que reinicies ngrok, tendrás una nueva URL y debes actualizar esto

**Alternativa:** Si no quieres usar ngrok, puedes usar `localhost`:
```env
GOOGLE_REDIRECT_URI=http://localhost:8080/api/admin/templates/auth/callback
```

### 3. **Ejecutar la Migración**

```bash
php artisan migrate
```

### 4. **Autenticarse**

#### Opción A: Usando Postman/Insomnia

1. **Obtener URL de autorización:**
   ```http
   GET http://medical-ethics-tribunal.test/api/admin/templates/auth/url
   Authorization: Bearer {tu_token_sanctum}
   ```

2. **Respuesta:**
   ```json
   {
     "auth_url": "https://accounts.google.com/o/oauth2/v2/auth?..."
   }
   ```

3. **Copiar y abrir la URL:**
   - Copia el `auth_url`
   - Pégalo en tu navegador
   - Inicia sesión con Google
   - Autoriza los permisos

4. **¡Listo!** Google redirigirá automáticamente y se guardará el token.

#### Opción B: Usando cURL

```bash
# 1. Obtener URL de autorización
curl -X GET "http://medical-ethics-tribunal.test/api/admin/templates/auth/url" \
  -H "Authorization: Bearer TU_TOKEN_AQUI"

# 2. La respuesta tendrá el auth_url, ábrelo en el navegador
```

### 5. **Verificar Autenticación**

El token se guarda automáticamente en:
```
storage/app/google-drive-token.json
```

Si este archivo existe y tiene contenido, estás autenticado ✅

### 6. **Sincronizar Plantillas**

```bash
curl -X POST "http://medical-ethics-tribunal.test/api/admin/templates/sync" \
  -H "Authorization: Bearer TU_TOKEN_AQUI" \
  -H "Content-Type: application/json" \
  -d '{"folder_name": "Plantillas Tribunal Ética Médica"}'
```

## ⚠️ Advertencia: "Google no ha verificado esta aplicación"

Cuando veas la pantalla de advertencia de Google, es **normal en desarrollo**. Tienes dos opciones:

### **Opción 1: Continuar de todas formas (Rápido)**

1. Haz clic en **"Configuración avanzada"** (abajo a la izquierda)
2. Aparecerá un enlace **"Continuar a [nombre de tu app] (no seguro)"**
3. Haz clic en ese enlace para continuar
4. Autoriza los permisos normalmente

✅ **Esto es seguro si eres el desarrollador y confías en tu propia aplicación.**

### **Opción 2: Agregar usuarios de prueba (Recomendado)**

1. Ve a [Google Cloud Console](https://console.cloud.google.com/)
2. **APIs y servicios** → **Pantalla de consentimiento de OAuth**
3. Verifica que el modo sea **"Pruebas"** (no "Producción")
4. En la sección **"Usuarios de prueba"**, haz clic en **"+ AGREGAR USUARIOS"**
5. Agrega tu email: `mauriciotribunaldeeticamedica@gmail.com`
6. Guarda los cambios
7. Espera unos minutos y vuelve a intentar la autenticación

✅ **Con esto, no verás la advertencia con tu cuenta de prueba.**

## 🔧 Solución de Problemas

### Error: "redirect_uri_mismatch"

**Problema:** La URI de redirección no coincide.

**Solución:**
1. Verifica que la URI en `.env` coincida EXACTAMENTE con la configurada en Google Cloud Console
2. Asegúrate de incluir `http://` o `https://`
3. No olvides la ruta completa: `/api/admin/templates/auth/callback`

### Error: "invalid_client"

**Problema:** Client ID o Secret incorrectos.

**Solución:**
1. Verifica que copiaste correctamente el Client ID y Secret
2. Asegúrate de que no haya espacios extra en el `.env`
3. Limpia la caché: `php artisan config:clear`

### Google no redirige después de autorizar

**Problema:** El servidor local no es accesible desde internet.

**Solución:**
1. Verifica que tu servidor está corriendo
2. Verifica que la ruta `/api/admin/templates/auth/callback` existe
3. Usa la opción manual: copia el código de la URL y envíalo por POST

### El token expira frecuentemente

**Problema:** No se está guardando el refresh token.

**Solución:**
1. Verifica permisos de escritura en `storage/app/`
2. Verifica que el archivo `google-drive-token.json` se crea después de autenticar
3. Asegúrate de que `access_type=offline` está configurado en `config/services.php`

## 📝 Verificar Configuración

Puedes verificar tu configuración ejecutando:

```bash
php artisan tinker
```

Y luego:
```php
config('services.google.client_id');
config('services.google.client_secret');
config('services.google.redirect');
```

Todos deben mostrar los valores correctos (no `null`).

## ✅ Checklist Rápido

- [ ] APIs habilitadas en Google Cloud Console (Drive y Docs)
- [ ] Credenciales OAuth 2.0 creadas
- [ ] URIs de redirección agregadas en Google Cloud Console
- [ ] Variables en `.env` configuradas correctamente
- [ ] Migración ejecutada
- [ ] Servidor local corriendo
- [ ] Autenticación exitosa (archivo `google-drive-token.json` existe)

## 🎯 Siguiente Paso

Una vez autenticado, puedes:
1. Sincronizar plantillas desde Google Drive
2. Asignar plantillas a procesos
3. Generar documentos automáticamente

¡Listo para usar! 🚀

