# Backlog fuera de alcance del prompt

Listado de funciones detectadas pero excluidas de este alcance MVP documental.

| ID | Función fuera de alcance | Prioridad | Esfuerzo | Dependencia principal | Motivo de exclusión actual |
|---|---|---|---|---|---|
| BKL-01 | Integración con pasarela de pago real (checkout + webhooks) | Alta | Alto | Backend transaccional + legal/finanzas | El prompt define flujo de pagos y reglas, no implementación productiva de cobro. |
| BKL-02 | Facturación electrónica por país | Alta | Alto | Proveedor fiscal local por país | Requiere cumplimiento tributario y homologación legal fuera de MVP. |
| BKL-03 | Conciliación automática bancaria | Media | Alto | Integraciones bancarias/API terceros | Necesita conectividad externa y reglas contables no definidas en este alcance. |
| BKL-04 | Motor de antifraude para comprobantes | Media | Medio/Alto | ML/servicio OCR + datasets | MVP usa validación operativa/manual, no scoring automatizado. |
| BKL-05 | SLA inteligente y autoasignación de cola por asociado | Media | Medio | Orquestador de tareas + métricas históricas | Primero se requiere operación base y telemetría estable. |
| BKL-06 | Multiidioma completo (i18n) para paneles y mensajes | Media | Medio | Sistema de traducciones + QA lingüístico | No crítico para validar flujo núcleo en esta fase. |
| BKL-07 | Dashboard ejecutivo con KPIs en tiempo real | Baja | Medio | Data pipeline + BI | MVP prioriza trazabilidad funcional y validación de reglas. |
| BKL-08 | SSO corporativo (SAML/OIDC) | Baja | Medio/Alto | IdP corporativo + seguridad | Se mantiene autenticación simplificada del entorno demo/MVP. |
| BKL-09 | Notificaciones omnicanal (email/SMS/WhatsApp) | Media | Medio | Proveedores de mensajería + plantillas | Fuera del alcance actual; solo estados y auditoría internos. |
| BKL-10 | Gestión avanzada de campañas de referidos (A/B, atribución multi-touch) | Baja | Alto | Analítica de marketing + almacenamiento de eventos | MVP cubre referido base con trazabilidad mínima. |

## Priorización sugerida para siguiente ola

1. **Ola 1**: BKL-01, BKL-02, BKL-09 (valor de negocio directo post-MVP).
2. **Ola 2**: BKL-03, BKL-04, BKL-05 (eficiencia operativa y control de riesgo).
3. **Ola 3**: BKL-06, BKL-07, BKL-08, BKL-10 (escala y optimización).

## Criterios para mover un ítem a ejecución

- Dueño funcional y técnico asignado.
- Dependencias externas confirmadas (proveedor, contrato, acceso).
- Criterios de aceptación medibles y testables.
- Estimación refinada y ventana de release definida.
