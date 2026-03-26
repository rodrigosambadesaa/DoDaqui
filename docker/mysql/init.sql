-- Tabla de usuarios: almacena identidad, credenciales hash y rol básico.
CREATE TABLE IF NOT EXISTS usuarios (
  id_usuario INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  contrasinal_hash VARCHAR(255) NOT NULL,
  rol ENUM('cliente', 'admin') DEFAULT 'cliente',
  data_alta TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Catálogo de categorías navegables en frontend.
CREATE TABLE IF NOT EXISTS categorias (
  id_categoria INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  slug VARCHAR(120) NOT NULL UNIQUE
);

-- Productores locales asociados a productos.
CREATE TABLE IF NOT EXISTS produtores (
  id_produtor INT AUTO_INCREMENT PRIMARY KEY,
  nome_comercial VARCHAR(120) NOT NULL,
  concello VARCHAR(100) NOT NULL
);

-- Productos comercializados. prezo_kg permite venta por peso cuando no es NULL.
CREATE TABLE IF NOT EXISTS produtos (
  id_produto INT AUTO_INCREMENT PRIMARY KEY,
  id_produtor INT NOT NULL,
  id_categoria INT NOT NULL,
  nome VARCHAR(150) NOT NULL,
  slug VARCHAR(150) NOT NULL UNIQUE,
  descricion_curta VARCHAR(255) NOT NULL,
  prezo DECIMAL(10,2) NOT NULL,
  prezo_kg DECIMAL(10,2) NULL,
  stock INT DEFAULT 0,
  FOREIGN KEY (id_produtor) REFERENCES produtores(id_produtor),
  FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
);

-- Carritos de compra por usuario y estado.
CREATE TABLE IF NOT EXISTS carros (
  id_carro INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  estado ENUM('activo', 'convertido', 'abandonado') DEFAULT 'activo',
  data_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

-- Líneas del carrito (cantidad decimal para soportar productos por kg).
CREATE TABLE IF NOT EXISTS carro_linas (
  id_carro_lina INT AUTO_INCREMENT PRIMARY KEY,
  id_carro INT NOT NULL,
  id_produto INT NOT NULL,
  cantidade DECIMAL(10,3) NOT NULL,
  prezo_unitario DECIMAL(10,2) NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  UNIQUE KEY uniq_carro_produto (id_carro, id_produto),
  FOREIGN KEY (id_carro) REFERENCES carros(id_carro),
  FOREIGN KEY (id_produto) REFERENCES produtos(id_produto)
);

-- Cabecera de pedidos ya confirmados.
CREATE TABLE IF NOT EXISTS pedidos (
  id_pedido INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  codigo_pedido VARCHAR(50) NOT NULL UNIQUE,
  estado ENUM('pendente', 'preparacion', 'enviado', 'entregado', 'cancelado') DEFAULT 'pendente',
  importe_total DECIMAL(10,2) NOT NULL,
  data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

-- Líneas históricas de pedidos con snapshot de nombre y precio.
CREATE TABLE IF NOT EXISTS pedido_linas (
  id_pedido_lina INT AUTO_INCREMENT PRIMARY KEY,
  id_pedido INT NOT NULL,
  id_produto INT NOT NULL,
  nome_produto_snapshot VARCHAR(150) NOT NULL,
  prezo_unitario DECIMAL(10,2) NOT NULL,
  cantidade DECIMAL(10,3) NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido),
  FOREIGN KEY (id_produto) REFERENCES produtos(id_produto)
);

-- Datos base de categorías para primer arranque.
INSERT INTO categorias (nome, slug) VALUES
('Verduras', 'verduras'),
('Lacteos', 'lacteos'),
('Conservas', 'conservas')
ON DUPLICATE KEY UPDATE nome = VALUES(nome);

-- Productores de demostración.
INSERT INTO produtores (nome_comercial, concello) VALUES
('Horta da Ria', 'Vilagarcia'),
('Leite do Monte', 'Lugo'),
('Conservas do Mar', 'Bueu');

-- Productos semilla del prototipo.
INSERT INTO produtos (id_produtor, id_categoria, nome, slug, descricion_curta, prezo, prezo_kg, stock) VALUES
(1, 1, 'Tomate Eco', 'tomate-eco', 'Tomate galego de temporada.', 2.50, 4.95, 120),
(2, 2, 'Queixo Artesan', 'queixo-artesan', 'Queixo curado de leite cru.', 6.90, 19.90, 45),
(3, 3, 'Mexillon en escabeche', 'mexillon-escabeche', 'Conserva tradicional galega.', 4.20, NULL, 80)
ON DUPLICATE KEY UPDATE nome = VALUES(nome), prezo = VALUES(prezo), prezo_kg = VALUES(prezo_kg), stock = VALUES(stock);

-- Usuarios de prueba: contraseña demo123 con hash bcrypt.
INSERT INTO usuarios (nome, email, contrasinal_hash, rol) VALUES
('Admin', 'admin@tenda.gal', '$2y$10$yfdhTI5ZJviLV0VLFmiIyuakYfwMglU8x7C4Gsi.OxoZ4PF/fRj0q', 'admin'),
('Cliente Demo', 'demo@tenda.gal', '$2y$10$yfdhTI5ZJviLV0VLFmiIyuakYfwMglU8x7C4Gsi.OxoZ4PF/fRj0q', 'cliente')
ON DUPLICATE KEY UPDATE
  nome = VALUES(nome),
  contrasinal_hash = VALUES(contrasinal_hash),
  rol = VALUES(rol);

-- Garantiza que el usuario demo disponga de carrito activo al inicializar.
INSERT INTO carros (id_usuario, estado)
SELECT u.id_usuario, 'activo' FROM usuarios u
WHERE u.email = 'demo@tenda.gal'
AND NOT EXISTS (
  SELECT 1 FROM carros c WHERE c.id_usuario = u.id_usuario AND c.estado = 'activo'
);
