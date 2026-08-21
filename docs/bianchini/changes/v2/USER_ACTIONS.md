# Ações externas — SEO técnico básico

## U-001 — Executar o gate numa cópia local configurada

**Necessária em:** conclusão de P01, antes de marcar o plano como concluído.

Esta sessão não tem `.env`; por isso `docker compose run --rm cli` recebe
credenciais vazias e não acessa o banco. Em uma cópia local válida, executar:

```powershell
pwsh -NoProfile -File ./scripts/configure-seo.ps1
pwsh -NoProfile -File ./scripts/validate-seo.ps1 -Scope plan
```

O limite é o gate do plano: ele não pode ser aprovado sem os dois comandos com
saída bem-sucedida e HTML das URLs verificadas. Enquanto isso, a execução pode
fazer revisão estática, mas não declarar a validação operacional aprovada.

**Fallback:** nenhuma alteração é publicada; a configuração permanece local e
reproduzível até uma máquina com `.env`, Docker e WordPress instalados executar
o gate.
