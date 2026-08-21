# CoursePress Lab

Ambiente WordPress de portfólio para aprender e demonstrar a venda e a entrega de cursos online com práticas próximas de um projeto comercial.

## Objetivo

Construir uma solução reproduzível que reúna:

- landing page de venda;
- WooCommerce e checkout em ambiente de testes;
- área de membros e curso;
- integrações de marketing e análise;
- tema e plugin próprios;
- testes, documentação, desempenho, backup e segurança.

> O projeto é educacional e não processa pagamentos reais durante o desenvolvimento.

## Tecnologias iniciais

- WordPress 6.9;
- PHP 8.3 com Apache;
- MariaDB 10.11;
- Docker Compose;
- WP-CLI;
- WooCommerce 11.0.1;
- Tutor LMS 4.0.5;
- tema `coursepress-lab`;
- plugin `coursepress-core`.

## Pré-requisitos

- Git;
- Docker Desktop com o Engine Linux em execução;
- Docker Compose.

Não é necessário instalar PHP ou MariaDB diretamente no computador.

## Como executar

### 1. Preparar as variáveis locais

No PowerShell:

```powershell
Copy-Item .env.example .env
```

No Bash/WSL:

```bash
cp .env.example .env
```

O arquivo `.env` é local e não deve ser enviado ao GitHub.

### 2. Subir o ambiente

```bash
docker compose up -d
```

Na primeira execução, o Docker baixará as imagens e preparará o banco. Isso pode levar alguns minutos.

### 3. Conferir os serviços

```bash
docker compose ps
```

Quando o banco estiver saudável e o WordPress estiver em execução, acesse:

- site: http://localhost:8080
- instalação/administração: http://localhost:8080/wp-admin

Selecione **Português do Brasil** durante a instalação.

### 4. Configurar a base da loja

Depois de instalar o WordPress, execute no PowerShell:

```powershell
.\scripts\configure-store.ps1
```

O script é idempotente e pode ser executado novamente. Ele valida o ambiente, ativa o código autoral, instala a versão definida do WooCommerce e configura localização, moeda, formatos brasileiros, checkout, permalinks e as páginas transacionais oficiais. Títulos, slugs e conteúdos dessas páginas não são reescritos pela automação.

Também configura as páginas publicadas de Política de Privacidade e Termos e Condições para este ambiente educacional e demonstrativo. Essas páginas usam marcadores e hashes para impedir sobrescrita de conteúdo alterado manualmente; a Política de Privacidade padrão do WordPress só é reatribuída quando ainda é um rascunho oficial sem alterações.

### 5. Criar o produto demonstrativo

Depois de configurar a loja, execute no PowerShell:

```powershell
.\scripts\configure-demo-product.ps1
```

O script cria ou atualiza exclusivamente o produto demonstrativo gerenciado pela CoursePress Academy. Ele pode ser executado novamente sem duplicar produto ou categoria e mantém o produto publicado na loja demonstrativa.

### 6. Configurar a identidade e a navegação

Depois de configurar a loja, execute no PowerShell:

```powershell
.\scripts\configure-site-identity.ps1
```

No WSL/Linux com PowerShell 7:

```bash
pwsh -NoProfile -File ./scripts/configure-site-identity.ps1
```

O script cria ou atualiza a página Início e o menu Navegação principal. Ele protege recursos não gerenciados e pode ser executado novamente sem duplicar páginas ou itens de menu.

### 7. Configurar a fundação do curso

Depois de criar o produto demonstrativo, execute no PowerShell:

```powershell
.\scripts\configure-lms.ps1
```

O script instala o Tutor LMS na versão definida, instala sua tradução pt_BR e cria uma única trilha demonstrativa vinculada ao produto `CPA-WP-NEGOCIOS-001`. A primeira aula é uma prévia pública; as demais dependem de matrícula. O marco não configura pagamentos, matrícula automática ou recursos Pro.

### 8. Configurar o checkout demonstrativo

Depois de configurar a fundação do curso, execute no PowerShell:

```powershell
.\scripts\configure-test-checkout.ps1
```

No WSL/Linux com PowerShell 7:

```bash
pwsh -NoProfile -File ./scripts/configure-test-checkout.ps1
```

O script só funciona quando `WP_ENVIRONMENT_TYPE` é `local`. Ele habilita de forma idempotente o método nativo **Cheque** do WooCommerce com textos explícitos de demonstração. Esse método não processa pagamentos reais: o pedido fica aguardando aprovação manual e o administrador deve alterá-lo para **Concluído** no WooCommerce. O Tutor LMS 4.0.5 então realiza a matrícula pela integração nativa, sem código próprio de matrícula.

O configurador não desabilita outros meios de pagamento. Se encontrar uma configuração diferente e desconhecida para Cheque, ele falha sem sobrescrevê-la.

### 9. Conhecer a landing page

A página inicial é uma landing page responsiva construída no tema `coursepress-lab`, sem page builder. Ela busca o curso vinculado ao produto de SKU `CPA-WP-NEGOCIOS-001`, apresenta o preço e a grade reais, e adiciona esse produto ao carrinho antes de direcionar ao Checkout Block.

Quando o curso ou produto demonstrativo não estiver disponível, a página apresenta uma mensagem segura e não exibe os CTAs de compra.

### 10. Registrar interesse demonstrativo

