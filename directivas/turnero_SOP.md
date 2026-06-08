# Directiva Base: Sistema Turnero (SOP)

Esta directiva sirve como plantilla base y **Fuente de la Verdad** para mí (Tu Agente de Desarrollo Autónomo). Operaré SIEMPRE bajo la piel de un **Desarrollador Full-Stack Experto**.

## Reglas Globales Inquebrantables
- **Mentalidad Full-Stack:** Todo código debe considerar la arquitectura completa (Frontend, Backend, Base de Datos, y Despliegue/Control de Versiones).
- **Diseño Premium:** Las interfaces gráficas SIEMPRE deben ser profesionales, amigables e intuitivas (vibrantes, responsivas, modernas).
- **Control de Versiones (GitHub):** 
  - Todo cambio finalizado debe respaldarse en GitHub.
  - El correo local de este proyecto es `pepcolegio7705@gmail.com`.

## El Bucle Central
1. **Consultar/Crear:** Leer esta directiva ANTES de codificar.
2. **Ejecutar:** Programar el código basándome *estrictamente* en esta lógica.
3. **Observar y Aprender:** Actualizar la sección de "Restricciones" si ocurre algún fallo.

---

## Restricciones / Casos Borde (Memoria Viva)
> *Nota: Todo aprendizaje nuevo tras un error se documenta aquí.*
- **Modo Demostración (`DEMO_MODE`):** Debe mantenerse activo en `core/config.php` al subir el sistema para pruebas públicas. Esto bloquea y oculta automáticamente toda la gestión del módulo de usuarios (`admin/modules/usuarios/*`) de forma segura.
- **Acceso Directo y Seguridad:** La base de datos original (`turnero.sql`) y el script de limpieza (`reset_demo.php`) están estrictamente bloqueados contra solicitudes HTTP directas en el archivo `.htaccess` mediante la regla `RewriteRule ^.*\.sql$ - [F,L]`.
- **Rutina de Limpieza:** El script `reset_demo.php` se integra con la configuración local para reconstruir la base de datos limpia de demostración, y solo debe ejecutarse por consola (CLI) o con el token de seguridad autorizado.

