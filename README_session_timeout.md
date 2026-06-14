Cambio: implementación de cierre automático de sesión por inactividad

Resumen
- Se añadió cierre automático de sesión tras 20 minutos de inactividad.

Archivos modificados
- index.php: se establece `$_SESSION['last_activity'] = time()` al iniciar sesión.
- base_html.php: verificación server-side del timeout y destrucción de la sesión si expira.
- logs/nav-bar.php: verificación server-side adicional y actualización de `last_activity`.
- logs/head.php: script JS global que redirige al `logout.php` tras 20 minutos de inactividad del cliente.
- parqueo_informes/dashboard.php: verificación server-side y script cliente local (se mantuvo para compatibilidad).
- tests/session_timeout_test.php: script de inspección de sesión para pruebas.

Cómo ajustar el timeout
- Producción: el valor por defecto es 20 minutos (20 * 60 segundos).
- Pruebas locales: cambiar temporalmente los valores a 30 (segundos) en:
  - `logs/head.php` (variable `maxInactive`)
  - `parqueo_informes/dashboard.php` (variable `maxInactive` en el script JS y `$inactive` en PHP)
  - `base_html.php` y `logs/nav-bar.php` (variable `$inactive` en PHP)

Notas
- El logout se realiza mediante `logout.php`; el script cliente redirige a `/parqueadero/logout.php?timeout=1`.
- Si hay páginas que no incluyen `logs/head.php`, pueden no ejecutar el script JS global. Estas páginas incluyen `index.php` (login). El control server-side cubre las páginas protegidas.

Recomendación
- Mantener el control server-side (PHP) como fuente de verdad y usar el script cliente solo para redirección automática UX.

Commit sugerido
- Mensaje: "feat: auto-logout por inactividad (20 minutos)"
- Archivos: index.php, base_html.php, logs/nav-bar.php, logs/head.php, parqueo_informes/dashboard.php, tests/session_timeout_test.php

¿Hacer commit ahora con ese mensaje?

Fecha: 2026-06-14
Autor: cambios automáticos realizados por el asistente de desarrollo
