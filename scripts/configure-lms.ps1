$ErrorActionPreference = "Stop"
Set-StrictMode -Version Latest

$TutorVersion = "4.0.5"
$StoreLocale = "pt_BR"
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

function Get-TutorLanguages {
    $LanguageOutput = & docker compose run --rm cli language plugin list tutor --format=json
    if ($LASTEXITCODE -ne 0) {
        throw "Nao foi possivel listar os pacotes de idioma do Tutor LMS."
    }

    try {
        return @(($LanguageOutput | Select-Object -Last 1).Trim() | ConvertFrom-Json)
    }
    catch {
        throw "A lista de pacotes de idioma do Tutor LMS e invalida."
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

    & docker compose run --rm cli core is-installed
    if ($LASTEXITCODE -ne 0) { throw "WordPress ainda nao esta instalado neste computador." }

    & docker compose run --rm cli plugin is-active coursepress-core
    if ($LASTEXITCODE -ne 0) { throw "O plugin coursepress-core nao esta ativo. Execute scripts/configure-store.ps1 antes de continuar." }

    & docker compose run --rm cli plugin is-active woocommerce
    if ($LASTEXITCODE -ne 0) { throw "WooCommerce nao esta ativo. Execute scripts/configure-store.ps1 antes de continuar." }

    & docker compose run --rm cli plugin is-installed tutor
    if ($LASTEXITCODE -ne 0) {
        Invoke-Wp plugin install tutor "--version=$TutorVersion" --activate
    }
    else {
        $InstalledVersionOutput = & docker compose run --rm cli plugin get tutor --field=version
        if ($LASTEXITCODE -ne 0) { throw "Nao foi possivel identificar a versao instalada do Tutor LMS." }

        if (($InstalledVersionOutput | Select-Object -Last 1).Trim() -ne $TutorVersion) {
            Invoke-Wp plugin install tutor "--version=$TutorVersion" --force --activate
        }
        else {
            Invoke-Wp plugin activate tutor
        }
    }

    $TutorVersionOutput = & docker compose run --rm cli plugin get tutor --field=version
    if ($LASTEXITCODE -ne 0) { throw "Nao foi possivel confirmar a versao instalada do Tutor LMS." }

    $InstalledTutorVersion = ($TutorVersionOutput | Select-Object -Last 1).Trim()
    if ($InstalledTutorVersion -ne $TutorVersion) { throw "Versao inesperada do Tutor LMS: $InstalledTutorVersion. Esperada: $TutorVersion." }

    $TutorLanguages = Get-TutorLanguages
    if ($null -eq ($TutorLanguages | Where-Object { $_.language -eq $StoreLocale } | Select-Object -First 1)) {
        throw "O pacote de idioma $StoreLocale do Tutor LMS nao esta disponivel."
    }

    Invoke-Wp language plugin install tutor $StoreLocale
    & docker compose run --rm cli language plugin is-installed tutor $StoreLocale
    if ($LASTEXITCODE -ne 0) { throw "O pacote de idioma $StoreLocale do Tutor LMS nao foi instalado." }

    $LocaleOutput = & docker compose run --rm cli eval "echo get_option('WPLANG') . '|' . get_locale() . '|' . determine_locale();"
    if ($LASTEXITCODE -ne 0) { throw "Nao foi possivel validar o locale do Tutor LMS." }

    $LocaleParts = (($LocaleOutput | Select-Object -Last 1).Trim()).Split("|")
    if ($LocaleParts.Count -ne 3 -or $LocaleParts[0] -ne $StoreLocale -or $LocaleParts[1] -ne $StoreLocale -or $LocaleParts[2] -ne $StoreLocale) {
        throw "O locale da loja nao foi configurado como $StoreLocale."
    }

    $AdminOutput = & docker compose run --rm cli user list --role=administrator --format=ids --skip-plugins --skip-themes
    if ($LASTEXITCODE -ne 0) { throw "Nao foi possivel localizar um usuario administrador." }

    $AdminId = ($AdminOutput | Select-Object -Last 1).Trim().Split(" ")[0]
    if ($AdminId -notmatch "^\d+$") { throw "Nenhum usuario administrador foi encontrado." }

    $LmsOutput = & docker compose run --rm cli eval-file wp-content/plugins/coursepress-core/cli/configure-lms.php "--user=$AdminId"
    if ($LASTEXITCODE -ne 0) { throw "Nao foi possivel configurar a fundacao do Tutor LMS." }

    try { $Lms = (($LmsOutput | Select-Object -Last 1).Trim() | ConvertFrom-Json) }
    catch { throw "O resultado da configuracao do Tutor LMS e invalido." }

    if ([string] $Lms.course.id -notmatch "^\d+$" -or $Lms.modules.Count -ne 3 -or $Lms.lessons.Count -ne 6 -or [string] $Lms.product.sku -ne "CPA-WP-NEGOCIOS-001" -or [string] $Lms.monetization -ne "wc") {
        throw "A fundacao do Tutor LMS nao retornou a estrutura esperada."
    }

    Invoke-Wp rewrite flush --hard

    Write-Host ""
    Write-Host "Fundacao do Tutor LMS configurada com sucesso." -ForegroundColor Green
    Write-Host "Tutor LMS: $InstalledTutorVersion"
    Write-Host "Curso ID: $($Lms.course.id)"
    Write-Host "Modulos: $($Lms.modules.Count) | Aulas: $($Lms.lessons.Count)"
    Write-Host "Monetizacao: WooCommerce"
}
finally {
    Pop-Location
}
