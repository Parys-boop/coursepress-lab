# Repository Guidelines

## Ambiente e Comunicação

O Codex CLI é o ambiente principal de implementação deste projeto. Responda em português e explique decisões técnicas, linguagens, ferramentas e padrões usados para que o mantenedor consiga aprender e defender o projeto tecnicamente.

Mantenha mudanças pequenas, justificadas e alinhadas a `docs/ROADMAP.md`. Antes de encerrar qualquer tarefa, execute as validações relevantes, mostre `git status --short` e resuma o diff.

## Estrutura do Projeto

O CoursePress Lab é um ambiente WordPress em Docker. Não modifique arquivos do núcleo do WordPress. Concentre código autoral nestes locais versionados:

- `wp-content/themes/coursepress-lab/`: tema customizado (`functions.php`, `index.php`, `style.css`).
- `wp-content/plugins/coursepress-core/`: funcionalidades específicas do projeto.
- `scripts/`: automações, como `configure-store.ps1`.
- `tests/` (quando existir): testes automatizados.
- `docs/`: documentação, incluindo `ROADMAP.md` e `MULTI-COMPUTER.md`.
- `compose.yaml` e `.env.example`: definição e exemplo do ambiente local.

## Administração e Validação

Use Docker Compose e WP-CLI para administrar o ambiente:

- `Copy-Item .env.example .env`: cria a configuração local.
- `docker compose up -d`: inicia WordPress, MariaDB e serviços de apoio.
- `docker compose ps`: confirma o estado dos contêineres.
- `.\scripts\configure-store.ps1`: configura a loja e ativa o código do projeto.
- `docker compose run --rm cli plugin list`: verifica plugins via WP-CLI.
- `docker compose run --rm cli theme list`: verifica temas via WP-CLI.
- `docker compose logs -f wordpress`: acompanha logs do WordPress.
- `docker compose stop`: interrompe os serviços sem apagar dados.

Nunca execute `docker compose down -v` sem autorização explícita: ele remove volumes locais, incluindo banco e uploads.

## Estilo e Testes

Siga as convenções WordPress PHP: quatro espaços, proteção `ABSPATH`, saída escapada com `esc_html` ou `esc_url`, e funções prefixadas como `coursepress_lab_*` e `coursepress_core_*`. Use os text domains `coursepress-lab` e `coursepress-core`; prefira kebab-case para diretórios e handles.

Ainda não há suíte automatizada. Valide manualmente as alterações afetadas: contêineres saudáveis, tema e plugin ativados sem erro e páginas acessíveis em `http://localhost:8080`. Ao adicionar testes, mantenha-os em `tests/` ou próximos ao módulo testado e documente o comando de execução.

## Segurança, Commits e Pull Requests

Nunca versione `.env`, credenciais, banco de dados, uploads, logs ou dados pessoais. Não faça commit, push ou abra pull request sem autorização explícita. Quando autorizado, use mensagens curtas no estilo Conventional Commits, como `feat:`, `fix:` e `docs:`, e registre no PR a descrição, validações, issues relacionadas e capturas de tela de mudanças visuais.
