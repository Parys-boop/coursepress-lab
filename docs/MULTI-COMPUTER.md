# Trabalho em vários computadores

Arthur alterna entre o computador da faculdade, o computador do trabalho e o computador pessoal. Este documento define como manter o projeto consistente sem enviar dados sensíveis ao GitHub.

## O que o Git sincroniza

- `compose.yaml`;
- `.env.example`;
- tema autoral;
- plugin autoral;
- documentação;
- scripts e testes futuros.

## O que permanece local

- `.env`;
- banco de dados;
- usuários e senhas;
- uploads;
- cache;
- logs;
- volumes Docker.

O banco contém e-mails, configurações e hashes de senha. Por isso, dumps SQL e backups não devem ser enviados ao repositório público.

## Ao começar em um computador

```bash
git pull
docker desktop start
docker compose up -d
docker compose ps
```

Se for a primeira utilização nessa máquina:

```bash
cp .env.example .env
docker compose up -d
```

No PowerShell, use `Copy-Item .env.example .env` no lugar de `cp`.

## Ao encerrar a sessão

```bash
git status
docker compose stop
```

Se houver alteração em código ou documentação, teste, faça commit e envie antes de trocar de computador.

## Administração com WP-CLI

```bash
docker compose run --rm cli core version
docker compose run --rm cli plugin list
docker compose run --rm cli theme list
```

O serviço `cli` usa os mesmos arquivos e o mesmo banco da instalação local.

## Conteúdo demonstrativo

O conteúdo público do case será convertido gradualmente em código de inicialização ou em exportações sanitizadas, sem usuários e credenciais. Até esse processo existir, não se deve esperar que páginas criadas manualmente no painel apareçam automaticamente em outra máquina.

## Backup local

Uma exportação completa do banco pode ser feita com WP-CLI, mas deve ficar fora do Git e ser tratada como arquivo sensível. O procedimento definitivo de backup e restauração será implementado e testado na fase de qualidade.
