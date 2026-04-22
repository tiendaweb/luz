
## Demo: migraciones, seeds y credenciales

> ⚠️ **Uso exclusivo en entorno demo/local.**  
> Las credenciales incluidas aquí son públicas y no deben usarse en producción.

### Ejecutar migraciones + seeds

```bash
php scripts/seed.php
```

Este comando crea/actualiza tablas (incluyendo `roles` y `users.role_id`) y carga usuarios demo con contraseñas hasheadas mediante `password_hash()` (Argon2id o bcrypt como fallback).

### Credenciales de prueba

- **Admin**: `admin@psme.local` / `Admin123*`
- **Asociado**: `asociado@psme.local` / `Asociado123*`
- **Usuario**: `usuario@psme.local` / `Usuario123*`

---

### **OBJETIVO PRINCIPAL**
Crear una **maqueta funcional HTML/CSS/JS** (sin persistencia de datos) para la plataforma web de "Foro LATAM 2026 PSME" — un espacio de encuentro en Salud Mental y Emocional dirigido por María Luz Genovese, Psicóloga Social.

---

### **INFORMACIÓN DEL PROYECTO**

**Responsable:**
- **Nombre:** María Luz Genovese
- **Profesión:** Psicóloga Social
- **Contacto WhatsApp:** (+54) 9 115593 6719

**Propuesta:**
El sitio debe posicionar enfocarse en los foros y espacio, ademas, el expertise de María Luz como profesional mientras facilita la inscripción a sus foros internacionales. Incluir CTA claros para reserva de turnos y consultas.
Los foros son reuniones online via zoom, meet, se accede mediante un link

---

### **AUDIENCIA OBJETIVO**

- Psicólogos y profesionales de salud mental/emocional
- Estudiantes de psicología y disciplinas afines
- Personas interesadas en bienestar emocional y psicología social

---

### **CONTENIDO PRINCIPAL: FORO LATAM 2026 PSME**

**¿Qué es el foro?**
Espacio de encuentro donde se abordan temas de Psicología, Psicosocial y Salud Mental/Emocional a través de:
- Experiencias personales
- Teoría y enfoque psicológico
- Salud mental y bienestar emocional
- Relaciones interpersonales y comunicación
- Cultura y sociedad en relación con la psicología

**Objetivos:**
- Compartir conocimiento y experiencias
- Discutir y reflexionar sobre temas relevantes
- Aprender de otros y recibir apoyo
- Conectar con personas con intereses comunes

---

### **CALENDARIOS Y HORARIOS POR ZONA (MAYO 2026)**

#### **PROFESIONALES:**
| Zona | Fechas | Horario AR |
|------|--------|-----------|
| Colombia | Sábados 9, 16, 23, 30 | 10:00–11:00 |
| Ecuador/Bolivia | Lunes 4, 11, 18, 25 | 21:00–22:00 |
| México | Sábados 9, 16, 23, 30 | 19:00–20:00 |
| Ecuador | Martes 5, 12, 19, 26 | 20:00–21:00 |
| Guatemala | Miércoles 6, 13, 20, 27 | 20:00–21:00 |
| Perú | Miércoles 6, 13, 20, 27 | 21:00–22:00 |

#### **ESTUDIANTES:**
| Zona | Fechas | Horario AR |
|------|--------|-----------|
| Colombia | Sábados 9, 16, 23, 30 | 11:00–12:00 |
| México | Sábados 9, 16, 23, 30 | 11:00–12:00 |
| Guatemala | Miércoles 6, 13, 20, 27 | 11:00–12:00 |

**Modalidad:** Virtual

---

### **TARIFAS**

| Cantidad | Precio unitario | Descuento |
|----------|-----------------|-----------|
| 1–3 personas | $35.000 ARS | — |
| 4+ personas | $28.000 ARS | 20% |

**Opcionales:**
- Certificado de asistencia: Sí/No
- Comprobante de pago: Archivo adjunto (requerido con N° DNI)

---

### **ESTRUCTURA DE LA PLATAFORMA**

#### **PÁGINAS PÚBLICAS**

1. **Home / Landing Page**
   - Presentación clara de la propuesta y valor
   - Audiencia objetivo y beneficios
   - CTA principal: "Inscribirse al Foro"
   - Preview de próximas sesiones
   - Testimonios o highlights (opcional)

2. **Sobre María Luz** (Página de profesional)
   - Foto y biografía
   - Formación académica y experiencia
   - Especialidades y enfoques
   - Credibilidad y confianza

3. **Foros / Eventos**
   - Listado de foros disponibles
   - Descripción de cada uno (temática, objetivos)
   - Calendario interactivo con fechas/horarios por zona
   - Cupos disponibles (visual)
   - Botón individual: "Inscribirse a este foro"

4. **Blog** (Opcional)
   - Artículos sobre salud mental, psicología social
   - Reflexiones y actualizaciones

5. **Contacto**
   - Formulario de contacto (simulado, sin guardar)
   - Enlaces directos: WhatsApp, Email, Redes sociales
   - Mapa integrado (si aplica ubicación física)

6. **Zona de Usuarios** (Después de inscribirse)
   - **Login/Register** (simulado)

---

#### **ZONA DE USUARIOS: 3 Roles**

