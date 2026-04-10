CREATE TABLE IF NOT EXISTS usuarios (
	id_usuario INT AUTO_INCREMENT PRIMARY KEY,
	nome VARCHAR(120) NOT NULL,
	email VARCHAR(160) NOT NULL UNIQUE,
	contrasinal VARCHAR(255) NOT NULL,
	rol ENUM('cliente', 'admin') NOT NULL DEFAULT 'cliente',
	creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO usuarios (nome, email, contrasinal, rol)
VALUES (
	'Usuario Demo',
	'demo@tenda.gal',
	'$2y$10$nZ24rn1voj52FXBw4hezpOtXJAovRyHrNSVfv9zKIyKy5RrUbi2Z6',
	'cliente'
)
ON DUPLICATE KEY UPDATE
	nome = VALUES(nome),
	contrasinal = VALUES(contrasinal),
	rol = VALUES(rol);
