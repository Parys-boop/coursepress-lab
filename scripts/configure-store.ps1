$ErrorActionPreference = "Stop"
Set-StrictMode -Version Latest

$WooCommerceVersion = "11.0.1"
$RepositoryRoot = Split-Path -Parent $PSScriptRoot
$StoreDescription = "Cursos pr$([char] 0x00E1)ticos para freelancers e pequenos neg$([char] 0x00F3)cios."
$StoreLocale = "pt_BR"

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

function Get-WooCommerceLanguages {
    $LanguageOutput = & docker compose run --rm cli language plugin list woocommerce --format=json
    if ($LASTEXITCODE -ne 0) {
        throw "Nao foi possivel listar os pacotes de idioma do WooCommerce."
    }

    $LanguageJson = ($LanguageOutput | Select-Object -Last 1).Trim()

    try {
        return @($LanguageJson | ConvertFrom-Json)
    }
    catch {
        throw "A lista de pacotes de idioma do WooCommerce e invalida."
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

    Invoke-Wp theme activate coursepress-lab
    Invoke-Wp plugin activate coursepress-core

    # Nao combine os fluxos nativos aqui. O Windows PowerShell 5.1 converte
    # o progresso do Docker Compose em NativeCommandError quando eles sao mesclados.
    & docker compose run --rm cli plugin is-installed woocommerce
    $WooCommerceInstalled = $LASTEXITCODE -eq 0

    if (-not $WooCommerceInstalled) {
        Invoke-Wp plugin install woocommerce "--version=$WooCommerceVersion" --activate
    }
    else {
        Invoke-Wp plugin activate woocommerce
    }

    $InstalledVersionOutput = & docker compose run --rm cli plugin get woocommerce --field=version
    if ($LASTEXITCODE -ne 0) {
        throw "Nao foi possivel identificar a versao instalada do WooCommerce."
    }

    $InstalledVersion = ($InstalledVersionOutput | Select-Object -Last 1).Trim()
    if ($InstalledVersion -ne $WooCommerceVersion) {
        throw "Versao inesperada do WooCommerce: $InstalledVersion. Esperada: $WooCommerceVersion."
    }

    $WooCommerceLanguages = Get-WooCommerceLanguages
    $PortugueseLanguage = $WooCommerceLanguages | Where-Object { $_.language -eq $StoreLocale } | Select-Object -First 1

    if ($null -eq $PortugueseLanguage) {
        throw "O pacote de idioma $StoreLocale do WooCommerce nao esta disponivel."
    }

    Invoke-Wp language core install $StoreLocale --activate
    Invoke-Wp language plugin install woocommerce $StoreLocale

    $Options = [ordered]@{
        blogname                                          = "CoursePress Academy"
        blogdescription                                   = $StoreDescription
        WPLANG                                            = $StoreLocale
        timezone_string                                   = "America/Sao_Paulo"
        date_format                                       = "d/m/Y"
        time_format                                       = "H:i"
        start_of_week                                     = "1"
        permalink_structure                               = "/%postname%/"
        woocommerce_currency                              = "BRL"
        woocommerce_currency_pos                          = "left_space"
        woocommerce_price_decimal_sep                     = ","
        woocommerce_price_thousand_sep                    = "."
        woocommerce_price_num_decimals                    = "2"
        woocommerce_default_country                       = "BR:SP"
        woocommerce_calc_taxes                            = "no"
        woocommerce_enable_coupons                        = "yes"
        woocommerce_enable_guest_checkout                 = "no"
        woocommerce_enable_signup_and_login_from_checkout = "yes"
        woocommerce_enable_myaccount_registration         = "yes"
        woocommerce_manage_stock                          = "no"
        woocommerce_coming_soon                            = "no"
        woocommerce_store_pages_only                       = "no"
        woocommerce_weight_unit                           = "kg"
        woocommerce_dimension_unit                        = "cm"
    }

    foreach ($Option in $Options.GetEnumerator()) {
        Invoke-Wp option update $Option.Key ([string] $Option.Value)
    }

    & docker compose run --rm cli language plugin is-installed woocommerce $StoreLocale
    if ($LASTEXITCODE -ne 0) {
        throw "O pacote de idioma $StoreLocale do WooCommerce nao foi instalado."
    }

    $LocaleOutput = & docker compose run --rm cli eval "echo get_option('WPLANG') . '|' . get_locale() . '|' . determine_locale();"
    if ($LASTEXITCODE -ne 0) {
        throw "Nao foi possivel validar o locale da loja."
    }

    $LocaleParts = (($LocaleOutput | Select-Object -Last 1).Trim()).Split("|")
    if ($LocaleParts.Count -ne 3 -or $LocaleParts[0] -ne $StoreLocale -or $LocaleParts[1] -ne $StoreLocale -or $LocaleParts[2] -ne $StoreLocale) {
        throw "O locale da loja nao foi configurado como $StoreLocale."
    }

    $AdminOutput = & docker compose run --rm cli user list --role=administrator --format=ids --skip-plugins --skip-themes
    if ($LASTEXITCODE -ne 0) {
        throw "Nao foi possivel localizar um usuario administrador."
    }

    $AdminId = ($AdminOutput | Select-Object -Last 1).Trim().Split(" ")[0]
    if ($AdminId -notmatch "^\d+$") {
        throw "Nenhum usuario administrador foi encontrado."
    }

    Invoke-Wp wc tool run install_pages "--user=$AdminId"

    $StorePages = @(
        "woocommerce_shop_page_id",
        "woocommerce_cart_page_id",
        "woocommerce_checkout_page_id",
        "woocommerce_myaccount_page_id"
    )

    foreach ($StorePageOption in $StorePages) {
        $PageIdOutput = & docker compose run --rm cli option get $StorePageOption
        if ($LASTEXITCODE -ne 0) {
            throw "Nao foi possivel localizar a pagina configurada em $StorePageOption."
        }

        $PageId = ($PageIdOutput | Select-Object -Last 1).Trim()
        if ($PageId -notmatch "^\d+$") {
            throw "A pagina configurada em $StorePageOption e invalida."
        }
    }

    $StorePagesValidationOutput = & docker compose run --rm cli eval-file wp-content/plugins/coursepress-core/cli/validate-store-pages.php
    if ($LASTEXITCODE -ne 0 -or (($StorePagesValidationOutput | Select-Object -Last 1).Trim() -ne "store-pages-valid")) {
        throw "As paginas transacionais oficiais nao passaram na validacao."
    }

    $LegalPagesOutput = & docker compose run --rm cli eval-file wp-content/plugins/coursepress-core/cli/configure-legal-pages.php "--user=$AdminId"
    if ($LASTEXITCODE -ne 0) {
        throw "Nao foi possivel configurar as paginas legais."
    }

    $LegalPagesJson = ($LegalPagesOutput | Select-Object -Last 1).Trim()
    try {
        $LegalPages = $LegalPagesJson | ConvertFrom-Json
    }
    catch {
        throw "O resultado da configuracao das paginas legais e invalido."
    }

    $PrivacyPageId = [string] $LegalPages.pages.privacy.id
    $TermsPageId = [string] $LegalPages.pages.terms.id
    if ($PrivacyPageId -notmatch "^\d+$" -or $TermsPageId -notmatch "^\d+$") {
        throw "A configuracao das paginas legais nao retornou IDs validos."
    }

    $LegalPagesValidationOutput = & docker compose run --rm cli eval-file wp-content/plugins/coursepress-core/cli/validate-legal-pages.php
    if ($LASTEXITCODE -ne 0) {
        throw "As paginas legais e suas opcoes nao passaram na validacao."
    }

    $LegalPagesValidationJson = ($LegalPagesValidationOutput | Select-Object -Last 1).Trim()
    try {
        $LegalPagesValidation = $LegalPagesValidationJson | ConvertFrom-Json
    }
    catch {
        throw "O resultado da validacao das paginas legais e invalido."
    }

    if (
        [string] $LegalPagesValidation.pages.privacy.id -ne $PrivacyPageId -or
        [string] $LegalPagesValidation.pages.terms.id -ne $TermsPageId
    ) {
        throw "As paginas legais validadas nao correspondem aos IDs configurados."
    }

    Invoke-Wp eval-file wp-content/plugins/coursepress-core/cli/configure-privacy-notices.php

    Invoke-Wp rewrite flush --hard

    # Compare bytes em hexadecimal para nao depender da codificacao usada
    # pelo Windows PowerShell 5.1 ao ler a saida UTF-8 de processos nativos.
    $ExpectedDescriptionHex = -join (
        [System.Text.Encoding]::UTF8.GetBytes($StoreDescription) |
            ForEach-Object { $_.ToString("x2") }
    )
    $StoredDescriptionHexOutput = & docker compose run --rm cli eval "echo bin2hex(get_option('blogdescription'));"
    if ($LASTEXITCODE -ne 0) {
        throw "Nao foi possivel validar a descricao da loja."
    }

    $StoredDescriptionHex = ($StoredDescriptionHexOutput | Select-Object -Last 1).Trim()
    if ($StoredDescriptionHex -ne $ExpectedDescriptionHex) {
        throw "A descricao da loja nao foi preservada corretamente."
    }

    Write-Host ""
    Write-Host "CoursePress Academy configurada com sucesso." -ForegroundColor Green
    Write-Host "WooCommerce: $InstalledVersion"
    Write-Host "Idioma: $StoreLocale"
    Write-Host "Visibilidade da loja: ativa"
    Write-Host "Moeda: BRL"
    Write-Host "Localizacao: Brasil / Sao Paulo"
    Write-Host "Descricao: $StoreDescription"
}
finally {
    Pop-Location
}
