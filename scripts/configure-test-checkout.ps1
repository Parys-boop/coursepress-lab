$ErrorActionPreference = "Stop"
Set-StrictMode -Version Latest

$RepositoryRoot = Split-Path -Parent $PSScriptRoot

function Invoke-Wp {
    param(
        [Parameter(Mandatory = $true, Position = 0, ValueFromRemainingArguments = $true)]
        [string[]] $WpArguments
    )

    Write-Host "wp $($WpArguments -join ' ')" -ForegroundColor DarkGray
    & docker compose run --rm -T cli @WpArguments

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

    $DockerInfo = & docker info --format '{{.ServerVersion}}'
    if ($LASTEXITCODE -ne 0 -or [string]::IsNullOrWhiteSpace(($DockerInfo | Select-Object -Last 1))) {
        throw "Docker Desktop nao esta disponivel. Inicie-o e tente novamente."
    }

    & docker compose config --quiet
    if ($LASTEXITCODE -ne 0) { throw "A configuracao do Docker Compose e invalida." }

    & docker compose up -d
    if ($LASTEXITCODE -ne 0) { throw "Nao foi possivel iniciar os servicos." }

    $CheckoutOutput = & docker compose run --rm -T cli eval-file wp-content/plugins/coursepress-core/cli/configure-test-checkout.php
    if ($LASTEXITCODE -ne 0) { throw "Nao foi possivel configurar o pagamento demonstrativo por Cheque." }

    try { $Checkout = (($CheckoutOutput | Select-Object -Last 1).Trim() | ConvertFrom-Json) }
    catch { throw "O resultado da configuracao do checkout demonstrativo e invalido." }

    if (@("configured", "unchanged") -notcontains [string] $Checkout.action -or [string] $Checkout.gateway -ne "cheque" -or [string] $Checkout.checkout -ne "block" -or [string] $Checkout.settings.enabled -ne "yes") {
        throw "O metodo Cheque demonstrativo nao retornou a configuracao esperada."
    }

    Write-Host ""
    Write-Host "Checkout demonstrativo configurado com sucesso." -ForegroundColor Green
    Write-Host "Metodo: $($Checkout.settings.title)"
    Write-Host "Resultado: $($Checkout.action)"
}
finally {
    Pop-Location
}
