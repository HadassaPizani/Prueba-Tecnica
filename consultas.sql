-- 1. crear tabla  de usuarios
CREATE TABLE usuarios (
  id SERIAL PRIMARY KEY,
  nombre TEXT,
  correo TEXT,
  fecha_registro TIMESTAMP DEFAULT NOW()
);

-- 2. crear tabla  de tareas
CREATE TABLE tareas (
  id SERIAL PRIMARY KEY,
  titulo TEXT,
  descripcion TEXT,
  estado TEXT, -- puede ser 'pendiente' o 'completada'
  fecha_creacion TIMESTAMP DEFAULT NOW(),
  usuario_id INTEGER REFERENCES usuarios(id)
);

-- 3. Consultas SQL:

-- a) Usuarios registrados en los ultimod  30 días
SELECT * FROM usuarios
WHERE fecha_registro >= now() - INTERVAL '30 days';

-- b) actualizar el nombre de un usuario por ID
UPDATE usuarios
SET nombre = 'Nuevo Nombre'
WHERE id = 1;

-- c) eliminar usuarios con mas de un año de antiguedad
DELETE FROM usuarios
WHERE fecha_registro < NOW() - INTERVAL '1 year';
