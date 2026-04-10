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

CREATE TABLE IF NOT EXISTS carrito_items (
	id_item INT AUTO_INCREMENT PRIMARY KEY,
	id_usuario INT NOT NULL,
	product_id VARCHAR(80) NOT NULL,
	name VARCHAR(150) NOT NULL,
	price DECIMAL(10,2) NOT NULL,
	quantity INT NOT NULL DEFAULT 1,
	actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	UNIQUE KEY unique_user_product (id_usuario, product_id),
	CONSTRAINT fk_carrito_usuario
		FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
		ON DELETE CASCADE
);
