--crear tabla  de usuarios
CREATE TABLE usuarios (
  id SERIAL PRIMARY KEY,
  nombre TEXT,
  correo TEXT,
  fecha_registro TIMESTAMP DEFAULT NOW()
);

--crear tabla  de tareas
CREATE TABLE tareas (
  id SERIAL PRIMARY KEY,
  titulo TEXT,
  descripcion TEXT,
  estado TEXT, -- puede ser 'pendiente' o 'completada'
  fecha_creacion TIMESTAMP DEFAULT NOW(),
  usuario_id INTEGER REFERENCES usuarios(id)
);

-- Consultas SQL:

--usuarios registrados en los ultimod  30 dias
SELECT * FROM usuarios
WHERE fecha_registro >= now() - INTERVAL '30 days';

-- actualizar el nombre de un usuario por ID
UPDATE usuarios
SET nombre = 'nuevo nombre'
WHERE id = 1;

--eliminar usuarios con mas de un año de antiguedad
DELETE FROM usuarios
WHERE fecha_registro < NOW() - INTERVAL '1 year';

-- Cosas agregadas 

INSERT INTO tareas (titulo, descripcion, estado, usuario_id)
VALUES ('Tarea 1', 'Descripcion de prueba', 'pendiente', 1);

INSERT INTO usuarios (nombre, correo)
VALUES ('Usuario de prueba', 'usuario@ejemplo.com');

SELECT *
FROM tareas

SELECT *
FROM usuarios




