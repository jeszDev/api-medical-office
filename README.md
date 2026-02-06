# CONSULTORIO MÉDICO - Laravel (API REST)

## Requisitos previos

Antes de iniciar, asegúrate de contar con:

* PHP **8.1 - 8.4**
* Composer instalado
* Node.js y npm instalados (como es API no es necesario)
* Servidor de base de datos configurado (MySQL, PostgreSQL, etc.)

## Pasos para levantar el proyecto

### 1. Clonar el repositorio

```bash
git clone <url-del-repositorio>
cd nombre-del-proyecto
```

### 2. Habilitar extensiones en php.ini

```bash
extension=fileinfo
extension=mysqli
extension=pdo_mysql
````

### 3. Instalar dependencias de PHP

```bash
composer install
```

### 3. Instalar dependencias de Node

```bash
Como es una API no es necesario instalar
npm install
```

### 4. Configurar el archivo de entorno

```bash
cp .env.example .env
```

Editar el archivo `.env` con las credenciales de base de datos y configuraciones necesarias.

### 5. Generar la clave de la aplicación

```bash
php artisan key:generate
```

### 6. Ejecutar migraciones (si aplica)

```bash
php artisan migrate --seed

Limpiar la BD y cargar nuevamente

php artisan migrate:fresh --seed
```

### 7. Levantar el servidor de Laravel

```bash
php artisan serve
```

La API quedará disponible normalmente en:

```
http://127.0.0.1:8000
```

### 8. Compilar assets frontend (si aplica)

```bash
No aplica porque es una API
npm run dev
```

## Notas

* Este proyecto funciona como **API REST**, por lo que los endpoints se consumen desde aplicaciones externas (frontend, móvil, etc.).
* Para entorno productivo se recomienda usar:

```bash
npm run build
```

---
