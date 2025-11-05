# Placeholders de Plantillas - Sistema de Tribunales de Ética Médica

## Introducción

Este documento describe todos los placeholders disponibles que pueden ser utilizados en las plantillas de documentos del sistema. Los placeholders son marcadores especiales que se reemplazan automáticamente con los datos del proceso al generar un documento.

## Formato de Uso

Los placeholders deben escribirse usando el formato `{{nombre_del_placeholder}}` (con triple llaves). Por ejemplo: `{{process_number}}` o `{{complainant_name}}`.

## Lista de Placeholders Disponibles

### 📋 Datos del Proceso

#### `{{process_number}}`
- **Descripción**: Número consecutivo único del proceso asignado automáticamente por el sistema.
- **Ejemplo**: `PRO-0001`, `PRO-0023`
- **Formato**: Texto alfanumérico

#### `{{process_name}}`
- **Descripción**: Nombre o título descriptivo del proceso jurídico.
- **Ejemplo**: `Demanda por mala práctica médica`, `Proceso por negligencia médica`
- **Formato**: Texto

#### `{{process_date}}`
- **Descripción**: Fecha de inicio o radicación del proceso en formato año-mes-día.
- **Ejemplo**: `2024-01-15`, `2024-12-31`
- **Formato**: Fecha (YYYY-MM-DD)

#### `{{process_description}}`
- **Descripción**: Descripción detallada del caso o proceso jurídico.
- **Ejemplo**: `El quejoso alega que el médico actuó con negligencia...`
- **Formato**: Texto largo (puede contener múltiples líneas)

#### `{{process_status}}`
- **Descripción**: Estado actual del proceso en formato legible.
- **Valores posibles**: 
  - `Pendiente` (cuando el proceso está pendiente)
  - `En Curso` (cuando el proceso está en progreso)
  - `Cerrado` (cuando el proceso está cerrado)
- **Formato**: Texto legible traducido

---

### 👤 Datos del Quejoso/Demandante

#### `{{complainant_name}}`
- **Descripción**: Nombre completo del quejoso (nombre y apellido).
- **Ejemplo**: `Juan Pérez`, `María González`
- **Formato**: Texto
- **Nota**: Si el quejoso es anónimo, retornará `N/A`

#### `{{complainant_document_type}}`
- **Descripción**: Tipo de documento de identidad del quejoso.
- **Valores posibles**: 
  - `Cédula de Ciudadanía`
  - `Cédula de Extranjería`
- **Formato**: Texto legible
- **Nota**: Si no hay datos del usuario, retornará `N/A`

#### `{{complainant_document_number}}`
- **Descripción**: Número de documento de identidad del quejoso.
- **Ejemplo**: `1234567890`, `987654321`
- **Formato**: Número de texto
- **Nota**: Si no hay datos del usuario, retornará `N/A`

#### `{{complainant_address}}`
- **Descripción**: Dirección de residencia del quejoso.
- **Ejemplo**: `Calle 123 #45-67`, `Avenida Principal 890`
- **Formato**: Texto
- **Nota**: Si no hay datos del usuario, retornará `N/A`

#### `{{complainant_city}}`
- **Descripción**: Ciudad de residencia del quejoso.
- **Ejemplo**: `Bogotá`, `Medellín`, `Cali`
- **Formato**: Texto
- **Nota**: Si no hay ciudad registrada, retornará `N/A`

#### `{{complainant_phone}}`
- **Descripción**: Número de teléfono de contacto del quejoso.
- **Ejemplo**: `3001234567`, `6012345678`
- **Formato**: Número de texto
- **Nota**: Si no hay datos del usuario, retornará `N/A`

#### `{{complainant_email}}`
- **Descripción**: Correo electrónico de contacto del quejoso.
- **Ejemplo**: `juan.perez@example.com`
- **Formato**: Email
- **Nota**: Si no hay datos del usuario, retornará `N/A`

#### `{{complainant_municipality}}`
- **Descripción**: Municipio de residencia del quejoso.
- **Ejemplo**: `Bogotá D.C.`, `Envigado`
- **Formato**: Texto
- **Nota**: Si no está registrado, retornará `N/A`

#### `{{complainant_company}}`
- **Descripción**: Empresa u organización a la que pertenece el quejoso (si aplica).
- **Ejemplo**: `Empresa ABC S.A.S.`, `Hospital XYZ`
- **Formato**: Texto
- **Nota**: Si no está registrado, retornará `N/A`

#### `{{complainant_is_anonymous}}`
- **Descripción**: Indica si el quejoso realizó la queja de forma anónima.
- **Valores posibles**: `Sí` o `No`
- **Formato**: Texto

---

### 🏥 Datos del Médico Demandado

#### `{{doctor_name}}`
- **Descripción**: Nombre completo del médico demandado (nombre y apellido).
- **Ejemplo**: `Carlos Rodríguez`, `Ana Martínez`
- **Formato**: Texto
- **Nota**: Si no hay datos del usuario, retornará `N/A`