##### **ADMIN (María Luz)**
- Dashboard principal
- **Gestión de Usuarios:** Ver, editar, eliminar registros
- **Gestión de Eventos:** Crear, editar, listar foros
- **Gestión de Turnos/Citas:** Reservas y confirmaciones
- **Inscripciones:** Estado, certificados solicitados
- **Mensajes:** Contactos y mensajes internos
- Reportes básicos (inscritos, ingresos, etc.)

##### **ASOCIADO** (Con link de referido personalizado)
- **Link de referido único:** Cada vez que se comparte, contiene parámetro de afiliado
- **Panel de referidos:** Ver usuarios inscritos a través del link
- **Estado de pagos:** Verificar estado de pago de referidos
- **Comisiones/Ganancias:** Dashboard simple (opcional)

##### **USUARIO** (Profesional, Estudiante, Interesado)
- **Mi Perfil:** Datos personales, rol, estado
- **Mis Inscripciones:** Eventos a los que se ha registrado
- **Mis Descargas:** eBooks/recursos entregados en foros asistidos
- **Historial:** Foros completados con certificados descargables

---

### **FORMULARIO DE INSCRIPCIÓN**

**Campos requeridos:**
1. Seleccionar fecha y hora disponible
2. Rol: Profesional / Estudiante
3. Nombre y apellidos completo (para certificado)
4. Número de documento (DNI/Cédula)
5. Profesión o ejercicio actual
6. ¿Solicita certificado de asistencia? (Sí/No)
7. Link de pago (si certificado = Sí)
8. Adjuntar comprobante de pago (archivo)
9. **Firma digital / Aceptación** (checkbox + campo de firma)

**Mensaje final:**
> "Gracias por seleccionar esta experiencia en comunidad. Nos pedimos compromiso y responsabilidad a la hora de asistir. Cualquier inquietud, estaré a disposición.  
> **María Luz Genovese** | Psicóloga Social | WhatsApp: (+54) 9 115593 6719"

---

### **ESPECIFICACIONES TÉCNICAS**

**Stack:**
- HTML5 (semántico)
- CSS3 (responsive, mobile-first)
- JavaScript vanilla (sin frameworks ni persistencia)
- Formularios simulados (validación local, sin base de datos)

**Funcionalidad:**
- ✅ Navegación fluida entre páginas
- ✅ Calendario interactivo para seleccionar turnos
- ✅ Formulario de inscripción funcional (sin guardar)
- ✅ Simulación de login/zona usuarios (sin autenticación real)
- ✅ Responsive design (móvil, tablet, desktop)
- ✅ Firma digital/canvas (visual)

**Funcionalidad NO incluida:**
- ❌ Base de datos
- ❌ Envío de emails
- ❌ Pagos reales
- ❌ Autenticación segura

---

### **ESTILO Y TONALIDAD**

- **Paleta:** Colores cálidos, profesionales y accesibles (psicología ≠ frío)
- **Tipografía:** Moderna, legible, con buena jerarquía
- **Imagery:** Fotos de profesionales, espacios colaborativos, o abstracciones en salud mental
- **Tono:** Empático, confiable, profesional pero cercano
- **Accesibilidad:** WCAG 2.1 AA (contraste, navegación)

---

### **ENTREGABLES**

1. **Index.html** (landing + home)
2. **about.html** (sobre María Luz)
3. **foros.html** (listado y detalles)
4. **contact.html** (contacto)
5. **login.html** (simulado)
6. **dashboard-admin.html** (simulado)
7. **dashboard-usuario.html** (simulado)
8. **styles.css** (global + componentes)
9. **script.js** (navegación, formularios, interactividad)
10. **assets/** (imágenes, iconos)

---

### **PRIORIDADES**

**MVP (Mínimo Viable):**
1. Landing page clara y atractiva
2. Página sobre María Luz (credibilidad)
3. Listado de foros con calendarios
4. Formulario de inscripción funcional
5. Zona de usuario básica (login simulado)

**Nice to have:**
- Blog
- Testimonios
- Dashboard admin/asociado completo
- Animaciones sutiles

---

## ✅ MEJORAS APLICADAS

| Aspecto | Original | Mejorado |
|---------|----------|----------|
| **Estructura** | Dispersa, sin orden lógico | Secciones claras por tipo (público, usuarios, specs) |
| **Horarios** | Texto corrido, confuso | Tabla con zonas, fechas y horarios |
| **Roles** | Descritos brevemente | Desglose detallado de funcionalidades por rol |
| **Formulario** | Puntos sueltos | Flujo completo con contexto |
| **Especificaciones** | Vago ("en .html") | Stack, funcionalidad y limitaciones explícitas |
| **Prioridades** | No hay | MVP + Nice to have definidos |
| **Entregables** | No mencionados | Lista de archivos esperados |

---

### **SISTEMA DE ESTILOS BASE (IMPLEMENTADO)**

Se incorporó una base reusable para acelerar la maqueta:

- `styles.css` con variables globales de color, tipografía, spacing, radios y sombras.
- Componentes mínimos: botones CTA, cards, tablas, badges de cupos, inputs y alerts.
- Enfoque de contraste/legibilidad orientado a WCAG 2.1 AA.
- Breakpoints mobile-first para móvil/tablet/desktop.
- Guía de clases utilitarias en `docs/guia-componentes.md` para evitar CSS duplicado.
