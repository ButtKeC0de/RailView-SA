# Roteiro de testes — Rail View

Status: **execução pendente**. A análise do código e os scripts CLI não substituem os testes abaixo. Preencha resultado, data, ambiente, commit e evidência somente após executar.

## Preparação

1. Ligue Apache e MariaDB no XAMPP; confira a porta 3306.
2. Use uma instalação de teste e faça backup antes de importar database/SA_ferrorama.sql.
3. Abra http://localhost/RailView-SA/public/login.php.
4. Use janelas separadas para os perfis. Contas demo: admin@demo.test, funcionario@demo.test e usuario@demo.test, todas com senha fictícia password.
5. Use dados descartáveis nos testes de exclusão; não altere registros de terceiros.

## Banco e autenticação

| ID | Ação | Resultado esperado | Resultado / evidência |
|---|---|---|---|
| T01 | Importar o SQL duas vezes | Sem erros; três contas demo, dois trens demo, dois sensores demo e duas rotas demo sem duplicação | |
| T02 | Rodar php scripts/verificar_demo.php e php scripts/verificar_alertas.php | Ambos terminam com código zero | |
| T03 | Rodar php -l em todos os PHP de infra, public e scripts | Nenhum erro de sintaxe | |
| T04 | Entrar com cada uma das três contas demo | Login válido e nome/perfil corretos | |
| T05 | Entrar com nome completo Admin Demo | Login também aceita o nome completo | |
| T06 | Tentar senha incorreta, usuário inexistente e campos vazios | Não autentica; credenciais inválidas usam mensagem genérica | |
| T07 | Visitar home.php e páginas de consulta sem sessão | Redirecionamento ao login | |
| T08 | Visitar usuario_cadastro.php sem sessão | Redirecionamento ao login; cadastro exige administrador | |
| T09 | Sair e tentar voltar a uma página protegida | Sessão encerrada; exige login | |

## Perfis e contas

| ID | Ação | Resultado esperado | Resultado / evidência |
|---|---|---|---|
| T10 | Como usuário e funcionário, consultar trens, sensores, rotas e alertas | Consultas disponíveis, ações administrativas ausentes | |
| T11 | Como usuário, abrir diretamente cadastro/edição/exclusão de trem e sensor, lista de usuários e edição de rota | HTTP 403; nenhum registro alterado | |
| T12 | Como usuário, tentar editar ou excluir outra conta | HTTP 403; conta preservada | |
| T13 | Como único administrador, tentar excluir a própria conta ou mudar para Usuário | Operação bloqueada; permanece um admin | |
| T14 | Como administrador, criar conta fictícia com cada perfil | Conta salva; senha com hash; email duplicado, senha curta e perfil inválido recusados | |
| T15 | Alterar os próprios dados e senha | Persiste alteração; login posterior aceita nova senha | |
| T16 | Rebaixar conta de teste logada em outra janela e recarregar | Permissão atualizada pelo banco sem novo login | |
| T17 | Excluir conta de teste logada em outra janela e recarregar | Sessão invalidada e redirecionamento ao login | |
| T18 | Usar o botão Início da lista de usuários | Abre home.php | |

## Trens, sensores e rotas

| ID | Ação | Resultado esperado | Resultado / evidência |
|---|---|---|---|
| T19 | Cadastrar trem de teste com responsável existente | Trem salvo com responsável correto | |
| T20 | Editar trem e consultar lista | Novos dados aparecem corretamente | |
| T21 | Cadastrar e editar sensor, escolhendo trem e status | Dados e vínculo persistidos; status inválido recusado | |
| T22 | Consultar sensor pelo ID e usar a pesquisa | Exibe o sensor solicitado e filtra conforme os dados | |
| T23 | Cadastrar rota com trem, origem, destino e operadora | Rota e Historico_Rota salvos com vínculo coerente | |
| T24 | Tentar origem e destino iguais ou trem inexistente | Operação recusada | |
| T25 | Editar rota e trocar o trem | Lista mostra o trem escolhido | |
| T26 | Tentar excluir trem com sensor ou rota | Exclusão bloqueada; vínculos preservados | |
| T27 | Excluir rota e sensor descartáveis; depois excluir seu trem | Operações permitidas via POST; exclusão da rota remove o vínculo correspondente | |
| T28 | Como admin, tentar exclusão por GET | Nenhum registro excluído | |
| T29 | Informar texto com caracteres HTML nos campos | Saída mostra texto escapado, sem executar HTML ou JavaScript | |
| T30 | Abrir Sensores pelo menu e pelo atalho da home | Ambos abrem sensor_lista.php, inclusive em servidor com caminhos sensíveis a maiúsculas | |

## Limites e alertas

| ID | Ação | Resultado esperado | Resultado / evidência |
|---|---|---|---|
| T31 | Como admin, abrir Alertas e configurar máximo 80 km/h em sensor de teste | Limite aparece na tabela | |
| T32 | Registrar leituras 79, 80 e 81, nessa ordem | Três leituras salvas; somente 81 gera alerta | |
| T33 | Registrar falha sem valor | Leitura e alerta de falha salvos juntos | |
| T34 | Remover o máximo com campo vazio e registrar 100 sem falha | Leitura salva sem alerta numérico | |
| T35 | Informar leitura vazia sem falha, texto inválido ou sensor inexistente | Rejeita sem gravar leitura nem alerta | |
| T36 | Como conta comum ou funcionário, tentar enviar POST de leitura/limite | HTTP 403; banco preservado | |
| T37 | Como admin, enviar formulário sem token CSRF válido | HTTP 403; banco preservado | |
| T38 | Consultar alertas e leituras com conta comum | Históricos visíveis, sem formulário administrativo | |

## Apresentação e validação externa

| ID | Ação | Resultado esperado | Resultado / evidência |
|---|---|---|---|
| T39 | Navegar em 375 px e em desktop | Conteúdo e controles utilizáveis; tabelas de alertas rolam horizontalmente | |
| T40 | Conferir links, botões, mensagens e recursos externos | Nenhum link local quebrado; indisponibilidade externa identificada | |
| T41 | Repetir roteiro com pessoa de fora da equipe | Registro real em teste_externo.md, com feedback e correções | |
| T42 | Tirar capturas de login, home, módulos, alertas e tela pequena | Imagens reais inseridas no README e no MANUAL | |

## Limitações a declarar na avaliação

Funcionário tem as mesmas permissões de Usuário, divergindo de RN11–RN15 do documento original. Sensores se associam às rotas por meio do trem. Leituras são registradas manualmente; não existe integração com equipamento IoT. Estatísticas, manutenção, relatórios semanais, velocidade média e chegada automática ainda não estão implementados. Historico_Rota não é uma trilha imutável de viagens.

Não marque esses requisitos como concluídos no Kanban sem implementar, testar ou registrar formalmente uma alteração de escopo aprovada.