#### `{{doctor_document_type}}`
- **Descripción**: Tipo de documento de identidad del médico.
- **Valores posibles**: 
  - `Cédula de Ciudadanía`
  - `Cédula de Extranjería`
- **Formato**: Texto legible
- **Nota**: Si no hay datos del usuario, retornará `N/A`

#### `{{doctor_document_number}}`
- **Descripción**: Número de documento de identidad del médico.
- **Ejemplo**: `1234567890`
- **Formato**: Número de texto
- **Nota**: Si no hay datos del usuario, retornará `N/A`

#### `{{doctor_address}}`
- **Descripción**: Dirección de residencia o consultorio del médico.
- **Ejemplo**: `Calle 45 #67-89`, `Carrera 10 #20-30`
- **Formato**: Texto
- **Nota**: Si no hay datos del usuario, retornará `N/A`

#### `{{doctor_phone}}`
- **Descripción**: Número de teléfono de contacto del médico.
- **Ejemplo**: `3001234567`
- **Formato**: Número de texto
- **Nota**: Si no hay datos del usuario, retornará `N/A`

#### `{{doctor_email}}`
- **Descripción**: Correo electrónico de contacto del médico.
- **Ejemplo**: `carlos.rodriguez@example.com`
- **Formato**: Email
- **Nota**: Si no hay datos del usuario, retornará `N/A`

#### `{{doctor_specialty}}`
- **Descripción**: Especialidad médica del profesional.
- **Ejemplo**: `Cardiología`, `Medicina General`, `Pediatría`
- **Formato**: Texto
- **Nota**: Si no hay especialidad registrada, retornará `N/A`

#### `{{doctor_faculty}}`
- **Descripción**: Facultad o universidad donde el médico obtuvo su título.
- **Ejemplo**: `Universidad Nacional de Colombia`, `Pontificia Universidad Javeriana`
- **Formato**: Texto
- **Nota**: Puede estar vacío

#### `{{doctor_medical_registration_number}}`
- **Descripción**: Número de registro médico profesional (tarjeta profesional).
- **Ejemplo**: `123456`, `789012`
- **Formato**: Número de texto

#### `{{doctor_medical_registration_place}}`
- **Descripción**: Lugar o entidad donde está registrado el médico profesionalmente.
- **Ejemplo**: `Ministerio de Salud`, `Colegio Médico de Bogotá`
- **Formato**: Texto

#### `{{doctor_medical_registration_date}}`
- **Descripción**: Fecha de registro médico profesional en formato año-mes-día.
- **Ejemplo**: `2010-05-20`, `2015-11-15`
- **Formato**: Fecha (YYYY-MM-DD)

#### `{{doctor_main_practice_company}}`
- **Descripción**: Empresa o institución principal donde el médico ejerce su práctica profesional.
- **Ejemplo**: `Hospital San José`, `Clínica Los Andes`
- **Formato**: Texto
- **Nota**: Si no está registrado, retornará `N/A`

#### `{{doctor_other_practice_company}}`
- **Descripción**: Otra empresa o institución donde el médico también ejerce (si aplica).
- **Ejemplo**: `Consultorio Particular`, `Clínica ABC`
- **Formato**: Texto
- **Nota**: Si no está registrado, retornará `N/A`

---

### ⚖️ Datos del Magistrado Instructor

#### `{{magistrate_instructor_name}}`
- **Descripción**: Nombre completo del magistrado instructor asignado al proceso (nombre y apellido).
- **Ejemplo**: `Luis Sánchez`, `Patricia Ramírez`
- **Formato**: Texto
- **Nota**: Si no hay datos del usuario, retornará `N/A`

#### `{{magistrate_instructor_document_type}}`
- **Descripción**: Tipo de documento de identidad del magistrado instructor.
- **Valores posibles**: 
  - `Cédula de Ciudadanía`
  - `Cédula de Extranjería`
- **Formato**: Texto legible
- **Nota**: Si no hay datos del usuario, retornará `N/A`

#### `{{magistrate_instructor_document_number}}`
- **Descripción**: Número de documento de identidad del magistrado instructor.
- **Ejemplo**: `1234567890`
- **Formato**: Número de texto
- **Nota**: Si no hay datos del usuario, retornará `N/A`

#### `{{magistrate_instructor_address}}`
- **Descripción**: Dirección de contacto del magistrado instructor.
- **Ejemplo**: `Calle 50 #60-70`
- **Formato**: Texto
- **Nota**: Si no hay datos del usuario, retornará `N/A`

#### `{{magistrate_instructor_phone}}`
- **Descripción**: Número de teléfono de contacto del magistrado instructor.
- **Ejemplo**: `3001234567`
- **Formato**: Número de texto
- **Nota**: Si no hay datos del usuario, retornará `N/A`

#### `{{magistrate_instructor_email}}`
- **Descripción**: Correo electrónico de contacto del magistrado instructor.
- **Ejemplo**: `luis.sanchez@example.com`
- **Formato**: Email
- **Nota**: Si no hay datos del usuario, retornará `N/A`

