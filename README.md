# PHP Dynamic Email Service

Un servicio REST API profesional y robusto para el envío de correos electrónicos dinámicos mediante PHPMailer y SMTP.

## Requisitos
- PHP 8.1+
- Composer
- O Docker instalado

## Estructura de Carpetas
```text
/
├── app/
│   ├── Config/        # Gestión de configuración (.env)
│   ├── Controllers/   # Lógica del API REST
│   ├── Services/      # Clase MailService para el envío
│   └── Validation/    # Validación de datos
├── public/            # Punto de entrada (index.php)
├── .env               # Configuración SMTP
├── composer.json      # Dependencias
├── Dockerfile         # Configuración del contenedor
└── README.md          # Este archivo
```

## Instalación

### Método 1: Local (sin Docker)
1. Clona el repositorio.
2. Ejecuta `composer install`.
3. Renombra `.env.example` a `.env` y configura tus credenciales SMTP.
4. Inicia el servidor PHP:
   ```bash
   php -S localhost:8080 -t public
   ```

### Método 2: Docker
1. Ejecuta el comando:
   ```bash
   docker-compose up -d --build
   ```
2. El servicio estará disponible en `http://localhost:8080`.

## Ejemplo de Uso (cURL)

### 1. Guardar una Configuración
Puedes guardar múltiples configuraciones SMTP con un nombre único.

```bash
curl -X POST http://localhost:8080/config \
     -H "Content-Type: application/json" \
     -d '{
           "name": "empresa_smtp",
           "host": "smtp.gmail.com",
           "port": 587,
           "user": "tu-usuario@gmail.com",
           "pass": "tu-password-aplicacion",
           "secure": "tls",
           "from_email": "tu-usuario@gmail.com",
           "from_name": "Nombre Empresa"
         }'
```

### 2. Enviar Correo usando una Configuración Guardada
Una vez guardada, solo necesitas pasar el `config_name`.

```bash
curl -X POST http://localhost:8080/ \
     -H "Content-Type: application/json" \
     -d '{
           "config_name": "empresa_smtp",
           "to": "destinatario@ejemplo.com",
           "subject": "Prueba con Configuración Guardada",
           "body": "<h1>Éxito!</h1><p>Usando configuración persistente.</p>"
         }'
```

### 3. Enviar Correo usando Configuración por Defecto (.env)
Si no envías `config_name`, se usarán los valores del archivo `.env`.

```bash
curl -X POST http://localhost:8080/ \
     -H "Content-Type: application/json" \
     -d '{
           "to": "destinatario@ejemplo.com",
           "subject": "Prueba por Defecto",
           "body": "<p>Usando .env</p>"
         }'
```

---
**Desarrollado con ❤️ para Christian.**
