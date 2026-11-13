# Relatório de Utilização de Inteligência Artificial no Desenvolvimento

**Projeto:** Rail View (Ferrorama) – Sistema de Monitoramento Ferroviário  
**Ferramenta de IA utilizada:** ChatGPT, Claude, Copilot  
**Repositório:** RailView-SA  
**Tecnologias do projeto:** PHP, MySQL, HTML, CSS, JavaScript e Bootstrap

## 1. Introdução

Durante o desenvolvimento do projeto Rail View (Ferrorama), a equipe utilizou o ChatGPT, Claude e Copilot como ferramenta de apoio para pesquisar soluções, compreender conceitos técnicos, estruturar e revisar códigos, identificar possíveis erros e sugerir melhorias.

A ferramenta foi utilizada como suporte ao processo de aprendizagem e desenvolvimento. As sugestões recebidas precisaram ser analisadas e adaptadas à estrutura do projeto, e a equipe permaneceu responsável pelas decisões de implementação, integração e validação do sistema.

Este relatório registra as principais formas de utilização da IA, os exemplos de instruções fornecidas, as funcionalidades relacionadas, as verificações possíveis a partir dos arquivos do projeto e os pontos que ainda precisam de validação.

## 2. Ferramenta utilizada

**ChatGPT.**

O ChatGPT foi utilizado para auxiliar em atividades relacionadas principalmente a:

- Banco de dados MySQL e relacionamentos entre tabelas;
- Login, autenticação e gerenciamento de sessões;
- Segurança das operações realizadas no sistema;
- Análise de erros e organização da documentação.

**Claude.**

O Claude foi utilizado para auxiliar em atividades relacionadas principalmente a:

- Operações CRUD (cadastro, consulta, edição e exclusão).
- Segurança das operações realizadas no sistema;

**Copilot.**

O Copilot foi utilizado para auxiliar em atividades relacionadas principalmente a:

- Desenvolvimento web com PHP, HTML, CSS, JavaScript e Bootstrap;

## 3. Objetivos e exemplos de utilização

### 3.1. Desenvolvimento das funcionalidades CRUD

A IA foi consultada para compreender a organização das operações de cadastro, consulta, edição e exclusão, além de auxiliar na estruturação de códigos PHP e consultas ao banco de dados.

**Exemplo de instrução fornecida:**

> “Separe em partes o que eu devo fazer para desenvolver um CRUD utilizando PHP, MySQL, HTML e CSS.”

**Aplicação no projeto:** apoio ao desenvolvimento das funcionalidades de gerenciamento de usuários, trens e sensores, incluindo formulários e consultas ao banco de dados.

### 3.2. Banco de dados e relacionamentos

A ferramenta foi utilizada para esclarecer conceitos de chaves primárias, chaves estrangeiras, integridade referencial e comandos SQL.

**Exemplo de instrução fornecida:**

> “Me ajude a adicionar o funcionário responsável pelo trem, utilizando uma chave estrangeira no banco de dados e um campo de seleção nos formulários de cadastro e edição.”

**Aplicação no projeto:** a tabela `Trem` possui o campo `id_usuario`, relacionado à tabela `Usuario`. Os arquivos de cadastro, edição e listagem de trens também consideram essa associação.

### 3.3. Login e gerenciamento de sessões

A IA auxiliou na investigação de um comportamento incorreto durante a troca de usuários, quando o sistema permitia retornar a um perfil sem realizar novamente o login.

**Exemplo de instrução fornecida:**

> “Quando eu saio do perfil e quero entrar novamente, ele entra no mesmo perfil sem que eu precise fazer login. Me ajude a arrumar isso.”

**Aplicação no projeto:** análise dos arquivos responsáveis pela autenticação e pelo encerramento da sessão. No código atual, o login utiliza `password_verify()` para verificar a senha e `session_regenerate_id(true)` após a autenticação. O arquivo de logout limpa os dados da sessão, remove o cookie de sessão e destrói a sessão.

### 3.4. Segurança das operações

A ferramenta foi consultada para compreender os riscos de utilizar requisições GET para excluir registros e para estudar alternativas mais seguras.

**Exemplo de instrução fornecida:**

> “Os arquivos de exclusão de trens e sensores utilizam requisições GET. Isso deve ser corrigido: excluir registros deve ser uma operação POST protegida contra CSRF, com validação de permissão no backend.”

**Aplicação no projeto:** os arquivos `trens_excluir.php` e `sensor_excluir.php` verificam se a requisição é POST e exigem perfil de administrador. Também foi analisada a necessidade de validar permissões no servidor.

### 3.5. Identificação e correção de erros

A IA também foi utilizada para analisar comportamentos inesperados, interpretar mensagens de erro e sugerir formas de localizar e corrigir problemas.

**Exemplo de instrução fornecida:**

> “Analise os códigos do projeto, identifique possíveis erros e apresente as correções necessárias, explicando onde cada alteração deve ser realizada.”

**Aplicação no projeto:** apoio à investigação de problemas de autenticação, integração com o banco de dados e funcionamento das funcionalidades.

## 4. Arquivos e funcionalidades relacionados ao auxílio da IA

A análise dos arquivos do repositório enviado identificou os seguintes componentes do sistema:

