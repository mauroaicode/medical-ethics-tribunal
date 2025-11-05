# Guía de Autenticación con Google Drive

## 📋 Resumen

Este documento explica cómo autenticar tu aplicación con Google Drive API para sincronizar y gestionar plantillas.

## 🔐 Paso a Paso

### **Paso 1: Configurar en Google Cloud Console**

1. Ve a [Google Cloud Console](https://console.cloud.google.com/)
2. Crea un nuevo proyecto o selecciona uno existente
3. Habilita las siguientes APIs:
   - **Google Drive API**
   - **Google Docs API**
4. Ve a "Credenciales" → "Crear credenciales" → "ID de cliente OAuth 2.0"
5. Configura:
   - **Tipo de aplicación**: Aplicación web
   - **URI de redirección autorizada**: `http://tu-dominio.com/api/admin/templates/auth/callback`
6. Descarga el `client_id` y `client_secret`

### **Paso 2: Configurar Variables de Entorno**

#### **Para Desarrollo Local:**

Agrega al archivo `.env`:

```env
GOOGLE_CLIENT_ID=tu-client-id-aqui.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=tu-client-secret-aqui
GOOGLE_REDIRECT_URI=http://medical-ethics-tribunal.test/api/admin/templates/auth/callback
```

**O si usas Laravel Sail o otro servidor local:**

```env
GOOGLE_REDIRECT_URI=http://localhost:8000/api/admin/templates/auth/callback
```

#### **Para Producción:**

```env
GOOGLE_REDIRECT_URI=https://tu-dominio.com/api/admin/templates/auth/callback
```

### **⚠️ Configuración en Google Cloud Console para Local**

**IMPORTANTE:** En Google Cloud Console, agrega **AMBAS** URIs de redirección:

1. **Para desarrollo local:**
   - `http://medical-ethics-tribunal.test/api/admin/templates/auth/callback`
   - O `http://localhost:8000/api/admin/templates/auth/callback` (si usas php artisan serve)

2. **Para producción:**
   - `https://tu-dominio.com/api/admin/templates/auth/callback`

**Puedes agregar múltiples URIs de redirección** en la configuración de OAuth 2.0 en Google Cloud Console.

### **Paso 3: Autenticación (3 formas)**

#### **Opción A: Flujo Automático (Recomendado)**

1. **Obtener URL de autorización:**
   ```http
   GET /api/admin/templates/auth/url
   Authorization: Bearer {tu_token}
   ```

2. **Respuesta:**
   ```json
   {
     "auth_url": "https://accounts.google.com/o/oauth2/v2/auth?..."
   }
   ```

3. **Abrir la URL en el navegador:**
   - Copia el `auth_url` de la respuesta
   - Ábrelo en tu navegador
   - Inicia sesión con tu cuenta de Google
   - Autoriza los permisos solicitados

4. **Google redirige automáticamente:**
   - Google redirigirá a: `http://tu-dominio.com/api/admin/templates/auth/callback?code=4/0AX4XfWh...`
   - El sistema procesará el código automáticamente
   - Verás un mensaje de éxito

✅ **Listo!** Ya estás autenticado.

#### **Opción B: Flujo Manual (Desde código)**

1. **Obtener URL de autorización:**
   ```http
   GET /api/admin/templates/auth/url
   ```

2. **Abrir URL y copiar código:**
   - Abre la URL en el navegador
   - Después de autorizar, Google te redirige
   - Copia el código del parámetro `code` en la URL

3. **Enviar código manualmente:**
   ```http
   POST /api/admin/templates/auth/callback
   Content-Type: application/json
   
   {
     "code": "4/0AX4XfWh..."
   }
   ```

#### **Opción C: Desde Frontend (JavaScript/Vue/React)**

```javascript
// 1. Obtener URL de autorización
const response = await fetch('/api/admin/templates/auth/url', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});
const { auth_url } = await response.json();

// 2. Abrir ventana de autorización
const authWindow = window.open(
  auth_url,
  'Google Auth',
  'width=500,height=600'
);

// 3. Escuchar el callback (puedes usar postMessage o polling)
window.addEventListener('message', (event) => {
  if (event.data.type === 'google-auth-success') {
    console.log('Autenticación exitosa!');
    authWindow.close();
  }
});
```

### **Paso 4: Sincronizar Plantillas**

Una vez autenticado, puedes sincronizar las plantillas desde Google Drive:

```http
POST /api/admin/templates/sync
Authorization: Bearer {tu_token}
Content-Type: application/json

{
  "folder_name": "Plantillas Tribunal Ética Médica"
}
```

**Respuesta:**
```json
{
  "message": "Se sincronizaron 3 plantillas exitosamente.",
  "templates": [...]
}
```

### **Paso 5: Asignar Plantilla a Proceso**

```http
POST /api/admin/templates/{template_id}/assign-to-process
Authorization: Bearer {tu_token}
Content-Type: application/json

{
  "process_id": 1,
  "destination_folder_name": "Procesos Generados" // Opcional
}
```

**Respuesta:**
```json
{
  "message": "Plantilla asignada al proceso y documento generado exitosamente.",
  "document": {
    "google_drive_file_id": "1abc...",
    "file_name": "Proceso_001_Demanda.docx",
    "local_path": "processes/documents/..."
  }
}
```

## 🔄 Refresh Token

El sistema guarda automáticamente el **refresh token** en:
- Archivo: `storage/app/google-drive-token.json`

Esto significa que **NO necesitas autenticarte nuevamente** cada vez que uses la API, solo la primera vez.

## ❓ Preguntas Frecuentes

**Q: ¿Qué pasa si el token expira?**  
A: El sistema usa el refresh token automáticamente para obtener un nuevo access token.

**Q: ¿Puedo usar múltiples cuentas de Google?**  
A: Por ahora, el sistema usa una sola cuenta. El token se guarda globalmente.

**Q: ¿Dónde se guardan los documentos generados?**  
A: Pueden guardarse en:
- Google Drive (carpeta especificada en `destination_folder_name`)
- AWS S3 (si está configurado)
- Local storage (`storage/app/private/processes/documents/`)

## 📝 Placeholders Disponibles

Al asignar una plantilla a un proceso, estos placeholders se reemplazan automáticamente:

```
{{process_number}}
{{process_name}}
{{process_date}}
{{complainant_name}}
{{doctor_name}}
{{magistrate_instructor_name}}
... (y muchos más)
```

Ver el código de `TemplateProcessorService` para la lista completa.

