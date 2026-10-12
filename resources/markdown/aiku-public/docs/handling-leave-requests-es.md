---
title: Gestionar las solicitudes de ausencia
summary: Una sola página para todas las solicitudes de ausencia de la organización — encontrar una solicitud, registrar una ausencia en nombre de alguien, aprobarla, rechazarla, editarla o eliminarla — más el calendario de ausencias y la pestaña Leave de cada empleado.
date: 2026-10-12
source_date: 2026-10-12
tags: hr, leave
category: hr
---

<aside class="tldr">
<b>HR → Leave Requests</b> lista cada solicitud de ausencia como una tarjeta. Los aprobadores deciden ahí las solicitudes pendientes, y RR. HH. puede registrar una ausencia por alguien, que queda aprobada al guardarla. Una solicitud ya aprobada o rechazada no se puede volver a decidir. La pestaña <b>Dashboard</b> es el calendario de ausencias, y la página de cada empleado tiene una pestaña <b>Leave</b> con su propio historial.
</aside>

El personal pide sus ausencias por sí mismo, desde la pestaña **Leave** de su página **Employee Clocking**, con **Request Leave**. Lo que envían llega aquí como *pendiente*.

## Encontrar una solicitud

Abre **HR → Leave Requests**. Cada tarjeta muestra el empleado, el tipo de ausencia, el periodo (**Leave period**) con el número de días, hasta dónde ha llegado la aprobación (**Approval**), el motivo, los adjuntos y quién cubre la ausencia (**Covered by**).

Encima de las tarjetas:

- **Search** busca por empleado, tipo de ausencia o motivo.
- **Status**: **All**, **Pending**, **Approved** o **Rejected**.
- **All leave types** reduce la lista a un solo tipo.
- **From** y **To** muestran las ausencias que caen dentro de esas fechas.
- **Clear filters** lo deja todo como estaba.

**Export** descarga el informe de ausencias en Excel o CSV, para las fechas, el tipo, el estado, el departamento y el empleado que elijas.

## Registrar una ausencia por alguien

Pulsa **Record leave** cuando la solicitud no llegó a través de aiku: una baja avisada por teléfono, unas vacaciones acordadas en papel.

1. Elige el **Employee**.
2. Elige el **Leave Type** (tipo de ausencia), la **Start Date** (fecha de inicio) y la **End Date** (fecha de fin).
3. Si quieres, elige quién la cubre en **Covered by**. Consulta <a href="/docs/covering-for-a-colleague-on-leave-es">Cubrir a un compañero de baja</a>.
4. Añade un motivo en **Reason** si quieres que conste.
5. Pulsa **Record leave**.

Una ausencia registrada así queda aprobada de inmediato. No espera a ningún aprobador.

## Aprobar y rechazar

Una tarjeta pendiente muestra **Approve** y **Reject** a las personas que son aprobadoras de esa solicitud. Los aprobadores se definen en la pestaña **Leave Approval** de la misma página.

- **Approve** abre **Approve leave**, donde también puedes elegir quién cubre, y luego **Yes, approve**.
- **Reject** pide un motivo en **Rejection Reason**. El empleado lo ve, y se queda en la tarjeta.

Cuando una solicitud necesita más de un aprobador, el recuadro **Approval** muestra el nivel alcanzado, por ejemplo *Level 1 of 2*, y quién ha decidido ya. La ausencia queda aprobada cuando la aprueba el último nivel. Un rechazo en cualquier nivel la rechaza.

Una vez aprobada o rechazada, la solicitud muestra **Processed** y los botones desaparecen. No se puede aprobar ni rechazar una segunda vez.

## Editar

**Edit** está disponible para los aprobadores, solo en solicitudes pendientes. Puedes cambiar el tipo de ausencia, las fechas, el motivo, quién cubre y los adjuntos. Los adjuntos nuevos sustituyen a los que ya había.

## Eliminar

**Delete** borra una solicitud definitivamente, así que aiku pregunta antes y te dice qué vas a deshacer:

- **Pendiente**: la solicitud aún no se ha decidido, y los aprobadores dejarán de verla.
- **Aprobada**: la ausencia sale del registro de ausencias del empleado y del calendario de ausencias, y termina la cobertura que tuviera asignada. El botón dice **Delete anyway**.
- **Rechazada**: la solicitud y el motivo del rechazo salen del registro de ausencias del empleado. El botón dice **Delete anyway**.

## El calendario de ausencias

La pestaña **Dashboard** es el calendario: una fila por empleado, una columna por día, y cada ausencia dibujada con el color de su tipo. Las ausencias que aún esperan aprobación van rayadas, y los festivos y los fines de semana, sombreados.

- Cambia entre **Month** (mes) y **Week** (semana), y avanza o retrocede con las flechas.
- Busca un nombre, o filtra por empleado, tipo de ausencia o departamento.
- **Sort by** ordena por nombre, apellido, nombre de pila o departamento.
- Pulsa una ausencia para ver sus **Leave Details**.
- **Export** descarga el informe de ausencias en Excel o CSV, o lo abre como **Print View** (vista de impresión).

La acción rápida **Leave calendar** del <a href="/docs/reading-the-hr-dashboard-es">panel de HR</a> abre esta misma página.

## Las ausencias de un empleado

Abre un empleado y elige la pestaña **Leave**.

- **Period** elige un año, o **All time** (todo). Empieza en el año actual.
- Los recuadros cuentan **Total requests**, **Approved**, **Pending** y **Rejected**, más los días disfrutados (**Days taken**). Pulsa un recuadro para listar solo esas solicitudes.
- **Leave balance** y **By leave type** muestran cómo se reparten los días aprobados.
- **Leave requests** lista cada solicitud con los mismos detalles que las tarjetas de la página principal.
- **Open in Leave Requests** te lleva a la página principal, ya buscada por este empleado, donde puedes actuar sobre una solicitud.

<aside class="wayfinder"><strong>Dónde pulsar en aiku</strong>
<ul>
<li><b>Todas las solicitudes:</b> tu organización → <b>HR → Leave Requests</b>.</li>
<li><b>Lo que está esperando:</b> pon <b>Status</b> en <b>Pending</b>.</li>
<li><b>Introducir una ausencia por alguien:</b> <b>Record leave</b>, en Leave Requests o desde las acciones rápidas del panel de HR.</li>
<li><b>Quién está ausente, y cuándo:</b> <b>HR → Leave Requests</b> → <b>Dashboard</b>.</li>
<li><b>El historial de una persona:</b> <b>HR → Employees</b> → abre el empleado → <b>Leave</b>.</li>
<li><b>Quién cubre una ausencia:</b> <b>HR → Absence cover</b>.</li>
</ul>
</aside>

<aside class="wayfinder"><strong>Permisos que necesitas</strong>
<ul>
<li>Abrir Leave Requests requiere permisos de <b>HR view</b> en la organización, o ser un aprobador de ausencias activo. Un supervisor de sección solo ve las solicitudes de su propia sección.</li>
<li><b>Approve</b> y <b>Reject</b> son para los aprobadores de esa solicitud. <b>Edit</b> es para los aprobadores de ausencias activos.</li>
<li><b>Record leave</b> y <b>Delete</b> requieren permisos de <b>HR edit</b>.</li>
<li>El calendario de ausencias requiere permisos de <b>HR view</b>.</li>
</ul>
</aside>