A landing inclui uma seção de captação local com nome, e-mail e consentimento explícito. O envio usa nonce, validação no servidor, honeypot e redirecionamento PRG. Os leads são registros privados do WordPress, acessíveis somente a administradores; reenvios do mesmo e-mail normalizado retornam sucesso sem criar duplicação.

O formulário não envia e-mails, não usa CRM, analytics nem serviços externos. A Política de Privacidade gerenciada descreve essa captação demonstrativa.

### 11. Consultar e-mails transacionais locais

O ambiente inclui o Mailpit `v1.30.7` para capturar e-mails sem entregá-los fora do Docker. Com `COURSEPRESS_LOCAL_MAIL_ENABLED=1`, o `wp_mail` usa o SMTP interno e a interface fica disponível em `http://127.0.0.1:MAILPIT_UI_PORT` (porta padrão `8025`).

O primeiro registro de interesse envia uma confirmação em texto simples. Reenvios do mesmo e-mail não geram nova confirmação depois de um envio bem-sucedido; se o envio anterior falhar, uma nova tentativa é feita sem duplicar o lead.

### 12. Validar GA4 pelo Google Tag Manager

A instrumentação é opcional e permanece desativada quando
`COURSEPRESS_GTM_CONTAINER_ID` está vazio. Para uma validação local, use um
contêiner Web exclusivo de teste e informe apenas um ID no formato `GTM-*` no
arquivo `.env`:

```dotenv
COURSEPRESS_GTM_CONTAINER_ID=GTM-EXEMPLO
```

No painel do GTM, configure uma única **tag Google** com o Measurement ID
`G-*` do fluxo GA4 de teste. O Measurement ID, credenciais e exports do Google
não devem ser gravados no repositório. Depois de alterar o `.env`, recrie o
serviço WordPress e confira a instalação local:

```bash
docker compose up -d --force-recreate wordpress
curl -fsS http://localhost:8080/ | rg -o 'googletagmanager\.com/gtm\.js\?id=GTM-[A-Z0-9]+'
```

O comando deve encontrar uma única referência. Use o Preview/Tag Assistant
para confirmar uma única tag Google e o GA4 DebugView para confirmar apenas o
`page_view` automático, sem nome, e-mail, dados de lead, checkout ou compra.
Remova o valor da variável e recrie o serviço para desativar a emissão.

Não use um contêiner de produção nem habilite coleta pública sem aprovação
jurídica/operacional sobre privacidade e consentimento. Eventos customizados,
SEO, Consent Mode e CMP não fazem parte desta etapa.

### 13. Encerrar a sessão

```bash
docker compose stop
```

Para iniciar novamente:

```bash
docker compose start
```

## Comandos úteis

Ver logs:

```bash
docker compose logs -f
```

Ver apenas os logs do WordPress:

```bash
docker compose logs -f wordpress
```

Executar WP-CLI sem instalar PHP no computador:

```bash
docker compose run --rm cli core version
docker compose run --rm cli plugin list
docker compose run --rm cli theme list
```

Parar e remover os contêineres, preservando os dados:

```bash
docker compose down
```

> Não execute `docker compose down -v` sem confirmar que os dados locais podem ser apagados.

## Trabalho em vários computadores

O GitHub sincroniza o código autoral, mas o banco, os usuários, uploads e configurações do painel permanecem em volumes locais. Consulte [docs/MULTI-COMPUTER.md](docs/MULTI-COMPUTER.md) antes de trocar de computador.

Nenhum banco com dados de usuário ou credenciais deve ser versionado.

## Estrutura

```text
coursepress-lab/
├── compose.yaml
├── .env.example
├── .gitignore
├── docs/
│   ├── MULTI-COMPUTER.md
│   └── ROADMAP.md
├── scripts/
│   └── configure-store.ps1
└── wp-content/
    ├── plugins/
    │   └── coursepress-core/
    └── themes/
        └── coursepress-lab/
```

O núcleo do WordPress, uploads e banco ficam em volumes Docker. Somente o código autoral do tema, do plugin e das automações é versionado.

## Reversão do produto demonstrativo

A reversão não é automática. Somente com autorização explícita, localize o produto pelo SKU `CPA-WP-NEGOCIOS-001` e confirme o marcador `_coursepress_demo_managed = 1` antes de removê-lo. A categoria `Cursos de WordPress` só pode ser removida se tiver o mesmo marcador e não possuir produtos. Nunca remova volumes para reverter esse conteúdo.

## Estado atual

- [x] ambiente local reproduzível;
- [x] banco com verificação de saúde;
- [x] esqueleto do tema próprio;
- [x] esqueleto do plugin próprio;
- [x] validação no PC da faculdade;
- [x] validação no PC pessoal;
- [x] validação no PC do trabalho;
- [x] WP-CLI disponível por contêiner;
- [x] configuração automatizada do WooCommerce validada;
- [x] páginas legais e transacionais reproduzíveis;
- [x] fundação do Tutor LMS;
- [x] checkout de testes por Cheque e matrícula nativa após aprovação;
- [x] landing page responsiva integrada ao curso demonstrativo;
- [ ] integrações;
- [ ] testes e publicação.

Consulte [docs/ROADMAP.md](docs/ROADMAP.md) para acompanhar as etapas.

## Licença

Este projeto é disponibilizado sob a licença MIT.