---

### 📜 Datos del Magistrado Ponente

#### `{{magistrate_ponente_name}}`
- **Descripción**: Nombre completo del magistrado ponente asignado al proceso (nombre y apellido).
- **Ejemplo**: `Roberto Gómez`, `Laura Fernández`
- **Formato**: Texto
- **Nota**: Si no hay datos del usuario, retornará `N/A`

#### `{{magistrate_ponente_document_type}}`
- **Descripción**: Tipo de documento de identidad del magistrado ponente.
- **Valores posibles**: 
  - `Cédula de Ciudadanía`
  - `Cédula de Extranjería`
- **Formato**: Texto legible
- **Nota**: Si no hay datos del usuario, retornará `N/A`

#### `{{magistrate_ponente_document_number}}`
- **Descripción**: Número de documento de identidad del magistrado ponente.
- **Ejemplo**: `1234567890`
- **Formato**: Número de texto
- **Nota**: Si no hay datos del usuario, retornará `N/A`

#### `{{magistrate_ponente_address}}`
- **Descripción**: Dirección de contacto del magistrado ponente.
- **Ejemplo**: `Calle 80 #90-10`
- **Formato**: Texto
- **Nota**: Si no hay datos del usuario, retornará `N/A`

#### `{{magistrate_ponente_phone}}`
- **Descripción**: Número de teléfono de contacto del magistrado ponente.
- **Ejemplo**: `3001234567`
- **Formato**: Número de texto
- **Nota**: Si no hay datos del usuario, retornará `N/A`

#### `{{magistrate_ponente_email}}`
- **Descripción**: Correo electrónico de contacto del magistrado ponente.
- **Ejemplo**: `roberto.gomez@example.com`
- **Formato**: Email
- **Nota**: Si no hay datos del usuario, retornará `N/A`

---

## Ejemplo de Uso en una Plantilla

```
TRIBUNAL DE ÉTICA MÉDICA
DEMANDA POR MALA PRÁCTICA MÉDICA

Proceso N°: {{process_number}}
Fecha de Radicación: {{process_date}}

I. IDENTIFICACIÓN DE LAS PARTES

A. QUEJOSO/DEMANDANTE:
Nombre: {{complainant_name}}
Documento de Identidad: {{complainant_document_type}} {{complainant_document_number}}
Dirección: {{complainant_address}}
Ciudad: {{complainant_city}}
Teléfono: {{complainant_phone}}
Correo Electrónico: {{complainant_email}}
¿Es anónimo?: {{complainant_is_anonymous}}

B. MÉDICO DEMANDADO:
Nombre: {{doctor_name}}
Documento de Identidad: {{doctor_document_type}} {{doctor_document_number}}
Especialidad: {{doctor_specialty}}
Número de Registro Médico: {{doctor_medical_registration_number}}
Fecha de Registro: {{doctor_medical_registration_date}}
Dirección: {{doctor_address}}
Teléfono: {{doctor_phone}}
Correo Electrónico: {{doctor_email}}

C. MAGISTRADO INSTRUCTOR:
Nombre: {{magistrate_instructor_name}}
Documento: {{magistrate_instructor_document_type}} {{magistrate_instructor_document_number}}
Correo: {{magistrate_instructor_email}}

D. MAGISTRADO PONENTE:
Nombre: {{magistrate_ponente_name}}
Documento: {{magistrate_ponente_document_type}} {{magistrate_ponente_document_number}}
Correo: {{magistrate_ponente_email}}

II. DESCRIPCIÓN DEL CASO
{{process_description}}

Estado del Proceso: {{process_status}}
```

## Notas Importantes

1. **Formato de Placeholders**: Todos los placeholders deben escribirse exactamente como se muestra, usando triple llaves: `{{nombre_del_placeholder}}`

2. **Mayúsculas y Minúsculas**: Los placeholders son sensibles a mayúsculas y minúsculas. Deben escribirse exactamente como se muestra en esta documentación.

3. **Valores por Defecto**: Si algún dato no está disponible en el sistema, el placeholder se reemplazará con `N/A` (No Aplicable).

4. **Fechas**: Todas las fechas se muestran en formato `YYYY-MM-DD` (año-mes-día). Por ejemplo: `2024-01-15`.

5. **Tipos de Documento**: Los tipos de documento se muestran en formato legible (ej: "Cédula de Ciudadanía") en lugar de valores técnicos.

6. **Quejosos Anónimos**: Cuando un quejoso es anónimo, algunos campos como nombre, documento, etc., pueden mostrar `N/A`.

7. **Espacios**: Los placeholders pueden estar en cualquier parte del documento y pueden tener espacios antes o después, pero el placeholder mismo debe escribirse sin espacios entre las llaves.

## Soporte

Si tiene dudas sobre el uso de los placeholders o necesita agregar nuevos campos, contacte al equipo de desarrollo.

