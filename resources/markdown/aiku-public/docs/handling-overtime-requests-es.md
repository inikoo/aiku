---
title: Gestionar las solicitudes de horas extra
summary: El personal pide horas extra desde su propia página de fichaje; RR. HH. encuentra cada solicitud como una tarjeta, la aprueba, la rechaza, la edita o la elimina, registra horas extra en nombre de alguien y mantiene la lista de tipos de horas extra.
date: 2026-10-12
source_date: 2026-10-12
tags: hr, overtime
category: hr
---

<aside class="tldr">
<b>HR → Overtime</b> lista cada solicitud de horas extra como una tarjeta. Aprueba o rechaza las pendientes; una vez que una solicitud tiene una decisión, no se puede volver a aprobar, rechazar ni editar. RR. HH. también puede introducir una solicitud por alguien con <b>New overtime request</b>. La pestaña <b>Dashboard</b> muestra el mes, y <b>Overtime types</b> guarda las clases de horas extra entre las que se puede elegir.
</aside>

## Pedir horas extra (empleado)

En tu página **Employee Clocking**, abre la pestaña **Overtime** y pulsa **New overtime request**. Elige el **Overtime type** (tipo de horas extra) y la **Date worked** (fecha trabajada), indica la **Start time** (hora de inicio) y la **Duration** (duración) en horas y minutos, y explica el porqué en **Reason**. **Submit Request** la envía a RR. HH. Mientras está pendiente todavía puedes cambiarla.

## Encontrar una solicitud (RR. HH.)

Abre **HR → Overtime**. Cada tarjeta muestra el empleado, el tipo de horas extra, lo que se pidió (**Requested**: la fecha, la hora de inicio y cuánto tiempo), lo que quedó registrado (**Recorded**), el motivo y la decisión (**Decision**) con quién la tomó y cuándo.

Encima de las tarjetas puedes buscar por empleado, aprobador, tipo o motivo, elegir un estado y poner una fecha **From** (desde) y **To** (hasta). **Clear filters** los borra. **Export** descarga el informe de horas extra en Excel o CSV.

## Aprobar y rechazar

En una tarjeta pendiente:

- **Approve**, y luego **Yes, approve**.
- **Reject**, y luego **Yes, reject**.
- **Edit** para corregir la solicitud antes de decidirla.

Tras la decisión, la tarjeta muestra **Processed**. Una solicitud decidida no se puede volver a aprobar, rechazar ni editar.

**Delete** está disponible en cualquier solicitud. Si ya se ha procesado, aiku te lo dice antes de que confirmes.

## Introducir horas extra por alguien

Pulsa **New overtime request** en la parte superior de la página.

1. Elige el **Staff member** (empleado), el **Overtime type** y la **Date worked**.
2. Indica la hora de inicio y la duración que se pidieron.
3. En **Recorded time**, deja **Same as requested** (igual que lo pedido), o elige **Different time** e introduce la **Recorded start time** y la **Recorded duration** que se trabajaron de verdad.
4. Elige el **Status** y añade un motivo en **Reason** si quieres.
5. Pulsa **Submit request**.

Cuando el tiempo registrado difiere del pedido, la tarjeta muestra la diferencia junto a **Recorded**.

## Tipos de horas extra

La pestaña **Overtime types** es la lista entre la que elige la gente. **New overtime type** añade uno, con un **Code**, un **Name**, un **Color** y una **Description** opcionales, y:

- **Category**: Overtime, Flexi time, Working time u Other.
- **Compensation type**: Paid (pagadas), Time in lieu (compensadas con tiempo libre) o Unpaid (no pagadas).
- **Multiplier**, si este tipo se paga con un recargo.

La lista marca cada tipo como **Active** o **Inactive**, y cada uno se puede editar o eliminar.

## El panel

La pestaña **Dashboard** es el mes de un vistazo: una fila por empleado, una columna por día. Filtra por empleado o tipo de horas extra, avanza o retrocede por meses y pulsa una entrada para ver sus **Overtime Details**.

<aside class="wayfinder"><strong>Dónde pulsar en aiku</strong>
<ul>
<li><b>Pedir horas extra:</b> <b>Employee Clocking</b> → <b>Overtime</b> → <b>New overtime request</b>.</li>
<li><b>Decidir solicitudes:</b> tu organización → <b>HR → Overtime</b>.</li>
<li><b>Introducir horas extra por alguien:</b> <b>HR → Overtime</b> → <b>New overtime request</b>.</li>
<li><b>Las clases de horas extra:</b> <b>HR → Overtime</b> → <b>Overtime types</b>.</li>
<li><b>El mes:</b> <b>HR → Overtime</b> → <b>Dashboard</b>.</li>
</ul>
</aside>

<aside class="wayfinder"><strong>Permisos que necesitas</strong>
<ul>
<li>Pedir horas extra requiere una ficha de empleado vinculada a tu login de aiku.</li>
<li>Ver las solicitudes requiere permisos de <b>HR view</b> en la organización. Un supervisor de sección solo ve las solicitudes de su propia sección, y no puede cambiarlas.</li>
<li>Aprobar, rechazar, editar, eliminar e introducir solicitudes requieren permisos de <b>HR edit</b>. Lo mismo para cambiar los tipos de horas extra.</li>
</ul>
</aside>

Relacionado: <a href="/docs/handling-leave-requests-es">Gestionar las solicitudes de ausencia</a> y <a href="/docs/correcting-a-clocking-es">Corregir un fichaje</a>.
