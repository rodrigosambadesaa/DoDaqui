CREATE TABLE IF NOT EXISTS usuarios (
	id_usuario INT AUTO_INCREMENT PRIMARY KEY,
	nome VARCHAR(120) NOT NULL,
	correo_electronico VARCHAR(160) NOT NULL UNIQUE,
	contrasinal VARCHAR(255) NOT NULL,
	rol_usuario ENUM('cliente', 'admin') NOT NULL DEFAULT 'cliente',
	creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO usuarios (nome, correo_electronico, contrasinal, rol_usuario)
VALUES (
	'Usuario Demo',
	'demo@tenda.gal',
	'$2y$10$nZ24rn1voj52FXBw4hezpOtXJAovRyHrNSVfv9zKIyKy5RrUbi2Z6',
	'cliente'
)
ON DUPLICATE KEY UPDATE
	nome = VALUES(nome),
	contrasinal = VALUES(contrasinal),
	rol_usuario = VALUES(rol_usuario);

CREATE TABLE IF NOT EXISTS carrito_items (
	id_item INT AUTO_INCREMENT PRIMARY KEY,
	id_usuario INT NOT NULL,
	id_produto VARCHAR(80) NOT NULL,
	nome_produto VARCHAR(150) NOT NULL,
	prezo_unitario DECIMAL(10,2) NOT NULL,
	cantidade INT NOT NULL DEFAULT 1,
	actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	UNIQUE KEY unique_user_product (id_usuario, id_produto),
	CONSTRAINT fk_carrito_usuario
		FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
		ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS pedidos (
	id_pedido INT AUTO_INCREMENT PRIMARY KEY,
	id_usuario INT NOT NULL,
	estado_pedido VARCHAR(32) NOT NULL DEFAULT 'confirmado',
	metodo_pagamento VARCHAR(24) NOT NULL,
	importe_subtotal DECIMAL(10,2) NOT NULL,
	importe_ive DECIMAL(10,2) NOT NULL,
	importe_total DECIMAL(10,2) NOT NULL,
	nome_facturacion VARCHAR(120) NOT NULL,
	enderezo_facturacion VARCHAR(200) NOT NULL,
	cidade_facturacion VARCHAR(120) NOT NULL,
	codigo_postal_facturacion VARCHAR(20) NOT NULL,
	pais_facturacion VARCHAR(80) NOT NULL,
	creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	CONSTRAINT fk_pedido_usuario
		FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
		ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS pedido_linas (
	id_lina INT AUTO_INCREMENT PRIMARY KEY,
	id_pedido INT NOT NULL,
	id_produto VARCHAR(80) NOT NULL,
	nome_produto VARCHAR(150) NOT NULL,
	prezo_unitario DECIMAL(10,2) NOT NULL,
	cantidade INT NOT NULL,
	CONSTRAINT fk_lina_pedido
		FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido)
		ON DELETE CASCADE
);
