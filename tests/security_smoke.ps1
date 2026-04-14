param(
    [string]$BaseUrl = 'http://localhost:8080'
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

function Assert-True {
    param(
        [bool]$Condition,
        [string]$Message
    )

    if (-not $Condition) {
        throw "[FAIL] $Message"
    }

    Write-Host "[OK] $Message"
}

function Get-HiddenCsrfToken {
    param([string]$Html)

    $pattern = 'name="csrf_token"\s+value="([^"]+)"'
    $match = [regex]::Match($Html, $pattern)
    if (-not $match.Success) {
        throw '[FAIL] No se encontró csrf_token en formulario.'
    }

    return $match.Groups[1].Value
}

function Get-MetaCsrfToken {
    param([string]$Html)

    $pattern = '<meta\s+name="csrf-token"\s+content="([^"]+)"'
    $match = [regex]::Match($Html, $pattern)
    if (-not $match.Success) {
        throw '[FAIL] No se encontró meta csrf-token.'
    }

    return $match.Groups[1].Value
}

Write-Host "Iniciando smoke tests de seguridad en $BaseUrl"

$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession

$homeResponse = Invoke-WebRequest -Uri "$BaseUrl/home.php" -WebSession $session
Assert-True ($homeResponse.StatusCode -eq 200) 'Home responde 200'

$linkMatches = [regex]::Matches($homeResponse.Content, 'href="(/[^"#?]+(?:\?[^"#]*)?)"')
$links = @{}
foreach ($m in $linkMatches) {
    $path = $m.Groups[1].Value
    if ($path -like '/logout.php*') { continue }
    $links[$path] = $true
}

foreach ($path in $links.Keys) {
    $response = Invoke-WebRequest -Uri "$BaseUrl$path" -WebSession $session
    Assert-True ($response.StatusCode -ge 200 -and $response.StatusCode -lt 400) "Enlace accesible: $path"
}

$auth = Invoke-WebRequest -Uri "$BaseUrl/auth.php" -WebSession $session
$csrfAuth = Get-HiddenCsrfToken -Html $auth.Content

$random = Get-Random -Minimum 100000 -Maximum 999999
$email = "security$random@example.com"
$password = 'SecurePass123!'

$registerBody = @{
    csrf_token = $csrfAuth
    action     = 'register'
    name       = 'Usuario Seguridad'
    email      = $email
    password   = $password
}
$register = Invoke-WebRequest -Uri "$BaseUrl/auth.php" -Method Post -WebSession $session -Body $registerBody
Assert-True ($register.StatusCode -eq 200) 'Registro responde correctamente'
Assert-True ($register.Content -match 'Registro correcto|correo ya está registrado') 'Registro procesado'

$auth2 = Invoke-WebRequest -Uri "$BaseUrl/auth.php" -WebSession $session
$csrfAuth2 = Get-HiddenCsrfToken -Html $auth2.Content
$loginBody = @{
    csrf_token = $csrfAuth2
    action     = 'login'
    email      = $email
    password   = $password
}
$login = Invoke-WebRequest -Uri "$BaseUrl/auth.php" -Method Post -WebSession $session -Body $loginBody -MaximumRedirection 5
Assert-True ($login.StatusCode -ge 200 -and $login.StatusCode -lt 400) 'Login ejecutado'

$homeLogged = Invoke-WebRequest -Uri "$BaseUrl/home.php" -WebSession $session
$csrfMeta = Get-MetaCsrfToken -Html $homeLogged.Content

$addPayload = '{"id":"product-1","name":"Tarro de miel ecológica","price":12.5}'
$addHeaders = @{
    'Content-Type' = 'application/json'
    'X-CSRF-Token' = $csrfMeta
}
$addRes = Invoke-WebRequest -Uri "$BaseUrl/cart_api.php?action=add" -Method Post -WebSession $session -Headers $addHeaders -Body $addPayload
$addJson = $addRes.Content | ConvertFrom-Json
Assert-True ($addJson.ok -eq $true) 'Añadir al carrito funciona con CSRF válido'

$csrfFailHeaders = @{
    'Content-Type' = 'application/json'
    'X-CSRF-Token' = 'invalid-token'
}
$csrfFailRes = Invoke-WebRequest -Uri "$BaseUrl/cart_api.php?action=add" -Method Post -WebSession $session -Headers $csrfFailHeaders -Body $addPayload -SkipHttpErrorCheck
$csrfRejected = ($csrfFailRes.Content -match 'Token CSRF inválido o ausente') -or ($csrfFailRes.Content -match '"ok"\s*:\s*false')
Assert-True $csrfRejected 'API carrito rechaza petición sin CSRF'

$cartPage = Invoke-WebRequest -Uri "$BaseUrl/cart.php" -WebSession $session
$csrfCheckout = Get-HiddenCsrfToken -Html $cartPage.Content
$checkoutPairs = @{
    csrf_token = $csrfCheckout
    action = 'realizar_pedido'
    nome_facturacion = 'Cliente Prueba'
    correo_cliente = $email
    telefono_cliente = '+34600111222'
    enderezo_facturacion = 'Rua Test 123'
    cidade_facturacion = 'Santiago'
    codigo_postal_facturacion = '15701'
    pais_facturacion = 'España'
    observacions = 'Entrega por la mañana'
}

$checkoutBody = ($checkoutPairs.GetEnumerator() | ForEach-Object {
    [uri]::EscapeDataString([string]$_.Key) + '=' + [uri]::EscapeDataString([string]$_.Value)
}) -join '&'

$checkoutHeaders = @{ 'Content-Type' = 'application/x-www-form-urlencoded;charset=UTF-8' }
$checkoutRes = Invoke-WebRequest -Uri "$BaseUrl/checkout.php" -Method Post -WebSession $session -Headers $checkoutHeaders -Body $checkoutBody
$checkoutJson = $checkoutRes.Content | ConvertFrom-Json
Assert-True ($checkoutJson.ok -eq $true) 'Checkout completo con CSRF y validaciones'

$abuseSession = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$abuseAuth = Invoke-WebRequest -Uri "$BaseUrl/auth.php" -WebSession $abuseSession
$abuseToken = Get-HiddenCsrfToken -Html $abuseAuth.Content

$loginLimited = $false
for ($i = 1; $i -le 11; $i++) {
    $abuseLoginBody = @{
        csrf_token = $abuseToken
        action     = 'login'
        email      = 'abuse-login@example.com'
        password   = 'wrong-pass'
    }
    $abuseLoginRes = Invoke-WebRequest -Uri "$BaseUrl/auth.php" -Method Post -WebSession $abuseSession -Body $abuseLoginBody
    if ($abuseLoginRes.Content -match 'Demasiados intentos de inicio de sesión') {
        $loginLimited = $true
        break
    }
}
Assert-True $loginLimited 'Rate limiter de login activo'

$registerLimited = $false
for ($j = 1; $j -le 4; $j++) {
    $abuseRegisterBody = @{
        csrf_token = $abuseToken
        action     = 'register'
        name       = 'Bot Ataque'
        email      = 'abuse-register@example.com'
        password   = 'SecurePass123!'
    }
    $abuseRegisterRes = Invoke-WebRequest -Uri "$BaseUrl/auth.php" -Method Post -WebSession $abuseSession -Body $abuseRegisterBody
    if ($abuseRegisterRes.Content -match 'Demasiados intentos de registro') {
        $registerLimited = $true
        break
    }
}
Assert-True $registerLimited 'Rate limiter de registro activo'

Write-Host 'Smoke tests de seguridad finalizados correctamente.'
