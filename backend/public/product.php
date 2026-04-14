<?php

declare(strict_types=1);

session_start();
require_once __DIR__ . '/bootstrap.php';

$user = currentUser();
$productId = trim((string) ($_GET['id'] ?? ''));
$reviewError = '';
$reviewOk = (string) ($_GET['review'] ?? '') === 'ok';

$products = [
    [
        'id' => 'product-1',
        'name' => 'Tarro de miel ecológica',
        'meta' => 'Granja Abeja Feliz',
        'category' => 'alimentacion',
        'price' => 12.50,
        'summary' => 'Miel cruda de producción local, sin mezclas industriales y con cosecha de temporada.',
        'description' => 'Este tarro de miel se obtiene en colmenas de proximidad y conserva propiedades naturales al no someterse a procesos industriales.',
    ],
    [
        'id' => 'product-2',
        'name' => 'Cesta de mimbre artesanal',
        'meta' => 'Colectivo Artesano',
        'category' => 'artesania',
        'price' => 45.00,
        'summary' => 'Pieza trenzada a mano con fibras naturales, ideal para almacenaje y decoración.',
        'description' => 'Cada pieza está elaborada a mano con mimbre local y acabados resistentes para uso diario en el hogar.',
    ],
    [
        'id' => 'product-3',
        'name' => 'Aceite de oliva prensado en frío',
        'meta' => 'Valle del Sol',
        'category' => 'alimentacion',
        'price' => 18.00,
        'summary' => 'Aceite virgen extra de primera prensada, con perfil afrutado y acidez baja.',
        'description' => 'Aceite producido en almazara tradicional, con extracción en frío para preservar aroma, sabor y calidad nutricional.',
    ],
    [
        'id' => 'product-4',
        'name' => 'Pan de masa madre',
        'meta' => 'Panadería Local',
        'category' => 'alimentacion',
        'price' => 6.50,
        'summary' => 'Pan de fermentación lenta, corteza crujiente y miga alveolada elaborado cada mañana.',
        'description' => 'Pan horneado diariamente con fermentación natural y harinas seleccionadas de productores de la zona.',
    ],
    [
        'id' => 'product-5',
        'name' => 'Queso curado artesanal',
        'meta' => 'Lácteos da Serra',
        'category' => 'alimentacion',
        'price' => 15.20,
        'summary' => 'Queso curado de leche local con maduración lenta y sabor intenso.',
        'description' => 'Queso de producción limitada con maduración controlada y notas complejas para tabla o cocina gourmet.',
    ],
    [
        'id' => 'product-6',
        'name' => 'Mermelada de frutos rojos',
        'meta' => 'Huerta Atlántica',
        'category' => 'cuidado',
        'price' => 7.90,
        'summary' => 'Elaborada en pequeños lotes con fruta de temporada y bajo contenido de azúcar.',
        'description' => 'Mermelada artesana cocinada lentamente para conservar sabor y textura, perfecta para desayunos y repostería.',
    ],
];

$product = null;
foreach ($products as $candidate) {
    if ((string) $candidate['id'] === $productId) {
        $product = $candidate;
        break;
    }
}

if ($product === null) {
    http_response_code(404);
}

$opinions = [];