| Arquivos ou componentes | Funcionalidade relacionada |
|---|---|
| `public/login.php` | Login, verificação de credenciais e criação da sessão. |
| `public/logout.php` | Encerramento da sessão e remoção do cookie de sessão. |
| `infra/auth.php` | Verificação de login e controle de acesso por perfil. |
| `infra/conexao.php` | Conexão com o banco de dados MySQL. |
| `public/usuario_cadastro.php`, `usuario_editar.php`, `usuario_excluir.php` e `usuario_lista.php` | Gerenciamento de usuários. |
| `public/trens_cadastro.php`, `trens_editar.php`, `trens_excluir.php` e `trens_lista.php` | Gerenciamento de trens e associação com o usuário responsável. |
| `public/sensor_cadastro.php`, `sensor_editar.php`, `sensor_excluir.php` e `sensor_lista.php` | Gerenciamento de sensores. |
| `public/criacao_rotas.php` | Cadastro, edição, listagem e exclusão de rotas. |
| `database/SA_ferrorama.sql` | Criação do banco de dados, tabelas e relacionamentos. |
| `Assets/css/` e `scripts/` | Estilização e funcionalidades complementares da interface. |

A presença desses arquivos demonstra a estrutura das funcionalidades no repositório, mas não significa, por si só, que cada linha de código tenha sido gerada por IA. O auxílio ocorreu em consultas específicas, conforme as necessidades encontradas durante o desenvolvimento.

## 5. Alterações, adaptações e responsabilidade da equipe

Entre os cuidados adotados ou necessários durante essa integração estão:

- Ajustar nomes de tabelas, campos, variáveis e arquivos aos padrões utilizados no projeto;
- Adaptar consultas SQL aos relacionamentos definidos no banco de dados;
- Conferir se os formulários enviam os dados esperados pelo PHP;
- Verificar se as operações respeitam os perfis de acesso;
- Integrar as alterações às páginas e funcionalidades existentes;
- Analisar os resultados e corrigir incompatibilidades encontradas.

A equipe não considera uma sugestão da IA correta apenas por ter sido gerada pela ferramenta. O código foi compreendido, revisado e validado antes de ser utilizado.

## 6. Testes e validações

O repositório contém o arquivo `docs/roteiro_de_testes.md`, que apresenta procedimentos para verificar o funcionamento do sistema. O roteiro contempla, entre outros pontos:

- Login com credenciais válidas e inválidas;
- Armazenamento de senhas com hash;
- Regeneração do identificador de sessão após o login;
- Exibição dos dados do usuário autenticado;
- Encerramento da sessão e bloqueio de páginas protegidas;
- Operações de cadastro, edição, consulta e exclusão;
- Associação entre trens, usuários, sensores e rotas;
- Verificação das permissões de acesso.

## 7. Problemas identificados e pontos de melhoria

Foram observados os seguintes pontos:

### 7.1. Exclusões por POST

Os arquivos `trens_excluir.php` e `sensor_excluir.php` verificam o método POST e exigem autenticação com perfil de administrador. O arquivo `usuario_excluir.php` também verifica o método POST e aplica regras de permissão.

### 7.2. Exclusão e gerenciamento de rotas

O arquivo `criacao_rotas.php` utiliza POST para operações de alteração e exclusão e verifica o perfil de administrador nessas operações. Entretanto, também não foi encontrada proteção CSRF no arquivo analisado. Essa proteção deve ser incluída e validada.

### 7.3. Configuração da conexão com o banco

O arquivo `infra/conexao.php` configura a conexão com o banco `rail_view_db` utilizando a porta `3307`.

## 8. Reflexão sobre as contribuições e limitações da IA

A utilização do ChatGPT, Claude e Copilot contribuiu para o desenvolvimento do projeto ao facilitar a compreensão de conceitos de programação, banco de dados, autenticação e segurança. A ferramenta também auxiliou na investigação de erros e na busca por alternativas de implementação.

Por outro lado, a IA apresenta limitações. As respostas podem não considerar todos os detalhes da estrutura do projeto, podem sugerir códigos incompatíveis com arquivos existentes e não garantem que uma funcionalidade esteja correta ou segura.

Por esse motivo, foi necessário que os integrantes compreendessem as sugestões recebidas, fizessem as adaptações necessárias e executem os testes. A responsabilidade pelo código e pelo funcionamento do sistema continua sendo da equipe.

A experiência mostrou que a Inteligência Artificial pode ser uma ferramenta útil no processo de desenvolvimento e aprendizagem, desde que seja utilizada de forma crítica, transparente e responsável.

## 9. Registro da participação da IA nos commits

Quando código produzido com auxílio da IA for incorporado ao projeto, essa participação deve ser documentada nos commits correspondentes, sem criar uma identidade humana fictícia para a ferramenta.

## 10. Considerações finais

A Inteligência Artificial foi utilizada como ferramenta de apoio durante diferentes etapas do desenvolvimento do Rail View (Ferrorama), contribuindo para a pesquisa, a compreensão de conceitos, a elaboração e revisão de códigos e a identificação de possíveis melhorias.

Entretanto, o uso da ferramenta não substituiu a participação dos integrantes. As sugestões precisaram ser analisadas e adaptadas ao projeto, e a validação do funcionamento depende da execução de testes pela equipe.

Conclui-se que o uso responsável da IA exige transparência, análise crítica e compreensão do conteúdo utilizado, garantindo que os desenvolvedores conheçam e assumam a responsabilidade pelas funcionalidades entregues.
