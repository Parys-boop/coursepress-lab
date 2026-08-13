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

### 4. Encerrar a sessão

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

Parar e remover os contêineres, preservando os dados:

```bash
docker compose down
```

> Não execute `docker compose down -v` sem confirmar que os dados locais podem ser apagados.

## Estrutura

```text
coursepress-lab/
├── compose.yaml
├── .env.example
├── .gitignore
├── docs/
│   └── ROADMAP.md
└── wp-content/
    ├── plugins/
    │   └── coursepress-core/
    └── themes/
        └── coursepress-lab/
```

O núcleo do WordPress, uploads e banco ficam em volumes Docker. Somente o código autoral do tema e do plugin é versionado.

## Estado atual

- [x] Ambiente local reproduzível;
- [x] banco com verificação de saúde;
- [x] esqueleto do tema próprio;
- [x] esqueleto do plugin próprio;
- [ ] instalação e configuração inicial do WordPress;
- [ ] WooCommerce;
- [ ] Tutor LMS;
- [ ] landing page;
- [ ] checkout de testes;
- [ ] integrações;
- [ ] testes e publicação.

Consulte [docs/ROADMAP.md](docs/ROADMAP.md) para acompanhar as etapas.

## Licença

Este projeto é disponibilizado sob a licença MIT.
