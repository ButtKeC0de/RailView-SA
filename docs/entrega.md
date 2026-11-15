# Entrega e situação real

## Pendências que exigem execução ou informação da equipe

- [ ] Confirmar integrantes e contribuições no README.
- [ ] Rodar PHP lint e scripts/verificar_demo.php em PHP instalado.
- [ ] Importar o SQL duas vezes em MariaDB de teste e conferir que os exemplos não duplicam.
- [ ] Executar os três logins, CRUDs e bloqueios do roteiro.
- [ ] Capturar as telas reais e inseri-las no README e no manual.
- [ ] Realizar e registrar o teste com participante externo.
- [ ] Validar a tela de leituras/alertas (RN24, RN28, RF21 e RF24). Integração física IoT e outros requisitos ainda não implementados precisam de decisão de escopo.
- [ ] Conferir os documentos PDF históricos, incluindo Link_Daily_Meeting.pdf.
- [ ] Atualizar o Projects com evidências e sem marcar testes não realizados como concluídos.
- [ ] Publicar e verificar a tag e a release v1.0.0 no commit final.

## Ajustes sugeridos para o Kanban

O quadro público consultado em 15/11/2026 mostra tarefas de segurança em Backlog e partes do manual em Done. Confronte os cartões com o código e as evidências:

| Cartões | Estado justificável |
|---|---|
| #89 e #90 — hash e verificação | In review: há password_hash/password_verify, mas falta registrar teste de login real |
| #92, #93 e #95 — permissões | In review até executar acesso direto como usuário comum |
| #81–#85 — manual | In review: texto preparado, capturas e validação por usuário pendentes |
| #39 — IoT | Backlog: sem integração com equipamentos |
| #40 — alertas | In review: regras e histórico implementados; falta validar no navegador |
| #59 e #72 — estilos | In review até inspeção visual em navegador |

Esta tabela não altera o Projects. A edição depende de sessão autenticada e permissão no quadro.

## Computador da bancada

1. Baixe o commit final completo para htdocs/RailView-SA.
2. Ligue Apache e MariaDB. Confira a porta 3306 e a configuração da conexão.
3. Faça backup de qualquer banco existente e importe o SQL final no ambiente local de demonstração.
4. Execute o roteiro; confira links, botões, textos e layout em largura de 375 px.
5. Ensaie os logins admin@demo.test e usuario@demo.test em sessões separadas.
6. Encerre ambas as sessões e deixe o navegador aberto em public/login.php.

## Publicação da versão

O commit de correções deve vir antes da tag. Preserve alterações de outras pessoas e confira git status e o diff antes de publicar. Não reutilize uma tag já existente nem force o histórico.

```powershell
git status --short
git diff --check
git log -1 --oneline
git tag -a v1.0.0 -m "Rail View v1.0.0"
git push origin main
git push origin v1.0.0
```

Depois, em GitHub → Releases → Draft a new release, selecione v1.0.0 e use as notas abaixo, informando as limitações e os testes efetivamente executados. Confira que a tag aponta ao commit apresentado. Não registre como concluída uma publicação que ainda não existe.

### Notas da versão propostas

Rail View v1.0.0 — protótipo acadêmico com gestão de trens, sensores, rotas e usuários. Documentação de instalação e uso, contas fictícias com bcrypt, carga demo reexecutável, conexão configurável por ambiente e correções de navegação. Inclui registro manual de leituras, configuração de máximos e histórico de alertas gerados automaticamente. Telemetria IoT física e manutenção permanecem fora do fluxo implementado. Evidências de navegador e teste externo devem refletir somente execuções realizadas.
