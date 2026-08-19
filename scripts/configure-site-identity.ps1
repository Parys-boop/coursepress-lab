$ErrorActionPreference = "Stop"
Set-StrictMode -Version Latest

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
        throw "Docker CLI nao esta disponivel. Instale ou inicie o mecanismo Docker e tente novamente."
    }

    $null = & docker info 2>&1
    if ($LASTEXITCODE -ne 0) {
        throw "O mecanismo Docker nao esta disponivel. Inicie-o e tente novamente."
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

    & docker compose run --rm cli theme is-active coursepress-lab
    if ($LASTEXITCODE -ne 0) {
        throw "O tema coursepress-lab nao esta ativo. Execute scripts/configure-store.ps1 antes de continuar."
    }

    & docker compose run --rm cli plugin is-active coursepress-core
    if ($LASTEXITCODE -ne 0) {
        throw "O plugin coursepress-core nao esta ativo. Execute scripts/configure-store.ps1 antes de continuar."
    }

    & docker compose run --rm cli plugin is-active woocommerce
    if ($LASTEXITCODE -ne 0) {
        throw "WooCommerce nao esta ativo. Execute scripts/configure-store.ps1 antes de continuar."
    }

    $AdminOutput = & docker compose run --rm cli user list --role=administrator --format=ids --skip-plugins --skip-themes
    if ($LASTEXITCODE -ne 0) {
        throw "Nao foi possivel localizar um usuario administrador."
    }

    $AdminId = ($AdminOutput | Select-Object -Last 1).Trim().Split(" ")[0]
    if ($AdminId -notmatch "^\d+$") {
        throw "Nenhum usuario administrador foi encontrado."
    }

    Invoke-Wp eval-file wp-content/plugins/coursepress-core/cli/configure-site-identity.php --use-include "--user=$AdminId"
}
finally {
    Pop-Location
}
