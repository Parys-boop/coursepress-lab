# Ações do responsável — v1: GA4 + Google Tag Manager

## U-001 — Criar e disponibilizar as configurações de teste

**Necessária antes de:** executar P01 no ambiente local.

1. Criar ou selecionar uma propriedade GA4 de teste e um fluxo Web para o
   endereço local que será validado.
2. Criar ou selecionar um contêiner Web GTM exclusivo do CoursePress Lab.
3. Configurar no GTM uma única tag **Google tag** com o Measurement ID `G-*`
   do fluxo GA4, com acionador de inicialização/page view conforme a interface
   atual do Google.
4. Entregar somente o ID do contêiner `GTM-*` para o arquivo `.env` local.
   Não enviar credenciais, exports nem o Measurement ID ao repositório.

**Evidência exigida:** IDs de teste confirmados fora do Git e captura/registro
do Preview do Tag Assistant mostrando a tag Google acionada.

**Fallback:** enquanto o ID `GTM-*` não existir, manter a configuração vazia;
o tema não emite o snippet e o restante do ambiente continua operando.

## U-002 — Autorizar ativação fora do ambiente demonstrativo

**Necessária antes de:** usar ID de contêiner de produção ou publicar coleta
para visitantes reais.

Confirmar, com responsável jurídico/operacional quando aplicável, a base para
coleta, a política de privacidade e a solução de consentimento. CMP, banner e
Consent Mode não fazem parte deste marco e exigem planejamento separado se
necessários.

**Evidência exigida:** aprovação registrada pelo responsável para a ativação
pública e identificação do contêiner permitido; sem credenciais no Git.

**Fallback:** usar somente a propriedade e o contêiner de teste no ambiente
local, ou remover o ID do `.env` para desativar a emissão.
