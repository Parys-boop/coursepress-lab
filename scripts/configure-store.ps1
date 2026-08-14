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

    $null = & docker info 2>&1
    if ($LASTEXITCODE -ne 0) {
        throw "Docker Desktop não está disponível. Inicie-o e tente novamente."
    }

    & docker compose config --quiet
    if ($LASTEXITCODE -ne 0) {
        throw "A configuração do Docker Compose é inválida."
    }

    & docker compose up -d
    if ($LASTEXITCODE -ne 0) {
        throw "Não foi possível iniciar os serviços."
    }

    & docker compose run --rm cli core is-installed
    if ($LASTEXITCODE -ne 0) {
        throw "WordPress ainda não está instalado neste computador."
    }

    Invoke-Wp theme activate coursepress-lab
    Invoke-Wp plugin activate coursepress-core

    & docker compose run --rm cli plugin is-installed woocommerce *> $null
    if ($LASTEXITCODE -ne 0) {
        Invoke-Wp plugin install woocommerce "--version=$WooCommerceVersion" --activate
    }
    else {
        Invoke-Wp plugin activate woocommerce
    }

    $InstalledVersionOutput = & docker compose run --rm cli plugin get woocommerce --field=version
    if ($LASTEXITCODE -ne 0) {
        throw "Não foi possível identificar a versão instalada do WooCommerce."
    }

    $InstalledVersion = ($InstalledVersionOutput | Select-Object -Last 1).Trim()
    if ($InstalledVersion -ne $WooCommerceVersion) {
        throw "Versão inesperada do WooCommerce: $InstalledVersion. Esperada: $WooCommerceVersion."
    }

    $Options = [ordered]@{
        blogname                                          = "CoursePress Academy"
        blogdescription                                   = "Cursos práticos para freelancers e pequenos negócios."
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
        woocommerce_enable_guest_checkout                 = "yes"
        woocommerce_enable_signup_and_login_from_checkout = "yes"
        woocommerce_enable_myaccount_registration         = "yes"
        woocommerce_manage_stock                          = "no"
        woocommerce_weight_unit                           = "kg"
        woocommerce_dimension_unit                        = "cm"
    }

    foreach ($Option in $Options.GetEnumerator()) {
        Invoke-Wp option update $Option.Key ([string] $Option.Value)
    }

    $AdminOutput = & docker compose run --rm cli user list --role=administrator --format=ids --skip-plugins --skip-themes
    if ($LASTEXITCODE -ne 0) {
        throw "Não foi possível localizar um usuário administrador."
    }

    $AdminId = ($AdminOutput | Select-Object -Last 1).Trim().Split(" ")[0]
    if ($AdminId -notmatch "^\d+$") {
        throw "Nenhum usuário administrador foi encontrado."
    }

    Invoke-Wp wc tool run install_pages "--user=$AdminId"
    Invoke-Wp rewrite flush

    Write-Host ""
    Write-Host "CoursePress Academy configurada com sucesso." -ForegroundColor Green
    Write-Host "WooCommerce: $InstalledVersion"
    Write-Host "Moeda: BRL"
    Write-Host "Localização: Brasil / São Paulo"
}
finally {
    Pop-Location
}
