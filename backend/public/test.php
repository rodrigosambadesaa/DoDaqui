<?php
session_start();

// Información del servidor
echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>DoDaquí - Test Server</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #f4f5f7 0%, #e7eaee 100%);
            min-height: 100vh;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }
        h1 {
            color: #1a1a1a;
            margin-top: 0;
        }
        .status {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-weight: 500;
        }
        .success {
            background-color: #edf1f4;
            color: #2d6a4f;
            border-left: 4px solid #52b788;
        }
        .warning {
            background-color: #fff3e0;
            color: #e65100;
            border-left: 4px solid #ffb74d;
        }
        .nav {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e0e0e0;
        }
        .nav a {
            display: inline-block;
            padding: 10px 20px;
            margin-right: 12px;
            margin-bottom: 12px;
            background-color: #1a1a1a;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background-color 0.3s ease;
        }
        .nav a:hover {
            background-color: #c0a080;
        }
        .info {
            background-color: #f5f5f5;
            padding: 12px;
            border-radius: 6px;
            margin: 12px 0;
            font-size: 14px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🚀 DoDaquí v1.0.0 - Servidor Local</h1>
        
        <div class='status success'>
            ✅ Servidor PHP iniciado correctamente
        </div>
        
        <div class='info'>
            <strong>PHP Version:</strong> " . phpversion() . "
        </div>
        
        <div class='info'>
            <strong>Servidor:</strong> localhost:8080<br>
            <strong>Directorio:</strong> " . getcwd() . "
        </div>
        
        <div class='info'>
            <strong>Session:</strong> " . (isset(\$_SESSION['user']) ? 'Activa' : 'Sin usuario') . "
        </div>
        
        <div class='status warning'>
            ⚠️ Base de datos: Desconectada (modo demo)<br>
            Para funcionalidad completa, instala Docker o configura MySQL localmente.
        </div>
        
        <div class='nav'>
            <h3 style='margin-top: 0; color: #1a1a1a;'>Navega a:</h3>
            <a href='/home.php'>🏠 Home</a>
            <a href='/cart.php'>🛒 Cart</a>
            <a href='/checkout.php'>💳 Checkout</a>
        </div>
    </div>
</body>
</html>";
?>