try {
    $pdo = db();
    ensureOpinionsSchema($pdo);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['action'] ?? '') === 'add_opinion') {
        if ($user === null) {
            $reviewError = 'Debes iniciar sesión para dejar una valoración.';
        } elseif ($product === null) {
            $reviewError = 'No se puede valorar un producto inexistente.';
        } else {
            $rating = (int) ($_POST['valoracion'] ?? 0);
            $comment = trim((string) ($_POST['opinion'] ?? ''));

            if ($rating < 1 || $rating > 5) {
                $reviewError = 'Selecciona una valoración entre 1 y 5.';
            } elseif (mb_strlen($comment) < 10) {
                $reviewError = 'La opinión debe tener al menos 10 caracteres.';
            } elseif (mb_strlen($comment) > 600) {
                $reviewError = 'La opinión no puede superar 600 caracteres.';
            } else {
                $insertOpinion = $pdo->prepare(
                    'INSERT INTO opinions_clientes (id_produto, id_cliente, valoracion, opinion)
                     VALUES (:id_produto, :id_cliente, :valoracion, :opinion)'
                );

                $insertOpinion->execute([
                    'id_produto' => (string) $product['id'],
                    'id_cliente' => (int) $user['id_usuario'],
                    'valoracion' => $rating,
                    'opinion' => $comment,
                ]);

                header('Location: /product.php?id=' . urlencode((string) $product['id']) . '&review=ok');
                exit;
            }
        }
    }

    if ($product !== null) {
        $opinionsQuery = $pdo->prepare(
            'SELECT o.valoracion, o.opinion, o.data_opinion, u.nome
             FROM opinions_clientes o
             INNER JOIN usuarios u ON u.id_usuario = o.id_cliente
             WHERE o.id_produto = :id_produto
             ORDER BY o.data_opinion DESC, o.id_opinion DESC
             LIMIT 20'
        );
        $opinionsQuery->execute(['id_produto' => (string) $product['id']]);
        $opinions = $opinionsQuery->fetchAll() ?: [];
    }
} catch (Throwable $exception) {
    if ($reviewError === '') {
        $reviewError = 'No se pudieron cargar las valoraciones. Inténtalo más tarde.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product ? safe((string) $product['name']) : 'Producto no encontrado'; ?> | DoDaqui</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>

<body>
    <div class="page-wrap">
        <div class="desktop-shell">
            <header class="top-nav">
                <a class="brand" href="/home.php">DoDaqui</a>
                <nav class="nav-links desktop-only">
                    <a href="/home.php">Inicio</a>
                    <a href="/products.php" class="is-active">Categorías</a>
                    <a href="/cart.php">Carrito</a>
                    <a href="/cart.php">Pedidos</a>
                </nav>
                <div class="nav-grow"></div>
                <div class="nav-actions">
                    <?php if ($user === null): ?>
                        <a class="login-link" href="/auth.php">Iniciar sesión</a>
                    <?php else: ?>
                        <span><?php echo safe((string) $user['nome']); ?></span>
                        <a href="/logout.php">Salir</a>
                    <?php endif; ?>
                    <a href="/cart.php" aria-label="Carrito">Carrito</a>
                    <span class="badge-count" id="cart-count">0</span>
                </div>
            </header>

            <main class="store-main">
                <?php if ($product === null): ?>
                    <section class="box">
                        <h2 class="catalog-title">Producto no encontrado</h2>
                        <p class="section-sub">No existe un producto con el identificador solicitado.</p>
                        <a class="btn btn-light" href="/products.php" style="margin-top: 10px;">Volver al catálogo</a>
                    </section>
                <?php else: ?>
                    <section class="box" style="display: grid; grid-template-columns: minmax(220px, 320px) minmax(0, 1fr); gap: 16px; align-items: start;">
                        <div class="product-thumb placeholder" style="height: 260px; margin-bottom: 0;"></div>
                        <div>
                            <p class="muted-xs" style="margin-bottom: 6px; text-transform: uppercase;"><?php echo safe((string) $product['category']); ?></p>
                            <h1 class="catalog-title" style="margin-bottom: 8px;"><?php echo safe((string) $product['name']); ?></h1>
                            <p class="product-meta" style="font-size: 12px; margin-bottom: 10px;"><?php echo safe((string) $product['meta']); ?></p>
                            <p style="font-size: 14px; margin-bottom: 10px;"><?php echo safe((string) $product['description']); ?></p>
                            <p class="section-sub" style="margin-bottom: 14px;"><?php echo safe((string) $product['summary']); ?></p>
                            <div class="product-row" style="max-width: 220px; margin-bottom: 10px;">
                                <span style="font-size: 18px;"><?php echo formatoEuro((float) $product['price']); ?></span>
                                <button
                                    class="plus-btn add-cart"
                                    type="button"
                                    data-id="<?php echo safe((string) $product['id']); ?>"
                                    data-name="<?php echo safe((string) $product['name']); ?>"
                                    data-price="<?php echo safe((string) $product['price']); ?>">+</button>
                            </div>
                            <a class="btn btn-light" href="/products.php">Volver al catálogo</a>
                        </div>
                    </section>

                    <section class="box" style="margin-top: 14px;">
                        <div style="display: flex; justify-content: space-between; gap: 10px; align-items: center; margin-bottom: 10px;">
                            <h3 style="margin: 0;">Valoraciones del producto</h3>
                            <a class="muted-xs" href="/opinions.php?producto=<?php echo urlencode((string) $product['id']); ?>">Ver todas</a>
                        </div>

                        <?php if ($reviewOk): ?>
                            <p class="section-sub" style="color: #2f5d38;">Gracias, tu valoración se ha guardado correctamente.</p>
                        <?php endif; ?>

                        <?php if ($reviewError !== ''): ?>
                            <p class="section-sub" style="color: #8b3a3a;"><?php echo safe($reviewError); ?></p>
                        <?php endif; ?>

                        <?php if ($user === null): ?>
                            <p class="section-sub">Inicia sesión para dejar una valoración.</p>
                            <a class="btn btn-light" href="/auth.php">Iniciar sesión</a>
                        <?php else: ?>
                            <form method="post" action="/product.php?id=<?php echo urlencode((string) $product['id']); ?>" class="review-form" style="margin-bottom: 12px;">
                                <input type="hidden" name="action" value="add_opinion">
                                <div class="form-grid-2">
                                    <div class="form-group">
                                        <label for="valoracion">Valoración</label>
                                        <select id="valoracion" name="valoracion" required>
                                            <option value="">Selecciona</option>
                                            <option value="5">5 - Excelente</option>
                                            <option value="4">4 - Muy buena</option>
                                            <option value="3">3 - Buena</option>
                                            <option value="2">2 - Mejorable</option>
                                            <option value="1">1 - Mala</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group" style="margin-top: 8px;">
                                    <label for="opinion">Tu opinión</label>
                                    <textarea id="opinion" name="opinion" rows="3" maxlength="600" required placeholder="Cuéntanos tu experiencia con este producto"></textarea>
                                </div>
                                <button class="btn btn-dark" style="margin-top: 10px;" type="submit">Enviar valoración</button>
                            </form>
                        <?php endif; ?>

                        <?php if (count($opinions) === 0): ?>
                            <p class="section-sub">Todavía no hay opiniones para este producto.</p>
                        <?php else: ?>
                            <div class="review-list">
                                <?php foreach ($opinions as $entry): ?>
                                    <?php
                                    $stars = str_repeat('★', (int) ($entry['valoracion'] ?? 0)) . str_repeat('☆', max(0, 5 - (int) ($entry['valoracion'] ?? 0)));
                                    ?>
                                    <article class="review-item">
                                        <div style="display: flex; justify-content: space-between; gap: 8px; align-items: center;">
                                            <strong><?php echo safe((string) ($entry['nome'] ?? 'Cliente')); ?></strong>
                                            <span class="muted-xs"><?php echo safe((string) date('d/m/Y', strtotime((string) ($entry['data_opinion'] ?? 'now')))); ?></span>
                                        </div>
                                        <p class="review-stars"><?php echo safe($stars); ?></p>
                                        <p><?php echo safe((string) ($entry['opinion'] ?? '')); ?></p>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="assets/app.js" defer></script>
</body>

</html>