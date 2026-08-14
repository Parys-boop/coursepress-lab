$ErrorActionPreference = "Stop"
Set-StrictMode -Version Latest

$WooCommerceVersion = "11.0.1"
$RepositoryRoot = Split-Path -Parent $PSScriptRoot

function Invoke-Wp {
    param(
        [Parameter(Mandatory = $true, Position = 0, ValueFromRemainingArguments = $true)]
        [string[]] $WpArguments
    )

    Write-Host "wp $($WpArguments -join ' ')" -ForegroundColor DarkGray
    & docker compose run --rm cli @WpArguments

    if ($LASTEXITCODE -ne 0) {
        throw "O comando WP-CLI falhou: wp $($WpArguments -join ' ')"
    }
}

Push-Location $RepositoryRoot

try {
    if (-not (Test-Path ".env")) {
        throw "Arquivo .env ausente. Copie .env.example para .env antes de continuar."
    }

    if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
        throw "Docker CLI nao esta disponivel. Instale ou inicie o Docker Desktop e tente novamente."
    }

    $null = & docker info 2>&1
    if ($LASTEXITCODE -ne 0) {
        throw "Docker Desktop nao esta disponivel. Inicie-o e tente novamente."
    }

    & docker compose config --quiet
    if ($LASTEXITCODE -ne 0) {
        throw "A configuracao do Docker Compose e invalida."
    }

    & docker compose up -d
    if ($LASTEXITCODE -ne 0) {
        throw "Nao foi possivel iniciar os servicos."
    }

    & docker compose run --rm cli core is-installed
    if ($LASTEXITCODE -ne 0) {
        throw "WordPress ainda nao esta instalado neste computador."
    }

    & docker compose run --rm cli plugin is-active woocommerce
    if ($LASTEXITCODE -ne 0) {
        throw "WooCommerce nao esta ativo. Execute scripts/configure-store.ps1 antes de continuar."
    }

    $InstalledVersionOutput = & docker compose run --rm cli plugin get woocommerce --field=version
    if ($LASTEXITCODE -ne 0) {
        throw "Nao foi possivel identificar a versao instalada do WooCommerce."
    }

    $InstalledVersion = ($InstalledVersionOutput | Select-Object -Last 1).Trim()
    if ($InstalledVersion -ne $WooCommerceVersion) {
        throw "Versao inesperada do WooCommerce: $InstalledVersion. Esperada: $WooCommerceVersion."
    }

    $AdminOutput = & docker compose run --rm cli user list --role=administrator --format=ids --skip-plugins --skip-themes
    if ($LASTEXITCODE -ne 0) {
        throw "Nao foi possivel localizar um usuario administrador."
    }

    $AdminId = ($AdminOutput | Select-Object -Last 1).Trim().Split(" ")[0]
    if ($AdminId -notmatch "^\d+$") {
        throw "Nenhum usuario administrador foi encontrado."
    }

    Invoke-Wp eval-file wp-content/plugins/coursepress-core/cli/configure-demo-product.php --use-include "--user=$AdminId"
}
finally {
    Pop-Location
}
