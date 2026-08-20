# Checkpoint WIP — páginas legais

Data do checkpoint: 2026-08-20

Branch: `feat/legal-pages`
Base main: `981c698`

## Objetivo

Concluir o marco de páginas legais e transacionais reproduzíveis para o ambiente educacional CoursePress Lab, preservando conteúdo não gerenciado e a página padrão de privacidade do WordPress.

## Arquivos modificados

- `README.md`
- `docs/ROADMAP.md`
- `scripts/configure-store.ps1`
- `wp-content/plugins/coursepress-core/cli/configure-legal-pages.php`

## Funcionalidades implementadas

- Instalação e validação das páginas transacionais oficiais do WooCommerce sem reescrever seus títulos, slugs ou conteúdos.
- Páginas gerenciadas de Política de Privacidade e Termos e Condições, com marcadores independentes de propriedade, chave e hash SHA-256 do conteúdo.
- Detecção global de chaves duplicadas e de colisões de slug.
- Sincronização somente quando o hash armazenado ainda corresponde ao conteúdo atual.
- Reatribuição da Política de Privacidade somente a partir de um rascunho oficial, sem alterações, comparado byte a byte com os candidatos oficiais.
- Validação de opções, páginas e URLs antes do flush de regras de reescrita.

## Validações aprovadas

- Parser do PowerShell 7 e lint PHP do helper.
- `git diff --check`.
- Criação, idempotência e restauração de slug de páginas legais no ambiente temporário `coursepress-legal-verify`.
- Bloqueio de conteúdo legal gerenciado adulterado.
- Bloqueio de rascunho padrão de privacidade com um byte alterado.
- HTTP das páginas legais e transacionais; checkout anônimo com carrinho válido e links legais no cadastro e checkout no ambiente temporário em porta 18080.
- Primeira execução de `configure-store.ps1` no ambiente principal terminou sem erro visível.

## Validações pendentes

- Segunda execução de `configure-store.ps1` no ambiente principal e prova explícita de idempotência.
- Cenário de instalação WordPress originalmente em `pt_BR` com pacote de idioma disponível antes da validação de `switch_to_locale()`.
- Parser real do Windows PowerShell 5.1 pelo WSL Interop.
- `/review` (não executado).

Não houve PR nem merge.

## Resultado da instalação direta em pt_BR

A instalação temporária foi chamada com `--locale=pt_BR`, mas o resultado observado foi:

- `WPLANG` ausente;
- `Privacy Policy` criada em inglês;
- `switch_to_locale('pt_BR')` retornou `false`;
- `RuntimeException: switch failed`.

Isso pode ser um defeito do preparo do ambiente localizado, não necessariamente do helper de páginas legais. O cenário não deve ser usado como evidência de incompatibilidade do helper até que o pacote de idioma esteja disponível antes da comparação.

## Windows PowerShell 5.1 pelo WSL

O executável foi localizado, mas o WSL Interop falhou antes de iniciar o parser:

```text
WSL (2 - ) ERROR: UtilBindVsockAnyPort:309: socket failed 1
```

Logo, a compatibilidade com Windows PowerShell 5.1 não está validada neste checkpoint.

## Ambientes Docker preservados

| Projeto | Porta | Volumes conhecidos | Rede conhecida |
| --- | --- | --- | --- |
| `coursepress-lab` | 8080 | `coursepress-lab_database_data`, `coursepress-lab_wordpress_data` | `coursepress-lab_coursepress` |
| `coursepress-legal-verify` | 18080 | `coursepress-legal-verify_database_data`, `coursepress-legal-verify_wordpress_data` | `coursepress-legal-verify_coursepress` |
| `coursepress-legal-ptbr-verify` | 18081 | `coursepress-legal-ptbr-verify_database_data`, `coursepress-legal-ptbr-verify_wordpress_data` | `coursepress-legal-ptbr-verify_coursepress` |

Esses recursos devem ser preservados. Não executar `docker compose down`, `docker compose down -v`, remoção de volume, rede ou prune para este checkpoint.

## Próximos passos na faculdade

1. Clonar ou atualizar a branch `feat/legal-pages`.
2. Revisar este checkpoint.
3. Diagnosticar a preparação correta de uma instalação WordPress originalmente em `pt_BR`, incluindo pacote de idioma disponível antes de validar `switch_to_locale()`.
4. Repetir o cenário direto em `pt_BR`.
5. Executar `configure-store.ps1` duas vezes.
6. Validar Windows PowerShell 5.1.
7. Executar o `/review`.
8. Somente depois preparar o commit final e o PR.
